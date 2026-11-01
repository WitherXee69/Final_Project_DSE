<?php
require_once '../config/database.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $companyName = trim($_POST["companyName"]);
    $contactPerson = trim($_POST["contactPerson"]);
    $email = trim($_POST["email"]);
    $contactNumber = trim($_POST["contactNumber"]);
    $address = trim($_POST["address"]);
    $username = trim($_POST["username"]);
    $password = $_POST["password"];
    $confirmPassword = $_POST["confirmPassword"];

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

            $checkUser = $pdo->prepare("SELECT COUNT(*) FROM User WHERE username = ?");

            $checkUser->execute([$username]);

            if ($checkUser->fetchColumn() > 0) {

                $error = "Username already exists.";

            } else {

                $maxId = $pdo->query("SELECT MAX(userId) FROM User")->fetchColumn();

                $number = (int)substr($maxId, 4);
                $newUserId = 'USR-' . str_pad($number + 1, 6, '0', STR_PAD_LEFT);

                $passwordHash = password_hash($password, PASSWORD_DEFAULT);

                $pdo->beginTransaction();

                $insertUser = $pdo->prepare("INSERT INTO User (userId, username, passwordHash, role, adminId) VALUES (?, ?, ?, ?, ?)");

                $insertUser->execute([$newUserId, $username, $passwordHash, 'CUSTOMER', 'USR-000001']);

                $insertCustomer = $pdo->prepare("INSERT INTO Customer (userId, contactNumber, email, address) VALUES (?, ?, ?, ?)");

                $insertCustomer->execute([$newUserId, $contactNumber, $email, $address]);

                $insertCorporate = $pdo->prepare("INSERT INTO CorporateCustomer (userId, companyName, contactPerson, csMgrId) VALUES (?, ?, ?, ?)");

                $insertCorporate->execute([$newUserId, $companyName, $contactPerson, 'USR-000003']);

                $pdo->commit();

                echo "<script> alert('Corporate Customer Registered Successfully. Customer ID: $newUserId'); </script>";

            }

        } catch (Exception $e) {

            $pdo->rollBack();

            $error = $e->getMessage();

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

</head>

<body class="login-page">

    <header>

        <div class="container nav">

            <a href="index.php" class="brand">
                <div class="logo">H</div>

                <div>

                    <b>HELMS</b>
                    <small>Henowic Inventors (Pvt) Ltd.</small>

                </div>

            </a>

            <nav class="menu login-menu">

                <a class="link" href="customer_service_dashboard.php">Dashboard</a>
                <a class="btn btn-ghost" href="index.php">Home</a>

            </nav>

        </div>

    </header>

    <main class="login-main register-main">

        <section class="login-intro">

            <span class="pill">CORPORATE CUSTOMER REGISTRATION · HELMS</span>

            <h1>
                Register a<br>
                <span>Corporate Customer</span>
            </h1>

            <p>Create and manage corporate customer accounts, maintain business records and build long-term customer relationships through HELMS.</p>

            <div class="login-points">

                <div class="login-point">

                    <span class="point-icon">✓</span>
                    <span>Create corporate customer accounts</span>

                </div>

                <div class="login-point">

                    <span class="point-icon">✓</span>
                    <span>Manage business customer information</span>

                </div>

                <div class="login-point">

                    <span class="point-icon">✓</span>
                    <span>Track corporate customer records</span>

                </div>

                <div class="login-point">

                    <span class="point-icon">✓</span>
                    <span>Manage customer agreements</span>

                </div>

            </div>

        </section>

        <section class="login-card">

            <div class="login-card-head">

                <div class="login-symbol">+</div>
                <h2>Corporate Customer Registration</h2>
                <p>Enter corporate customer information below.</p>

            </div>

            <?php if (!empty($error)): ?>

                <div class="login-notice show"><?= $error ?></div>
                <br>

            <?php endif; ?>

            <form class="login-form" method="post" action="">

                <div class="form-row">

                    <label for="companyName">Company Name</label>
                    <input id="companyName" name="companyName" type="text" placeholder="Enter company name" value="<?= htmlspecialchars($_POST['companyName'] ?? '') ?>" required>

                </div>

                <div class="form-row">

                    <label for="contactPerson">Contact Person</label>
                    <input id="contactPerson" name="contactPerson" type="text" placeholder="Enter contact person" value="<?= htmlspecialchars($_POST['contactPerson'] ?? '') ?>" required>

                </div>

                <div class="form-row">

                    <label for="email">Company Email</label>
                    <input id="email" name="email" type="email" placeholder="company@example.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>

                </div>

                <div class="form-row">

                    <label for="contactNumber">Contact Number</label>
                    <input id="contactNumber" name="contactNumber" type="text" placeholder="+94XXXXXXXXX" value="<?= htmlspecialchars($_POST['contactNumber'] ?? '') ?>" required>

                </div>

                <div class="form-row">

                    <label for="address">Company Address</label>
                    <textarea id="address" name="address" rows="3" placeholder="Enter company address" required><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>

                </div>

                <div class="form-row">

                    <label for="username">Username</label>
                    <input id="username" name="username" type="text" placeholder="Enter username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" autocomplete="off" required>

                </div>

                <div class="form-row">

                    <label for="password">Password</label>
                    <input id="password" name="password" type="password" placeholder="Enter password" autocomplete="new-password" required>

                </div>

                <div class="form-row">

                    <label for="confirmPassword">Confirm Password</label>
                    <input id="confirmPassword" name="confirmPassword" type="password" placeholder="Confirm password" autocomplete="new-password" required>

                </div>

                <div style="display:flex; gap:10px;">

                    <button class="btn btn-primary login-submit" type="submit">Register Corporate Customer</button>

                    <button class="btn btn-outline login-submit" type="button" onclick="window.location.href='register_corporate_customer.php'">Clear</button>

                </div>

            </form>

        </section>

    </main>

    <footer class="login-footer">
        <span>&copy; <?= date('Y') ?> Henowic Inventors (Pvt) Ltd.</span>
        <span>HELMS · NIBM DSE Final Project</span>
    </footer>

</body>

</html>