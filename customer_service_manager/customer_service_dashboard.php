<?php

require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';

// Require an authenticated user.
if (empty($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

// Only Customer Service Managers can access this page.
if (($_SESSION['role'] ?? '') !== 'CustomerServiceManager') {
    header('Location: ../dashboard.php');
    exit;
}

$error = '';

$search = trim($_GET['search'] ?? '');

$searchTerm = "%" . $search . "%";

$stmt = $pdo->prepare("
    SELECT
        c.userId,
        c.email,
        c.contactNumber,
        ic.name AS individualName,
        cc.companyName
    FROM Customer c
    LEFT JOIN IndividualCustomer ic ON c.userId = ic.userId
    LEFT JOIN CorporateCustomer cc ON c.userId = cc.userId
    WHERE c.userId LIKE ?
       OR c.email LIKE ?
       OR ic.name LIKE ?
       OR cc.companyName LIKE ?
    ORDER BY c.userId
");

$stmt->execute([
    $searchTerm,
    $searchTerm,
    $searchTerm,
    $searchTerm
]);

$customers = $stmt->fetchAll();

if ($search !== '' && count($customers) === 0) {
    $error = "No customer records found matching your search.";
}

$totalCustomers = $pdo->query(
    "SELECT COUNT(*) FROM Customer"
)->fetchColumn();

$corporateCustomers = $pdo->query(
    "SELECT COUNT(*) FROM CorporateCustomer"
)->fetchColumn();

$individualCustomers = $totalCustomers - $corporateCustomers;

?>

<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Customer Service Manager Portal | HELMS</title>

    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/customer_service_dashboard.css">

</head>

<body>

    <!-- Navigation -->
    <header>
        <div class="container nav">

            <a href="../index.php" class="brand">
                <div class="logo">H</div>

                <div>
                    <b>HELMS</b>
                    <small>Customer Service Manager Portal</small>
                </div>
            </a>

            <nav class="menu">
                <a class="link" href="register_corporate_customer.php">Register Customer</a>
                <a class="link" href="update_customer.php">Update Customer</a>
                <a class="link" href="deactivate_customer.php">Deactivate Customer</a>
                <a class="btn btn-ghost" href="../index.php">Home</a>
            </nav>

        </div>
    </header>


    <main class="csm-dashboard">

        <!-- Hero Section -->
        <section class="csm-hero">

            <div class="container csm-hero-content">

                <div class="csm-tag">
                    CUSTOMER SERVICE MANAGER PORTAL
                </div>

                <h1>
                    Welcome back,
                    <span>Customer Service Manager.</span>
                </h1>

                <p>
                    Manage individual customers, corporate customers and
                    customer records from one central HELMS workspace.
                </p>

                <div class="csm-buttons">
                    <a href="register_corporate_customer.php" class="btn btn-primary">
                        Register Corporate Customer
                    </a>
                </div>

            </div>

        </section>


        <div class="container csm-content">

            <!-- Statistics -->
            <section class="stat-grid">

                <div class="stat-card">
                    <div class="stat-icon">👥</div>

                    <div class="number">
                        <?= h($totalCustomers) ?>
                    </div>

                    <div class="label">Total Customers</div>
                </div>


                <div class="stat-card">
                    <div class="stat-icon">🏢</div>

                    <div class="number">
                        <?= h($corporateCustomers) ?>
                    </div>

                    <div class="label">Corporate Customers</div>
                </div>


                <div class="stat-card">
                    <div class="stat-icon">👤</div>

                    <div class="number">
                        <?= h($individualCustomers) ?>
                    </div>

                    <div class="label">Individual Customers</div>
                </div>

            </section>


            <!-- Customer Directory -->
            <section class="directory-card" id="customers">

                <div class="section-heading">

                    <div>
                        <span class="section-eyebrow">CUSTOMER DIRECTORY</span>
                        <h2>Manage Customers</h2>

                        <p>
                            Search and view individual and corporate customer records.
                        </p>
                    </div>

                </div>


                <?php if (!empty($error)): ?>

                    <div class="login-notice show csm-notice">
                        <?= h($error) ?>
                    </div>

                <?php endif; ?>


                <!-- Search -->
                <form method="get" class="csm-search">

                    <div class="field">

                        <input
                            type="text"
                            name="search"
                            value="<?= h($search) ?>"
                            placeholder="Search by customer ID, name, company or email..."
                        >

                        <button type="submit" class="btn btn-primary">
                            Search
                        </button>

                        <a href="customer_service_dashboard.php" class="btn btn-outline">
                            Clear
                        </a>

                    </div>

                </form>


                <!-- Customer Table -->
                <div class="customer-table-wrapper">

                    <table class="customer-table">

                        <thead>
                            <tr>
                                <th>Customer ID</th>
                                <th>Customer Type</th>
                                <th>Name</th>
                                <th>Email Address</th>
                                <th>Phone Number</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php if (count($customers) > 0): ?>

                                <?php foreach ($customers as $customer): ?>

                                    <tr>

                                        <td class="customer-id">
                                            <?= h($customer['userId']) ?>
                                        </td>

                                        <td>
                                            <span class="customer-type <?= !empty($customer['companyName']) ? 'corporate' : 'individual' ?>">
                                                <?= !empty($customer['companyName']) ? 'Corporate' : 'Individual' ?>
                                            </span>
                                        </td>

                                        <td class="customer-name">
                                            <?= h($customer['individualName'] ?? $customer['companyName'] ?? '') ?>
                                        </td>

                                        <td>
                                            <?= h($customer['email']) ?>
                                        </td>

                                        <td>
                                            <?= h($customer['contactNumber']) ?>
                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            <?php else: ?>

                                <tr>
                                    <td colspan="5" class="empty-table">
                                        No customer records to display.
                                    </td>
                                </tr>

                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </section>


            <!-- Quick Actions -->
            <section class="quick-actions-section">

                <div class="section-heading">
                    <div>
                        <span class="section-eyebrow">MANAGEMENT ACTIONS</span>
                        <h2>Quick Actions</h2>

                        <p>
                            Access the main customer management functions.
                        </p>
                    </div>
                </div>


                <div class="quick-actions">

                    <div class="action-card">

                        <div class="action-icon">＋</div>

                        <h3>Register Corporate Customer</h3>

                        <p>
                            Create new corporate customer accounts and add their information to the system.
                        </p>

                        <a href="register_corporate_customer.php" class="btn btn-primary">
                            Register Customer
                        </a>

                    </div>


                    <div class="action-card">

                        <div class="action-icon">✎</div>

                        <h3>Update Customer</h3>

                        <p>
                            Find existing customers and update their information and contact details.
                        </p>

                        <a href="update_customer.php" class="btn btn-primary">
                            Update Customer
                        </a>

                    </div>


                    <div class="action-card">

                        <div class="action-icon">−</div>

                        <h3>Deactivate Customer</h3>

                        <p>
                            Remove customer accounts from active customer records when required.
                        </p>

                        <a href="deactivate_customer.php" class="btn btn-primary">
                            Deactivate Customer
                        </a>

                    </div>

                </div>

            </section>

        </div>

    </main>

</body>
</html>
