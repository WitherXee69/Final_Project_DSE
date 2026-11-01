<?php
session_start();

if (empty($_SESSION['user_id'])) {
    header('Location: ../login.php?portal=staff');
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

$currentUserId = (string) $_SESSION['user_id'];

$message = '';
$messageType = '';

// Role list used by the filter, the table and the redirect after deletion.
$roleLabels = [
    'IndividualCustomer'     => 'Individual Customer',
    'CustomerServiceOfficer' => 'Customer Service Officer',
    'CustomerServiceManager' => 'Customer Service Manager',
    'FinanceOfficer'         => 'Finance Officer',
    'FinanceManager'         => 'Finance Manager',
    'TransportManager'       => 'Transport Manager',
    'Driver'                 => 'Driver',
    'WarehouseOfficer'       => 'Warehouse Officer',
    'WarehouseManager'       => 'Warehouse Manager',
    'ManagingDirector'       => 'Managing Director',
    'SystemAdmin'            => 'System Administrator',
];
$allowedRoles = array_merge(array_keys($roleLabels), ['ALL']);

// Verify System Administrator access.
try {
    $adminStmt = $pdo->prepare(
        "SELECT u.userId
         FROM User u
         INNER JOIN SystemAdmin sa ON sa.userId = u.userId
         WHERE u.userId = :userId
         LIMIT 1"
    );

    $adminStmt->execute(['userId' => $currentUserId]);

    if (!$adminStmt->fetch()) {
        http_response_code(403);
        exit('Access denied. System Administrator access is required.');
    }

} catch (PDOException $e) {
    error_log('User management authorization error: ' . $e->getMessage());

    http_response_code(500);
    exit('Unable to verify administrator access.');
}

// Handle permanent Individual Customer deletion.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!verify_csrf()) {
        $message = 'Invalid security token. Please try again.';
        $messageType = 'error';

    } else {

        $targetUserId = trim($_POST['userId'] ?? '');

        if ($targetUserId === '') {
            $message = 'Invalid user selected.';
            $messageType = 'error';

        } elseif ($targetUserId === $currentUserId) {
            $message = 'You cannot delete your own account while you are logged in.';
            $messageType = 'error';

        } else {

            try {
                $pdo->beginTransaction();

                // Retrieve and lock the selected user.
                $targetStmt = $pdo->prepare(
                    "SELECT userId, username, role
                     FROM User
                     WHERE userId = :userId
                     LIMIT 1
                     FOR UPDATE"
                );

                $targetStmt->execute([
                    'userId' => $targetUserId
                ]);

                $targetUser = $targetStmt->fetch(PDO::FETCH_ASSOC);

                if (!$targetUser) {
                    throw new RuntimeException(
                        'The selected user could not be found.'
                    );
                }

                // System Administrator accounts are protected.
                if ($targetUser['role'] === 'SystemAdmin') {
                    throw new RuntimeException(
                        'System Administrator accounts are protected and cannot be deleted.'
                    );
                }

                // This deletion process is specifically for Individual Customers.
                if ($targetUser['role'] !== 'IndividualCustomer') {
                    $roleName = $roleLabels[$targetUser['role']] ?? $targetUser['role'];

                    throw new RuntimeException(
                        'This ' . $roleName . ' account cannot be deleted from this page. ' .
                        'Only Individual Customer accounts without parcel records can be deleted.'
                    );
                }

                /*
                 * Find the Customer record connected to this user.
                 * IndividualCustomer and Customer share the same userId.
                 */
                $customerStmt = $pdo->prepare(
                    "SELECT c.*
                     FROM Customer c
                     INNER JOIN IndividualCustomer ic
                         ON ic.userId = c.userId
                     WHERE c.userId = :userId
                     LIMIT 1
                     FOR UPDATE"
                );

                $customerStmt->execute([
                    'userId' => $targetUserId
                ]);

                $customer = $customerStmt->fetch(PDO::FETCH_ASSOC);

                if (!$customer) {
                    throw new RuntimeException(
                        'The customer profile could not be found. No records were deleted.'
                    );
                }

                // Use Customer.customerId when that column exists, otherwise the shared userId.
                $customerId = $customer['customerId'] ?? $customer['userId'];

                /*
                 * Check whether this customer has any parcels.
                 * If at least one parcel exists, deletion is blocked.
                 */
                $parcelStmt = $pdo->prepare(
                    "SELECT COUNT(*)
                     FROM Parcel
                     WHERE customerId = :customerId"
                );

                $parcelStmt->execute([
                    'customerId' => $customerId
                ]);

                $parcelCount = (int) $parcelStmt->fetchColumn();

                if ($parcelCount > 0) {
                    throw new RuntimeException(
                        'This customer cannot be deleted because they have existing parcel records.'
                    );
                }

                /*
                 * No parcels found.
                 * Delete the customer profile records first,
                 * followed by the main User record.
                 */

                $individualStmt = $pdo->prepare(
                    "DELETE FROM IndividualCustomer
                     WHERE userId = :userId"
                );

                $individualStmt->execute([
                    'userId' => $targetUserId
                ]);

                if ($individualStmt->rowCount() !== 1) {
                    throw new RuntimeException(
                        'Unable to delete the individual customer profile.'
                    );
                }

                $customerDeleteStmt = $pdo->prepare(
                    "DELETE FROM Customer
                     WHERE userId = :userId"
                );

                $customerDeleteStmt->execute([
                    'userId' => $targetUserId
                ]);

                if ($customerDeleteStmt->rowCount() !== 1) {
                    throw new RuntimeException(
                        'Unable to delete the customer profile.'
                    );
                }

                $userDeleteStmt = $pdo->prepare(
                    "DELETE FROM User
                     WHERE userId = :userId"
                );

                $userDeleteStmt->execute([
                    'userId' => $targetUserId
                ]);

                if ($userDeleteStmt->rowCount() !== 1) {
                    throw new RuntimeException(
                        'Unable to delete the user account.'
                    );
                }

                $pdo->commit();

                $redirectRole = trim((string) ($_POST['currentRole'] ?? ''));

                if (!in_array($redirectRole, $allowedRoles, true)) {
                    $redirectRole = 'IndividualCustomer';
                }

                $redirectParams = [
                    'status' => 'deleted',
                    'role' => $redirectRole,
                    'search' => trim((string) ($_POST['currentSearch'] ?? ''))
                ];

                header(
                    'Location: user_management.php?' .
                    http_build_query($redirectParams)
                );
                exit;

            } catch (RuntimeException $e) {

                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }

                $message = $e->getMessage();
                $messageType = 'error';

            } catch (PDOException $e) {

                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }

                error_log(
                    'User deletion failed for ' .
                    $targetUserId . ': ' . $e->getMessage()
                );

                $sqlState = (string) ($e->errorInfo[0] ?? $e->getCode());

                if ($sqlState === '23000') {
                    $message =
                        'This account is still linked to other system records, so it cannot be deleted. ' .
                        'Nothing was changed.';
                } else {
                    $message =
                        'The account could not be deleted because of a system problem. ' .
                        'Nothing was changed. Please try again later.';
                }

                $messageType = 'error';

            } catch (Throwable $e) {

                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }

                error_log('Unexpected user deletion error: ' . $e->getMessage());

                $message =
                    'Something went wrong while deleting the account. Nothing was changed.';
                $messageType = 'error';
            }
        }
    }
}

