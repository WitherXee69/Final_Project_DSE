<?php

require_once '../config/database.php';

$search = $_GET['search'] ?? '';

$customer = null;

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && ($_POST['action'] ?? '') === 'delete') {

    $userId = $_POST['userId'];

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

        echo "<script> alert('Error: " . addslashes($e->getMessage()) . "'); </script>";
    }
}

if (!empty($search) && !preg_match('/^USR-[0-9]{6}$/', $search)) {

    $error = "Please enter a valid Customer ID (e.g. USR-000013).";

} elseif (!empty($search)) {

    $stmt = $pdo->prepare("SELECT c.userId, c.email, c.contactNumber, c.address, ic.name AS individualName, ic.nic, cc.companyName, cc.contactPerson FROM Customer c LEFT JOIN IndividualCustomer ic ON c.userId = ic.userId LEFT JOIN CorporateCustomer cc ON c.userId = cc.userId WHERE c.userId = ?");

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

            <span class="pill">DEACTIVATE CUSTOMER · HELMS</span>

            <h1>
                Deactivate<br>
                <span>Customer Records</span>
            </h1>

            <p>Search individual and corporate customers, review their information and permanently remove customer records from the system.</p>

            <div class="login-points">

                <div class="login-point">

                    <span class="point-icon">✓</span>
                    <span>Search existing customers</span>

                </div>

                <div class="login-point">

                    <span class="point-icon">✓</span>
                    <span>Deactivate individual customers</span>

                </div>

                <div class="login-point">

                    <span class="point-icon">✓</span>
                    <span>Deactivate corporate customers</span>

                </div>

                <div class="login-point">

                    <span class="point-icon">✓</span>
                    <span>Permanently remove customer records</span>
                    
                </div>

            </div>

        </section>

        <section class="login-card">

            <div class="login-card-head">

                <div class="login-symbol">✖</div>
                <h2>Deactivate Customer</h2>
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

                    <div class="svc" style="padding:20px;">

                        <h3>Customer Information</h3>
                        <p><strong>Customer ID:</strong> <?= $customer['userId'] ?></p>

                        <p><strong>Name / Company:</strong><?= $customer['individualName'] ?? $customer['companyName'] ?></p>

                        <?php if (empty($customer['companyName'])): ?>

                            <p><strong>NIC:</strong> <?= $customer['nic'] ?? '' ?></p>

                        <?php endif; ?>

                        <?php if (!empty($customer['companyName'])): ?>

                            <p><strong>Contact Person:</strong> <?= $customer['contactPerson'] ?></p>

                        <?php endif; ?>

                        <p><strong>Email:</strong> <?= $customer['email'] ?></p>
                        <p><strong>Contact Number:</strong> <?= $customer['contactNumber'] ?></p>
                        <p><strong>Address:</strong> <?= $customer['address'] ?></p>

                    </div>

                    <br>

                    <form method="post">

                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="userId" value="<?= $customer['userId'] ?>">

                        <div style="display:flex; gap:10px;">

                            <button class="btn btn-primary login-submit" type="submit">Deactivate Customer</button>
                            <button style="background: #0b1f3a; color:white;" class="btn btn-primary" type="button" onclick="window.location.href='deactivate_customer.php'">Clear</button>

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