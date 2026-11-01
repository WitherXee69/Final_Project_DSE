<?php
require_once __DIR__ . '/config/auth.php';

require_login();

$role = $_SESSION['role'];
$name = $_SESSION['full_name'] ?? 'HELMS User';

// Use the existing warehouse dashboard.
if (in_array($role, ['WarehouseOfficer', 'WarehouseManager'], true)) {
    header('Location: warehouse/index.php');
    exit;
}

if (in_array($role, ['SystemAdmin'], true)) {
    header('Location: system_admin/index.php');
    exit;
}

if (in_array($role, ['Driver'], true)) {
    header('Location: driver/driver_dashboard.php');
    exit;
}

if (in_array($role, ['IndividualCustomer', 'CorporateCustomer'], true)) {
    header('Location: customer/index.php');
    exit;
}

if (in_array($role, ['TransportManager'], true)) {
    header('Location: transportMgr/transportMgr_dashboard.php');
    exit;
}

if (in_array($role, ['ManagingDirector'], true)) {
    header('Location: managing_director/index.php');
    exit;
}

if (in_array($role, ['CustomerServiceManager'], true)) {
    header('Location: customer_service_manager/customer_service_dashboard.php');
    exit;
}

$roleNames = [
    'SystemAdmin' => 'System Administrator',
    'ManagingDirector' => 'Managing Director',
    'CustomerServiceManager' => 'Customer Service Manager',
    'CustomerServiceOfficer' => 'Customer Service Officer',
    'FinanceManager' => 'Finance Manager',
    'FinanceOfficer' => 'Finance Officer',
    'TransportManager' => 'Transport Manager',
    'Driver' => 'Driver',
    'IndividualCustomer' => 'Individual Customer',
    'CorporateCustomer' => 'Corporate Customer'
];

$displayRole = $roleNames[$role] ?? $role;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard | HELMS</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<header>
    <div class="container nav">
        <a href="index.php" class="brand">
            <div class="logo">H</div>
            <div>
                <b>HELMS</b>
                <small>Henowic Inventors (Pvt) Ltd.</small>
            </div>
        </a>

        <nav class="menu">
            <a class="btn btn-ghost" href="index.php">Home</a>
            <a class="btn btn-ghost" href="logout.php">Logout</a>
        </nav>
    </div>
</header>

<main class="container" style="padding-top:60px;padding-bottom:60px;">
    <span class="pill"><?= h($displayRole) ?> PORTAL</span>

    <h1>Welcome, <?= h($name) ?>.</h1>

    <p>
        You are signed in to the Henowic Enterprise Logistics Management System.
    </p>

    <section class="login-card" style="max-width:650px;margin-top:30px;">
        <h2>Your account</h2>

        <p><strong>Name:</strong> <?= h($name) ?></p>
        <p><strong>Role:</strong> <?= h($displayRole) ?></p>
        <p><strong>User ID:</strong> <?= h($_SESSION['user_id']) ?></p>

        <div class="staff-note">
            Your authentication is active. The dedicated dashboard and operational
            features for this role can be connected here next.
        </div>
    </section>
</main>

</body>
</html>