// Show the deletion result after redirect.
if (isset($_GET['status']) && $_GET['status'] === 'deleted') {
    $message = 'Individual Customer account permanently deleted successfully.';
    $messageType = 'success';
}

// Apply the selected role and search filters.
$roleFilter = trim((string) ($_GET['role'] ?? $_POST['currentRole'] ?? 'IndividualCustomer'));

if (!in_array($roleFilter, $allowedRoles, true)) {
    $roleFilter = 'IndividualCustomer';
}

$search = trim((string) ($_GET['search'] ?? $_POST['currentSearch'] ?? ''));

try {

    $sql = "SELECT userId, username, role
            FROM User
            WHERE 1 = 1";

    $params = [];

    if ($roleFilter !== 'ALL') {
        $sql .= " AND role = :role";
        $params['role'] = $roleFilter;
    }

    if ($search !== '') {
        $sql .= " AND (
                    userId LIKE :userId
                    OR username LIKE :username
                    OR role LIKE :searchRole
                  )";

        $term = '%' . $search . '%';

        $params['userId'] = $term;
        $params['username'] = $term;
        $params['searchRole'] = $term;
    }

    $sql .= " ORDER BY userId ASC";

    $usersStmt = $pdo->prepare($sql);
    $usersStmt->execute($params);

    $users = $usersStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    error_log('User list error: ' . $e->getMessage());

    http_response_code(500);
    exit('Unable to retrieve user records.');
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>User Management | HELMS</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/system-admin.css">
</head>

