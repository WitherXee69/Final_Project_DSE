<?php

require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';

// Authentication and role verification.
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
$success = false;

if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST['action'] ?? '') === 'update') {

    if (!verify_csrf()) {

        $error = "Invalid form submission. Please refresh the page and try again.";

    } else {

        $userId = trim($_POST['userId'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $contactNumber = trim($_POST['contactNumber'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $customerName = trim($_POST['customerName'] ?? '');
        $contactPerson = trim($_POST['contactPerson'] ?? '');
        $nic = trim($_POST['nic'] ?? '');

        // The presence of contactPerson identifies a corporate customer.
        $isCorporate = array_key_exists('contactPerson', $_POST);

        if (!preg_match('/^USR-[0-9]{6}$/', $userId)) {

            $error = "Invalid Customer ID.";

        } elseif (strlen($customerName) < 3) {

            $error = "Name / Company Name must contain at least 3 characters.";

        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

            $error = "Please enter a valid email address.";

        } elseif (!preg_match("/^07[0-9]{8}$/", $contactNumber)) {

            $error = "Please enter a valid contact number.";

        } elseif ($isCorporate && !empty($contactPerson) && !preg_match("/^[A-Za-z ]+$/", $contactPerson)) {

            $error = "Contact person name can only contain letters and spaces.";

        } else {

            try {

                // Make sure the customer actually exists.
                $checkCustomer = $pdo->prepare("
                    SELECT
                        c.userId,
                        ic.userId AS individualId,
                        cc.userId AS corporateId
                    FROM Customer c
                    LEFT JOIN IndividualCustomer ic ON c.userId = ic.userId
                    LEFT JOIN CorporateCustomer cc ON c.userId = cc.userId
                    WHERE c.userId = ?
                ");

                $checkCustomer->execute([$userId]);
                $existingCustomer = $checkCustomer->fetch();

                if (!$existingCustomer) {

                    $error = "Customer not found.";

                } elseif ($isCorporate && empty($existingCustomer['corporateId'])) {

                    $error = "This customer is not registered as a corporate customer.";

                } elseif (!$isCorporate && empty($existingCustomer['individualId'])) {

                    $error = "This customer is not registered as an individual customer.";

                } else {

                    $pdo->beginTransaction();

                    $updateCustomer = $pdo->prepare("
                        UPDATE Customer
                        SET email = ?, contactNumber = ?, address = ?
                        WHERE userId = ?
                    ");

                    $updateCustomer->execute([
                        $email,
                        $contactNumber,
                        $address,
                        $userId
                    ]);

                    if ($isCorporate) {

                        $updateCorporate = $pdo->prepare("
                            UPDATE CorporateCustomer
                            SET companyName = ?, contactPerson = ?
                            WHERE userId = ?
                        ");

                        $updateCorporate->execute([
                            $customerName,
                            $contactPerson,
                            $userId
                        ]);

                    } else {

                        $updateIndividual = $pdo->prepare("
                            UPDATE IndividualCustomer
                            SET name = ?, nic = ?
                            WHERE userId = ?
                        ");

                        $updateIndividual->execute([
                            $customerName,
                            $nic,
                            $userId
                        ]);
                    }

                    $pdo->commit();

                    $success = true;
                    $search = $userId;
                }

            } catch (Exception $e) {

                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }

                $error = "Customer update failed. Please try again.";

                error_log($e->getMessage());
            }
        }
    }
}

// Load the customer record.
if (!empty($search)) {

    if (!preg_match('/^USR-[0-9]{6}$/', $search)) {

        $error = "Please enter a valid Customer ID (e.g. USR-000013).";

    } else {

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
}

?>

<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Update Customer | HELMS</title>

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

                <a class="link" href="deactivate_customer.php">
                    Deactivate Customer
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
                    Update
                    <span>Customer Records</span>
                </h1>

                <p>
                    Search for an existing customer, review their information
                    and update their customer records through HELMS.
                </p>

            </div>

        </section>

        <div class="container csm-form-container">

            <div class="csm-form-card">

                <!-- Page Header -->
                <div class="csm-form-card-head">

                    <div class="csm-form-icon">✎</div>

                    <div>

                        <span class="section-eyebrow">
                            CUSTOMER RECORDS
                        </span>

                        <h2>
                            Update Customer
                        </h2>

                        <p>
                            Search for a customer using their Customer ID.
                        </p>

                    </div>

                </div>

                <?php if (!empty($error)): ?>

                    <div class="login-notice show csm-form-notice">
                        <?= h($error) ?>
                    </div>

                <?php endif; ?>

                <?php if ($success): ?>

                    <div class="csm-success-notice">
                        Customer updated successfully.
                    </div>

                <?php endif; ?>

                <!-- Customer Search -->
                <form class="csm-form search-customer-form" method="get">

                    <div class="form-section">

                        <div class="form-section-heading">

                            <span class="form-section-number">01</span>

                            <div>
                                <h3>Find Customer</h3>
                                <p>Enter the Customer ID to load their current information.</p>
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
                    <form class="csm-form" method="post" action="">

                        <input
                            type="hidden"
                            name="action"
                            value="update"
                        >

                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?= h(csrf_token()) ?>"
                        >

                        <input
                            type="hidden"
                            name="userId"
                            value="<?= h($customer['userId']) ?>"
                        >

                        <!-- Customer Information -->
                        <div class="form-section">

                            <div class="form-section-heading">

                                <span class="form-section-number">02</span>

                                <div>
                                    <h3>Customer Information</h3>
                                    <p>Update the customer's current information below.</p>
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

                            <div class="form-grid">

                                <div class="form-row">

                                    <label for="customerName">
                                        Name / Company Name
                                    </label>

                                    <input
                                        id="customerName"
                                        type="text"
                                        name="customerName"
                                        value="<?= h($customer['individualName'] ?? $customer['companyName'] ?? '') ?>"
                                        required
                                    >

                                </div>

                                <?php if ($customer['companyName'] === null): ?>

                                    <div class="form-row">

                                        <label for="nic">
                                            NIC
                                        </label>

                                        <input
                                            id="nic"
                                            type="text"
                                            name="nic"
                                            value="<?= h($customer['nic'] ?? '') ?>"
                                        >

                                    </div>

                                <?php endif; ?>

                                <?php if ($customer['companyName'] !== null): ?>

                                    <div class="form-row">

                                        <label for="contactPerson">
                                            Contact Person
                                        </label>

                                        <input
                                            id="contactPerson"
                                            type="text"
                                            name="contactPerson"
                                            value="<?= h($customer['contactPerson'] ?? '') ?>"
                                        >

                                    </div>

                                <?php endif; ?>

                                <div class="form-row">

                                    <label for="email">
                                        Email
                                    </label>

                                    <input
                                        id="email"
                                        type="email"
                                        name="email"
                                        value="<?= h($customer['email']) ?>"
                                        required
                                    >

                                </div>

                                <div class="form-row">

                                    <label for="contactNumber">
                                        Contact Number
                                    </label>

                                    <input
                                        id="contactNumber"
                                        type="text"
                                        name="contactNumber"
                                        value="<?= h($customer['contactNumber']) ?>"
                                        required
                                    >

                                </div>

                                <div class="form-row form-row-full">

                                    <label for="address">
                                        Address
                                    </label>

                                    <textarea
                                        id="address"
                                        name="address"
                                        rows="3"
                                        required
                                    ><?= h($customer['address']) ?></textarea>

                                </div>

                            </div>

                        </div>

                        <!-- Form Actions -->
                        <div class="csm-form-actions">

                            <button
                                class="btn btn-primary"
                                type="submit"
                            >
                                Update Customer
                            </button>

                            <button
                                class="btn btn-outline"
                                type="button"
                                onclick="window.location.href='update_customer.php'"
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