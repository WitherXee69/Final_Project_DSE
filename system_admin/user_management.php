<?php
session_start();

if (empty($_SESSION['user_id'])) {
    header('Location: ../login.php?portal=staff');
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/user_methods.php';

$currentUserId = (string) $_SESSION['user_id'];
$message = '';
$messageType = '';
$roleLabels = SA_ROLE_LABELS;
$allowedRoles = array_merge(array_keys($roleLabels), ['ALL']);

try {
    sa_require_admin($pdo, $currentUserId);
} catch (RuntimeException $e) {
    http_response_code(403);
    exit($e->getMessage());
} catch (PDOException $e) {
    error_log('User management authorization error: ' . $e->getMessage());
    http_response_code(500);
    exit('Unable to verify administrator access.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $message = 'Invalid security token. Please try again.';
        $messageType = 'error';
    } else {
        try {
            deactivateUser($pdo, $currentUserId, (string)($_POST['userId'] ?? ''));

            $redirectRole = trim((string)($_POST['currentRole'] ?? ''));
            if (!in_array($redirectRole, $allowedRoles, true)) {
                $redirectRole = 'IndividualCustomer';
            }

            header('Location: user_management.php?' . http_build_query([
                'status' => 'deleted',
                'role' => $redirectRole,
                'search' => trim((string)($_POST['currentSearch'] ?? '')),
            ]));
            exit;
        } catch (RuntimeException $e) {
            $message = $e->getMessage();
            $messageType = 'error';
        }
    }
}

if (isset($_GET['status']) && $_GET['status'] === 'deleted') {
    $message = 'Individual Customer account permanently deleted successfully.';
    $messageType = 'success';
}

$roleFilter = trim((string)($_GET['role'] ?? $_POST['currentRole'] ?? 'IndividualCustomer'));
if (!in_array($roleFilter, $allowedRoles, true)) {
    $roleFilter = 'IndividualCustomer';
}
$search = trim((string)($_GET['search'] ?? $_POST['currentSearch'] ?? ''));

try {
    $users = viewUser($pdo, $roleFilter, $search);
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
                <a href="user_management.php" class="active">User Management</a>
            </nav>

            <div class="system-admin-account">
                <div class="system-admin-account-info">
                    <strong>System Admin</strong>
                    <small>Account Management</small>
                </div>
                <a class="system-admin-logout" href="../logout.php">Logout</a>
            </div>
        </div>
    </header>

    <main class="container user-management-content">
        <a href="index.php" class="back-link">← Back to Dashboard</a>

        <div class="user-management-heading">
            <div>
                <span class="system-admin-eyebrow">SYSTEM ADMINISTRATION</span>
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
                        <option value="ALL" <?= $roleFilter === 'ALL' ? 'selected' : '' ?>>
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

                <button type="submit" class="filter-button">Apply</button>
                <a href="user_management.php" class="clear-filter">Reset</a>
            </form>
        </div>

        <p class="filter-description">
            By default, this page displays Individual Customer accounts.
            Every account has a Delete button, but only Individual Customer accounts
            without parcel records can actually be deleted. Your own account and
            System Administrator accounts are protected.
        </p>

        <?php if ($message !== ''): ?>
            <div class="alert <?= h($messageType) ?>"><?= h($message) ?></div>
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
                                <td><?= h($user['userId']) ?></td>
                                <td><?= h($user['username']) ?></td>
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
                                        <button type="submit" class="delete-button">Delete</button>
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

        <p class="user-count">Showing <?= count($users) ?> user record(s).</p>
    </main>
</body>
</html>
