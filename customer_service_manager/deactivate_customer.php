<?php

require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';

// Only logged-in Customer Service Managers can access this page.
if (empty($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

if (($_SESSION['role'] ?? '') !== 'CustomerServiceManager') {
    header('Location: ../dashboard.php');
    exit;
}

$search = trim($_GET['search'] ?? '');

$customer = null;
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && ($_POST['action'] ?? '') === 'delete') {

    $userId = trim($_POST['userId'] ?? '');

    // Verify the CSRF token before processing the deletion.
    if (!verify_csrf()) {

        $error = "Invalid or expired request. Please try again.";

    } elseif (!preg_match('/^USR-[0-9]{6}$/', $userId)) {

        $error = "Invalid Customer ID.";

    } else {

        // Make sure the submitted ID belongs to an existing customer.
        $checkCustomer = $pdo->prepare("SELECT userId FROM Customer WHERE userId = ?");
        $checkCustomer->execute([$userId]);

        if (!$checkCustomer->fetch()) {

            $error = "Customer not found.";

        } else {

            $search = $userId;

            try {

                $pdo->beginTransaction();

                $deleteCorporate = $pdo->prepare("DELETE FROM CorporateCustomer WHERE userId = ?");

                $deleteCorporate->execute([$userId]);

                $deleteIndividual = $pdo->prepare( "DELETE FROM IndividualCustomer WHERE userId = ?");

                $deleteIndividual->execute([$userId]);

                $deleteCustomer = $pdo->prepare( "DELETE FROM Customer WHERE userId = ?");

                $deleteCustomer->execute([$userId]);

                $deleteUser = $pdo->prepare("DELETE FROM User WHERE userId = ?");

                $deleteUser->execute([$userId]);

                $pdo->commit();

                echo "<script> alert('Customer deactivated successfully.'); window.location.href='deactivate_customer.php'; </script>";

                exit();

            } catch (Exception $e) {

                $pdo->rollBack();

                error_log("Customer deactivation failed: " . $e->getMessage());

                $error = "Unable to deactivate this customer. The operation was cancelled.";
            }
        }
    }
}

// Validate and search for a customer.
if (!empty($search) && !preg_match('/^USR-[0-9]{6}$/', $search)) {

    $error = "Please enter a valid Customer ID (e.g. USR-000013).";

} elseif (!empty($search)) {

    $stmt = $pdo->prepare("
        SELECT
            c.userId,
            c.email,
            c.contactNumber,
            c.address,
            ic.name AS individualName,
            ic.nic,
            cc.companyName,
            cc.contactPerson
        FROM Customer c
        LEFT JOIN IndividualCustomer ic ON c.userId = ic.userId
        LEFT JOIN CorporateCustomer cc ON c.userId = cc.userId
        WHERE c.userId = ?
    ");

    $stmt->execute([$search]);

    $customer = $stmt->fetch();

    if (!$customer) {

        $error = "Customer not found.";

    }

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Deactivate Customer | HELMS</title>

    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/customer_service_forms.css">

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

                <a class="link" href="customer_service_dashboard.php">
                    Dashboard
                </a>

                <a class="link" href="register_corporate_customer.php">
                    Register Customer
                </a>

                <a class="link" href="update_customer.php">
                    Update Customer
                </a>

                <a class="btn btn-ghost" href="../index.php">
                    Home
                </a>

            </nav>

        </div>

    </header>

    <main class="csm-form-page">

        <!-- Page Header -->
        <section class="csm-form-hero">

            <div class="container">

                <span class="csm-form-tag">
                    CUSTOMER MANAGEMENT
                </span>

                <h1>
                    Deactivate
                    <span>Customer Records</span>
                </h1>

                <p>
                    Find an existing customer, review their information
                    and deactivate their records from the HELMS system.
                </p>

            </div>

        </section>

        <div class="container csm-form-container">

            <div class="csm-form-card">

                <!-- Card Header -->
                <div class="csm-form-card-head">

                    <div class="csm-form-icon danger-form-icon">✖</div>

                    <div>

                        <span class="section-eyebrow">
                            CUSTOMER RECORDS
                        </span>

                        <h2>
                            Deactivate Customer
                        </h2>

                        <p>
                            Search using the Customer ID to review a customer record.
                        </p>

                    </div>

                </div>

                <?php if (!empty($error)): ?>

                    <div class="login-notice show csm-form-notice">
                        <?= h($error) ?>
                    </div>

                <?php endif; ?>

                <!-- Search Form -->
                <form class="csm-form" method="get">

                    <div class="form-section">

                        <div class="form-section-heading">

                            <span class="form-section-number">01</span>

                            <div>
                                <h3>Find Customer</h3>
                                <p>Enter the Customer ID to retrieve their information.</p>
                            </div>

                        </div>

                        <div class="customer-search-row">

                            <div class="form-row">

                                <label for="search">
                                    Customer ID
                                </label>

                                <input
                                    id="search"
                                    name="search"
                                    type="text"
                                    value="<?= h($search) ?>"
                                    placeholder="e.g. USR-000013"
                                    required
                                >

                            </div>

                            <button
                                class="btn btn-primary search-customer-btn"
                                type="submit"
                            >
                                Load Customer
                            </button>

                        </div>

                    </div>

                </form>

                <?php if ($customer): ?>

                    <div class="customer-loaded-divider"></div>

                    <!-- Customer Details -->
                    <div class="form-section">

                        <div class="form-section-heading">

                            <span class="form-section-number">02</span>

                            <div>
                                <h3>Customer Information</h3>
                                <p>Review the following details before proceeding.</p>
                            </div>

                        </div>

                        <div class="customer-record-banner">

                            <div>

                                <span>Customer ID</span>

                                <strong>
                                    <?= h($customer['userId']) ?>
                                </strong>

                            </div>

                            <div>

                                <span>Customer Type</span>

                                <strong>
                                    <?= $customer['companyName'] !== null ? 'Corporate Customer' : 'Individual Customer' ?>
                                </strong>

                            </div>

                        </div>

                        <div class="customer-details-grid">

                            <div class="customer-detail-item">

                                <span>Name / Company Name</span>

                                <strong>
                                    <?= h($customer['companyName'] !== null ? $customer['companyName'] : $customer['individualName']) ?>
                                </strong>

                            </div>

                            <?php if ($customer['companyName'] === null): ?>

                                <div class="customer-detail-item">

                                    <span>NIC</span>

                                    <strong>
                                        <?= h($customer['nic'] ?? '') ?>
                                    </strong>

                                </div>

                            <?php endif; ?>

                            <?php if ($customer['companyName'] !== null): ?>

                                <div class="customer-detail-item">

                                    <span>Contact Person</span>

                                    <strong>
                                        <?= h($customer['contactPerson'] ?? '') ?>
                                    </strong>

                                </div>

                            <?php endif; ?>

                            <div class="customer-detail-item">

                                <span>Email Address</span>

                                <strong>
                                    <?= h($customer['email']) ?>
                                </strong>

                            </div>

                            <div class="customer-detail-item">

                                <span>Contact Number</span>

                                <strong>
                                    <?= h($customer['contactNumber']) ?>
                                </strong>

                            </div>

                            <div class="customer-detail-item customer-detail-full">

                                <span>Address</span>

                                <strong>
                                    <?= h($customer['address']) ?>
                                </strong>

                            </div>

                        </div>

                    </div>

                    <!-- Warning -->
                    <div class="deactivation-warning">

                        <div class="warning-icon">!</div>

                        <div>

                            <strong>Permanent Deactivation</strong>

                            <p>
                                Deactivating this customer will permanently remove
                                their customer records from the system. This action
                                cannot be undone.
                            </p>

                        </div>

                    </div>

                    <!-- Deactivation Form -->
                    <form method="post" class="deactivation-form">

                        <input type="hidden" name="action" value="delete">

                        <input
                            type="hidden"
                            name="userId"
                            value="<?= h($customer['userId']) ?>"
                        >

                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?= h(csrf_token()) ?>"
                        >

                        <div class="csm-form-actions">

                            <button
                                class="btn btn-danger"
                                type="submit"
                                onclick="return confirm('Are you sure you want to permanently deactivate this customer?');"
                            >
                                Deactivate Customer
                            </button>

                            <button
                                class="btn btn-outline"
                                type="button"
                                onclick="window.location.href='deactivate_customer.php'"
                            >
                                Clear
                            </button>

                        </div>

                    </form>

                <?php endif; ?>

            </div>

        </div>

    </main>

    <footer class="csm-form-footer">

        <div class="container">

            <span>
                &copy; <?= date('Y') ?> Henowic Inventors (Pvt) Ltd.
            </span>

            <span>
                HELMS · NIBM DSE Final Project
            </span>

        </div>

    </footer>

</body>

</html>
