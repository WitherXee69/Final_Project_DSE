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

$error = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Verify CSRF token before processing the form.
    if (!verify_csrf()) {

        $error = "Invalid form submission. Please refresh the page and try again.";

    } else {

        $companyName = trim($_POST["companyName"] ?? '');
        $contactPerson = trim($_POST["contactPerson"] ?? '');
        $email = trim($_POST["email"] ?? '');
        $contactNumber = trim($_POST["contactNumber"] ?? '');
        $address = trim($_POST["address"] ?? '');
        $username = trim($_POST["username"] ?? '');
        $password = $_POST["password"] ?? '';
        $confirmPassword = $_POST["confirmPassword"] ?? '';

        if (strlen($companyName) < 3) {

            $error = "Name / Company Name must contain at least 3 characters.";

        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

            $error = "Please enter a valid email address.";

        } elseif (!preg_match("/^07[0-9]{8}$/", $contactNumber)) {

            $error = "Please enter a valid contact number.";

        } elseif (!preg_match("/^[A-Za-z ]+$/", $contactPerson)) {

            $error = "Contact person name can only contain letters and spaces.";

        } elseif (strlen($username) < 4) {

            $error = "Username must contain at least 4 characters.";

        } elseif (strlen($password) < 8) {

            $error = "Password must contain at least 8 characters.";

        } elseif ($password !== $confirmPassword) {

            $error = "Passwords do not match.";

        } else {

            try {

                $checkUser = $pdo->prepare(
                    "SELECT COUNT(*) FROM User WHERE username = ?"
                );

                $checkUser->execute([$username]);

                if ($checkUser->fetchColumn() > 0) {

                    $error = "Username already exists.";

                } else {

                    $maxId = $pdo->query(
                        "SELECT MAX(userId) FROM User"
                    )->fetchColumn();

                    $number = (int) substr($maxId, 4);
                    $newUserId = 'USR-' . str_pad(
                        $number + 1,
                        6,
                        '0',
                        STR_PAD_LEFT
                    );

                    $passwordHash = password_hash(
                        $password,
                        PASSWORD_DEFAULT
                    );

                    $pdo->beginTransaction();

                    // Create the user account with the correct role.
                    $insertUser = $pdo->prepare("
                        INSERT INTO User
                        (userId, username, passwordHash, role, adminId)
                        VALUES (?, ?, ?, ?, ?)
                    ");

                    $insertUser->execute([
                        $newUserId,
                        $username,
                        $passwordHash,
                        'CorporateCustomer',
                        'USR-000001'
                    ]);

                    // Create the general customer record.
                    $insertCustomer = $pdo->prepare("
                        INSERT INTO Customer
                        (userId, contactNumber, email, address)
                        VALUES (?, ?, ?, ?)
                    ");

                    $insertCustomer->execute([
                        $newUserId,
                        $contactNumber,
                        $email,
                        $address
                    ]);

                    // Link the corporate customer to the logged-in manager.
                    $insertCorporate = $pdo->prepare("
                        INSERT INTO CorporateCustomer
                        (userId, companyName, contactPerson, csMgrId)
                        VALUES (?, ?, ?, ?)
                    ");

                    $insertCorporate->execute([
                        $newUserId,
                        $companyName,
                        $contactPerson,
                        $_SESSION['user_id']
                    ]);

                    $pdo->commit();

                    echo "<script>
                        alert('Corporate Customer Registered Successfully. Customer ID: " . $newUserId . "');
                    </script>";
                }

            } catch (Exception $e) {

                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }

                $error = "Registration failed. Please check the information and try again.";

                // Log the actual error for debugging without exposing it to users.
                error_log($e->getMessage());
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Register Corporate Customer | HELMS</title>

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

                <a class="link" href="update_customer.php">
                    Update Customer
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
                    Register a
                    <span>Corporate Customer</span>
                </h1>

                <p>
                    Create a new corporate customer account and add
                    their business information to the HELMS system.
                </p>

            </div>

        </section>

        <!-- Form Area -->
        <div class="container csm-form-container">

            <div class="csm-form-card">

                <div class="csm-form-card-head">

                    <div class="csm-form-icon">+</div>

                    <div>
                        <span class="section-eyebrow">
                            NEW CUSTOMER
                        </span>

                        <h2>
                            Corporate Customer Registration
                        </h2>

                        <p>
                            Enter the company's information and account details below.
                        </p>
                    </div>

                </div>

                <?php if (!empty($error)): ?>

                    <div class="login-notice show csm-form-notice">
                        <?= h($error) ?>
                    </div>

                <?php endif; ?>

                <form class="csm-form" method="post" action="">

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= h(csrf_token()) ?>"
                    >

                    <!-- Company Information -->
                    <div class="form-section">

                        <div class="form-section-heading">

                            <span class="form-section-number">01</span>

                            <div>
                                <h3>Company Information</h3>
                                <p>Enter the basic details of the corporate customer.</p>
                            </div>

                        </div>

                        <div class="form-grid">

                            <div class="form-row">

                                <label for="companyName">
                                    Company Name
                                </label>

                                <input
                                    id="companyName"
                                    name="companyName"
                                    type="text"
                                    placeholder="Enter company name"
                                    value="<?= h($_POST['companyName'] ?? '') ?>"
                                    required
                                >

                            </div>

                            <div class="form-row">

                                <label for="contactPerson">
                                    Contact Person
                                </label>

                                <input
                                    id="contactPerson"
                                    name="contactPerson"
                                    type="text"
                                    placeholder="Enter contact person"
                                    value="<?= h($_POST['contactPerson'] ?? '') ?>"
                                    required
                                >

                            </div>

                            <div class="form-row">

                                <label for="email">
                                    Company Email
                                </label>

                                <input
                                    id="email"
                                    name="email"
                                    type="email"
                                    placeholder="company@example.com"
                                    value="<?= h($_POST['email'] ?? '') ?>"
                                    required
                                >

                            </div>

                            <div class="form-row">

                                <label for="contactNumber">
                                    Contact Number
                                </label>

                                <input
                                    id="contactNumber"
                                    name="contactNumber"
                                    type="text"
                                    placeholder="07XXXXXXXX"
                                    value="<?= h($_POST['contactNumber'] ?? '') ?>"
                                    required
                                >

                            </div>

                            <div class="form-row form-row-full">

                                <label for="address">
                                    Company Address
                                </label>

                                <textarea
                                    id="address"
                                    name="address"
                                    rows="3"
                                    placeholder="Enter company address"
                                    required
                                ><?= h($_POST['address'] ?? '') ?></textarea>

                            </div>

                        </div>

                    </div>

                    <!-- Account Information -->
                    <div class="form-section">

                        <div class="form-section-heading">

                            <span class="form-section-number">02</span>

                            <div>
                                <h3>Account Information</h3>
                                <p>Create the login credentials for the customer.</p>
                            </div>

                        </div>

                        <div class="form-grid">

                            <div class="form-row form-row-full">

                                <label for="username">
                                    Username
                                </label>

                                <input
                                    id="username"
                                    name="username"
                                    type="text"
                                    placeholder="Enter username"
                                    value="<?= h($_POST['username'] ?? '') ?>"
                                    autocomplete="off"
                                    required
                                >

                            </div>

                            <div class="form-row">

                                <label for="password">
                                    Password
                                </label>

                                <input
                                    id="password"
                                    name="password"
                                    type="password"
                                    placeholder="Enter password"
                                    autocomplete="new-password"
                                    required
                                >

                            </div>

                            <div class="form-row">

                                <label for="confirmPassword">
                                    Confirm Password
                                </label>

                                <input
                                    id="confirmPassword"
                                    name="confirmPassword"
                                    type="password"
                                    placeholder="Confirm password"
                                    autocomplete="new-password"
                                    required
                                >

                            </div>

                        </div>

                    </div>

                    <!-- Form Actions -->
                    <div class="csm-form-actions">

                        <button
                            class="btn btn-primary"
                            type="submit"
                        >
                            Register Corporate Customer
                        </button>

                        <button
                            class="btn btn-outline"
                            type="button"
                            onclick="window.location.href='register_corporate_customer.php'"
                        >
                            Clear
                        </button>

                    </div>

                </form>

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