<body class="system-admin-page">

<header class="system-admin-header">

    <div class="container system-admin-nav">

        <a href="../index.php" class="brand">
            <div class="logo">H</div>

            <div>
                <b>HELMS</b>
                <small>Henowic Inventors (Pvt) Ltd.</small>
            </div>
        </a>

        <nav class="system-admin-menu">
            <a href="index.php">Dashboard</a>
            <a href="user_management.php" class="active">
                User Management
            </a>
        </nav>

        <div class="system-admin-account">

            <div class="system-admin-account-info">
                <strong>System Admin</strong>
                <small>Account Management</small>
            </div>

            <a class="system-admin-logout" href="../logout.php">
                Logout
            </a>

        </div>

    </div>

</header>

<main class="container user-management-content">

    <a href="index.php" class="back-link">
        ← Back to Dashboard
    </a>

    <div class="user-management-heading">

        <div>
            <span class="system-admin-eyebrow">
                SYSTEM ADMINISTRATION
            </span>

            <h1>User Management</h1>

            <p>View and manage registered HELMS user accounts.</p>
        </div>

        <form method="GET" class="user-filters">

            <div class="filter-group">

                <label for="role">Filter by Role</label>

                <select name="role" id="role">

                    <?php foreach ($roleLabels as $roleValue => $roleName): ?>
                        <option
                            value="<?= h($roleValue) ?>"
                            <?= $roleFilter === $roleValue ? 'selected' : '' ?>
                        >
                            <?= h($roleName) ?>
                        </option>
                    <?php endforeach; ?>

                    <option
                        value="ALL"
                        <?= $roleFilter === 'ALL' ? 'selected' : '' ?>
                    >
                        All Users
                    </option>

                </select>

            </div>

            <div class="filter-group">

                <label for="search">Search Users</label>

                <input
                    type="text"
                    id="search"
                    name="search"
                    placeholder="Search ID, username or role"
                    value="<?= h($search) ?>"
                >

            </div>

            <button type="submit" class="filter-button">
                Apply
            </button>

            <a href="user_management.php" class="clear-filter">
                Reset
            </a>

        </form>

    </div>

    <p class="filter-description">
        By default, this page displays Individual Customer accounts.
        Every account has a Delete button, but only Individual Customer accounts
        without parcel records can actually be deleted. Your own account and
        System Administrator accounts are protected.
    </p>

    <?php if ($message !== ''): ?>

        <div class="alert <?= h($messageType) ?>">
            <?= h($message) ?>
        </div>

    <?php endif; ?>

    <div class="user-table-wrapper">

        <?php if (count($users) > 0): ?>

            <table class="user-table">

                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>

                    <?php foreach ($users as $user): ?>

                        <tr>

                            <td>
                                <?= h($user['userId']) ?>
                            </td>

                            <td>
                                <?= h($user['username']) ?>
                            </td>

                            <td>
                                <span class="user-role">
                                    <?= h($roleLabels[$user['role']] ?? $user['role']) ?>
                                </span>
                            </td>

                            <td>

                                <form
                                    method="POST"
                                    onsubmit="return confirm('Permanently delete this account? It will only be deleted if it is allowed and has no parcel records. This action cannot be undone.');"
                                >

                                    <input
                                        type="hidden"
                                        name="csrf_token"
                                        value="<?= h(csrf_token()) ?>"
                                    >

                                    <input
                                        type="hidden"
                                        name="userId"
                                        value="<?= h($user['userId']) ?>"
                                    >

                                    <input
                                        type="hidden"
                                        name="currentRole"
                                        value="<?= h($roleFilter) ?>"
                                    >

                                    <input
                                        type="hidden"
                                        name="currentSearch"
                                        value="<?= h($search) ?>"
                                    >

                                    <button
                                        type="submit"
                                        class="delete-button"
                                    >
                                        Delete
                                    </button>

                                </form>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        <?php else: ?>

            <div class="empty-state">
                No user records were found for the selected filter.
            </div>

        <?php endif; ?>

    </div>

    <p class="user-count">
        Showing <?= count($users) ?> user record(s).
    </p>

</main>

</body>
</html>