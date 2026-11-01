<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php?portal=staff');
    exit;
}

require_once __DIR__ . '/../config/database.php';

// Get the logged-in Transport Manager ID.
$transportMgrId = $_SESSION['user_id'];

// Load the action functions.
require_once __DIR__ . '/actions/register_driver.php';
require_once __DIR__ . '/actions/update_driver.php';
require_once __DIR__ . '/actions/delete_driver.php';
require_once __DIR__ . '/actions/view_driver_status.php';

function h($value)
{
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

function driverStatusClass($status)
{
    $status = strtoupper(trim($status));

    if ($status === 'AVAILABLE') {
        return 'status-available';
    }

    if ($status === 'ON_DUTY') {
        return 'status-on-duty';
    }

    if ($status === 'OFF_DUTY') {
        return 'status-off-duty';
    }

    return 'status-muted';
}

// Generate CSRF token.
if (empty($_SESSION['transportMgr_csrf_token'])) {
    $_SESSION['transportMgr_csrf_token'] = bin2hex(random_bytes(32));
}

$csrfToken = $_SESSION['transportMgr_csrf_token'];

// Retrieve flash messages.
$transportMgrMessage = $_SESSION['transportMgr_message'] ?? null;
$transportMgrMessageType = $_SESSION['transportMgr_message_type'] ?? 'success';

unset($_SESSION['transportMgr_message']);
unset($_SESSION['transportMgr_message_type']);

if (!in_array($transportMgrMessageType, ['success', 'error'], true)) {
    $transportMgrMessageType = 'success';
}

// Retrieve Transport Manager information and drivers.
try {

    // Get Transport Manager details.
    $stmt = $pdo->prepare("
        SELECT
            u.username,
            u.role,
            tm.region,
            tm.officeNumber
        FROM User u
        INNER JOIN TransportManager tm
            ON tm.userId = u.userId
        WHERE u.userId = ?
        LIMIT 1
    ");

    $stmt->execute([$transportMgrId]);

    $transportManager = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$transportManager) {
        http_response_code(403);
        exit('Access denied. This account is not registered as a Transport Manager.');
    }

    // Get drivers assigned to this Transport Manager.
    $drivers = viewDriverStatus($pdo, $transportMgrId);

} catch (PDOException $e) {

    error_log('Transport Manager dashboard error: ' . $e->getMessage());

    http_response_code(500);

    exit('Unable to load the Transport Manager dashboard right now.');
}

$managerName = $transportManager['username'];

$totalDrivers = count($drivers);

$availableDrivers = 0;
$onDutyDrivers = 0;
$offDutyDrivers = 0;

foreach ($drivers as $driver) {

    if ($driver['status'] === 'AVAILABLE') {
        $availableDrivers++;
    } elseif ($driver['status'] === 'ON_DUTY') {
        $onDutyDrivers++;
    } elseif ($driver['status'] === 'OFF_DUTY') {
        $offDutyDrivers++;
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Transport Manager Dashboard | HELMS</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="../assets/css/style.css">

    <link rel="stylesheet" href="../assets/css/transportMgr-dashboard.css">

</head>

<body class="transport-manager-page">

<header class="tm-header">

    <div class="container tm-nav">

        <a href="../index.php" class="brand">

            <div class="logo">H</div>

            <div>
                <b>HELMS</b>
                <small>Henowic Inventors (Pvt) Ltd.</small>
            </div>

        </a>

        <nav class="tm-menu">

            <a href="transportMgr_dashboard.php" class="active">
                Dashboard
            </a>

            <a href="#drivers">
                Drivers
            </a>

            <a href="#register-driver">
                Register Driver
            </a>

        </nav>

        <div class="tm-account">

            <span class="tm-avatar">
                <?= h(strtoupper(substr($managerName, 0, 1))) ?>
            </span>

            <span class="tm-account-name">
                <?= h($managerName) ?>
            </span>

            <a href="../logout.php" class="tm-logout">
                Logout
            </a>

        </div>

    </div>

</header>


<main class="container tm-main">

    <!-- Flash message -->

    <?php if ($transportMgrMessage): ?>

        <div class="tm-message <?= h($transportMgrMessageType) ?>">
            <?= h($transportMgrMessage) ?>
        </div>

    <?php endif; ?>


    <!-- Dashboard heading -->

    <section class="tm-page-heading">

        <div>

            <span class="tm-eyebrow">TRANSPORT MANAGEMENT PORTAL</span>

            <h1>
                Welcome back, <?= h($managerName) ?>
            </h1>

            <p>
                Manage your drivers and monitor their availability.
            </p>

        </div>

    </section>


    <!-- Transport Manager information -->

    <section class="tm-profile">

        <div class="tm-section-title">

            <h2>Transport Manager Information</h2>

            <span>YOUR PROFILE</span>

        </div>

        <div class="tm-profile-details">

            <div class="tm-profile-item">

                <span>Username</span>

                <strong><?= h($managerName) ?></strong>

            </div>

            <div class="tm-profile-item">

                <span>Region</span>

                <strong><?= h($transportManager['region']) ?></strong>

            </div>

            <div class="tm-profile-item">

                <span>Office Number</span>

                <strong><?= h($transportManager['officeNumber']) ?></strong>

            </div>

        </div>

    </section>


    <!-- Driver statistics -->

    <section class="tm-statistics">

        <article class="tm-stat-card">

            <div class="tm-stat-icon stat-navy">▤</div>

            <div>
                <span>Total Drivers</span>
                <strong><?= $totalDrivers ?></strong>
                <small>Assigned to you</small>
            </div>

        </article>

        <article class="tm-stat-card">

            <div class="tm-stat-icon stat-green">✓</div>

            <div>
                <span>Available Drivers</span>
                <strong><?= $availableDrivers ?></strong>
                <small>Ready for assignments</small>
            </div>

        </article>

        <article class="tm-stat-card">

            <div class="tm-stat-icon stat-blue">↗</div>

            <div>
                <span>On Duty Drivers</span>
                <strong><?= $onDutyDrivers ?></strong>
                <small>Currently on duty</small>
            </div>

        </article>

        <article class="tm-stat-card">

            <div class="tm-stat-icon stat-muted">—</div>

            <div>
                <span>Off Duty Drivers</span>
                <strong><?= $offDutyDrivers ?></strong>
                <small>Currently off duty</small>
            </div>

        </article>

    </section>


    <!-- Register Driver -->

    <section class="tm-section" id="register-driver">

        <div class="tm-section-heading">

            <div>
                <span class="tm-section-eyebrow">DRIVER MANAGEMENT</span>
                <h2>Register a Driver</h2>
                <p>Create a new driver account and assign it to your region.</p>
            </div>

        </div>

        <form
            method="POST"
            action="transportMgr_actions.php"
            class="tm-form"
        >

            <input type="hidden" name="csrf_token" value="<?= h($csrfToken) ?>">

            <input type="hidden" name="action" value="register_driver">

            <div class="tm-form-group">

                <label for="username">Username</label>

                <input
                    type="text"
                    id="username"
                    name="username"
                    maxlength="50"
                    placeholder="Enter username"
                    required
                >

            </div>

            <div class="tm-form-group">

                <label for="password">Password</label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Enter password"
                    required
                >

            </div>

            <div class="tm-form-group">

                <label for="licenseNumber">License Number</label>

                <input
                    type="text"
                    id="licenseNumber"
                    name="licenseNumber"
                    maxlength="50"
                    placeholder="Enter license number"
                    required
                >

            </div>

            <div class="tm-form-group">

                <label for="contactNumber">Contact Number</label>

                <input
                    type="text"
                    id="contactNumber"
                    name="contactNumber"
                    maxlength="30"
                    placeholder="Enter contact number"
                    required
                >

            </div>

            <div class="tm-form-group">

                <label for="registerStatus">Driver Status</label>

                <select id="registerStatus" name="status" required>

                    <option value="AVAILABLE">Available</option>

                    <option value="ON_DUTY">On Duty</option>

                    <option value="OFF_DUTY">Off Duty</option>

                </select>

            </div>

            <div class="tm-form-button">

                <button type="submit" class="btn btn-primary">
                    Register Driver
                </button>

            </div>

        </form>

    </section>


    <!-- Driver list -->

    <section class="tm-section" id="drivers">

        <div class="tm-section-heading">

            <div>
                <span class="tm-section-eyebrow">YOUR TEAM</span>
                <h2>Driver Management</h2>
                <p>View, update and delete drivers assigned to you.</p>
            </div>

            <span class="tm-count">
                <?= $totalDrivers ?> Drivers
            </span>

        </div>

        <?php if ($drivers): ?>

            <div class="tm-table-wrapper">

                <table class="tm-table">

                    <thead>

                        <tr>
                            <th>Driver ID</th>
                            <th>Username</th>
                            <th>License Number</th>
                            <th>Contact Number</th>
                            <th>Status</th>
                            <th>Update Details</th>
                            <th>Delete</th>
                        </tr>

                    </thead>

                    <tbody>

                    <?php foreach ($drivers as $driver): ?>

                        <tr>

                            <td>
                                <strong><?= h($driver['userId']) ?></strong>
                            </td>

                            <td>
                                <?= h($driver['username']) ?>
                            </td>

                            <td>
                                <?= h($driver['licenseNumber']) ?>
                            </td>

                            <td>
                                <?= h($driver['contactNumber']) ?>
                            </td>

                            <td>

                                <span class="tm-status <?= h(driverStatusClass($driver['status'])) ?>">
                                    <?= h($driver['status']) ?>
                                </span>

                            </td>

                            <td>

                                <form
                                    method="POST"
                                    action="transportMgr_actions.php"
                                    class="tm-update-form"
                                >

                                    <input
                                        type="hidden"
                                        name="csrf_token"
                                        value="<?= h($csrfToken) ?>"
                                    >

                                    <input
                                        type="hidden"
                                        name="action"
                                        value="update_driver"
                                    >

                                    <input
                                        type="hidden"
                                        name="driverId"
                                        value="<?= h($driver['userId']) ?>"
                                    >

                                    <input
                                        type="text"
                                        name="licenseNumber"
                                        value="<?= h($driver['licenseNumber']) ?>"
                                        maxlength="50"
                                        placeholder="License number"
                                        required
                                    >

                                    <input
                                        type="text"
                                        name="contactNumber"
                                        value="<?= h($driver['contactNumber']) ?>"
                                        maxlength="30"
                                        placeholder="Contact number"
                                        required
                                    >

                                    <select name="status" required>

                                        <option value="AVAILABLE"
                                            <?= $driver['status'] === 'AVAILABLE' ? 'selected' : '' ?>>
                                            Available
                                        </option>

                                        <option value="ON_DUTY"
                                            <?= $driver['status'] === 'ON_DUTY' ? 'selected' : '' ?>>
                                            On Duty
                                        </option>

                                        <option value="OFF_DUTY"
                                            <?= $driver['status'] === 'OFF_DUTY' ? 'selected' : '' ?>>
                                            Off Duty
                                        </option>

                                    </select>

                                    <button type="submit" class="btn btn-small btn-primary">
                                        Update
                                    </button>

                                </form>

                            </td>

                            <td>

                                <form
                                    method="POST"
                                    action="transportMgr_actions.php"
                                    onsubmit="return confirm('Are you sure you want to delete this driver?');"
                                >

                                    <input
                                        type="hidden"
                                        name="csrf_token"
                                        value="<?= h($csrfToken) ?>"
                                    >

                                    <input
                                        type="hidden"
                                        name="action"
                                        value="delete_driver"
                                    >

                                    <input
                                        type="hidden"
                                        name="driverId"
                                        value="<?= h($driver['userId']) ?>"
                                    >

                                    <button type="submit" class="btn btn-small btn-danger">
                                        Delete
                                    </button>

                                </form>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        <?php else: ?>

            <div class="tm-empty-state">

                <div class="tm-empty-icon">▤</div>

                <h3>No drivers registered</h3>

                <p>
                    Drivers assigned to you will appear here.
                    Use the registration form above to add a driver.
                </p>

            </div>

        <?php endif; ?>

    </section>

</main>


<footer class="tm-footer">

    <div class="container">

        <span>HELMS — Henowic Inventors (Pvt) Ltd.</span>

        <span>Transport Manager Portal</span>

    </div>

</footer>

</body>
</html>