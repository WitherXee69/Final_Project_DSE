<?php

require_once '../config/database.php';

$search = $_GET['search'] ?? '';

$customer = null;

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && ($_POST['action'] ?? '') === 'update') {

    $userId = $_POST['userId'];
    $email = trim($_POST['email']);
    $contactNumber = trim($_POST['contactNumber']);
    $address = trim($_POST['address']);
    $customerName = trim($_POST['customerName']);
    $contactPerson = trim($_POST['contactPerson'] ?? '');
    $nic = trim($_POST['nic'] ?? '');

    if (strlen($customerName) < 3) {

        $error = "Name / Company Name must contain at least 3 characters.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";

    } elseif (!preg_match("/^07[0-9]{8}$/", $contactNumber)) {

    $error = "Please enter a valid contact number.";

    } elseif (!empty($contactPerson) && !preg_match("/^[A-Za-z ]+$/", $contactPerson)) {

        $error = "Contact person name can only contain letters and spaces.";

    }

    if(empty($error)) {

        $updateCustomer = $pdo->prepare("UPDATE Customer SET email = ?, contactNumber = ?, address = ? WHERE userId = ?");
        $updateCustomer->execute([$email, $contactNumber, $address, $userId]);

        if (!empty($contactPerson)) {

            $updateCorporate = $pdo->prepare("UPDATE CorporateCustomer SET companyName = ?, contactPerson = ? WHERE userId = ?");
            $updateCorporate->execute([$customerName, $contactPerson, $userId]);

        } else {

            $updateIndividual = $pdo->prepare("UPDATE IndividualCustomer SET name = ?, nic = ? WHERE userId = ?");
            $updateIndividual->execute([$customerName, $nic, $userId]);
        }

        echo "<script> alert('Customer updated successfully.'); </script>";

        $search = $userId;

        $stmt = $pdo->prepare("SELECT c.userId, c.email, c.contactNumber, c.address, ic.name AS individualName, ic.nic, cc.companyName, cc.contactPerson FROM Customer c LEFT JOIN IndividualCustomer ic ON c.userId = ic.userId LEFT JOIN CorporateCustomer cc ON c.userId = cc.userId WHERE c.userId = ?");

        $stmt->execute([$search]);

        $customer = $stmt->fetch();

    }

}

if (!empty($search) && !preg_match('/^USR-[0-9]{6}$/', $search)) {

    $error = "Please enter a valid Customer ID (e.g. USR-000013).";

} elseif (!empty($search)) {

    $stmt = $pdo->prepare("SELECT c.userId, c.email, c.contactNumber, c.address, ic.name AS individualName, ic.nic, cc.companyName, cc.contactPerson FROM Customer c LEFT JOIN IndividualCustomer ic ON c.userId = ic.userId LEFT JOIN CorporateCustomer cc ON c.userId = cc.userId WHERE c.userId = ?");

    $stmt->execute([$search]);

    $customer = $stmt->fetch();

    if (!$customer && !empty($search)) {

        $error = "Customer not found.";
            
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

            <span class="pill">UPDATE CUSTOMER · HELMS</span>

            <h1>
                Update<br>
                <span>Customer Records</span>
            </h1>

            <p>Search individual and corporate customers, review their information and update customer details.</p>

            <div class="login-points">

                <div class="login-point">

                    <span class="point-icon">✓</span>
                    <span>Search existing customers</span>

                </div>

                <div class="login-point">

                    <span class="point-icon">✓</span>
                    <span>Update individual customers</span>

                </div>

                <div class="login-point">

                    <span class="point-icon">✓</span>
                    <span>Update corporate customers</span>

                </div>

                <div class="login-point">

                    <span class="point-icon">✓</span>
                    <span>Maintain customer records</span>

                </div>

            </div>

        </section>

        <section class="login-card">

            <div class="login-card-head">

                <div class="login-symbol">✎</div>
                <h2>Search Customer</h2>
                <p>Enter a Customer ID to load customer information.</p>

            </div>

            <?php if (!empty($error)): ?>

                <div class="login-notice show"><?= $error ?></div>
                <br>

            <?php endif; ?>

            <form class="login-form" method="get">

                <div class="form-row">

                    <label for="search">Customer ID</label>
                    <input id="search" name="search" type="text" value="<?= htmlspecialchars($search) ?>" placeholder="Enter Customer ID (e.g. USR-000013)" required>

                </div>

                <button class="btn btn-primary login-submit" type="submit">Load Customer</button>

            </form>

                <?php if ($customer): ?>

                <hr style="margin:20px 0;">

                <form class="login-form" method="post" action="">

                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="userId" value="<?= $customer['userId'] ?>">

                    <div class="form-row">

                        <label>Name / Company Name</label>
                        <input type="text" name="customerName" value="<?= $customer['individualName'] ?? $customer['companyName'] ?>">

                    </div>

                    <?php if (empty($customer['companyName'])): ?>

                    <div class="form-row">

                        <label>NIC</label>
                        <input type="text" name="nic" value="<?= $customer['nic'] ?? '' ?>">

                    </div>

                    <?php endif; ?>

                    <?php if (!empty($customer['companyName'])): ?>

                    <div class="form-row">

                        <label>Contact Person</label>
                        <input type="text" name="contactPerson" value="<?= $customer['contactPerson'] ?>">

                    </div>

                    <?php endif; ?>

                    <div class="form-row">

                        <label>Email</label>
                        <input type="email" name="email" value="<?= $customer['email'] ?>">

                    </div>

                    <div class="form-row">

                        <label>Contact Number</label>
                        <input type="text" name="contactNumber" value="<?= $customer['contactNumber'] ?>">

                    </div>

                    <div class="form-row">

                        <label>Address</label>
                        <textarea name="address" rows="3"><?= $customer['address'] ?></textarea>

                    </div>

                    <div style="display:flex; gap:10px;">

                            <button class="btn btn-primary login-submit" type="submit">Update Customer</button>
                            <button style="background: #0b1f3a; color:white;" class="btn btn-primary" type="button" onclick="window.location.href='update_customer.php'">Clear</button>

                        </div>

                </form>

                <?php endif; ?>

        </section>

    </main>

    <footer class="login-footer">

        <span>&copy; <?= date('Y') ?> Henowic Inventors (Pvt) Ltd.</span>
        <span>HELMS · NIBM DSE Final Project</span>

    </footer>

</body>
</html>