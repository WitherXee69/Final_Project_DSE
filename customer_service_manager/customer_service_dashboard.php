<?php

require_once '../config/database.php';

$search = $_GET['search'] ?? '';

$searchTerm = "%" . $search . "%";

$stmt = $pdo->prepare("SELECT c.userId, c.email, c.contactNumber, ic.name AS individualName, cc.companyName FROM Customer c LEFT JOIN IndividualCustomer ic ON c.userId = ic.userId LEFT JOIN CorporateCustomer cc ON c.userId = cc.userId WHERE c.userId LIKE ? OR c.email LIKE ? OR ic.name LIKE ? OR cc.companyName LIKE ? ORDER BY c.userId");

$stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm]);

$customers = $stmt->fetchAll();

$totalCustomers = $pdo->query("SELECT COUNT(*) FROM Customer")->fetchColumn();

$corporateCustomers = $pdo->query("SELECT COUNT(*) FROM CorporateCustomer")->fetchColumn();

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

    <header>

        <div class="container nav">

            <a href="index.php" class="brand">

                <div class="logo">H</div>

                <div>

                    <b>HELMS</b>
                    <small>Customer Service Manager Portal</small>

                </div>

            </a>

            <nav class="menu">

                <a class="link" href="#">Dashboard</a>

                <a class="link" href="#customers">Customers</a>

                <a class="link" href="#">Corporate</a>

                <a class="btn btn-ghost" href="index.php">Home</a>

            </nav>

        </div>

    </header>

    <section class="block" style="padding-top:0;">

        <div class="csm-hero">

            <div class="container">

                <div class="csm-tag">CUSTOMER SERVICE MANAGER PORTAL</div>

                <h1>
                    Welcome back,<br>
                    <span>Customer Service Manager</span>
                </h1>

                <p>Manage individual customers, corporate customers and customer records from one central HELMS workspace.</p>

                <div class="csm-buttons">

                    <a href="register_corporate_customer.php" class="btn btn-primary">Register Corporate Customer</a>                   

                </div>

            </div>

        </div>

        <div class="container">

            <div class="stat-grid">

                <div class="stat-card">

                    <div class="icon">👥</div>

                    <div class="number"><?= $totalCustomers ?></div>

                    <div class="label">Total Customers</div>

                </div>

                <div class="stat-card">

                    <div class="icon">🏢</div>
                    <div class="number"><?= $corporateCustomers ?></div>
                    <div class="label">Corporate Customers</div>

                </div>

                <div class="stat-card">

                    <div class="icon">👤</div>
                    <div class="number"><?= $individualCustomers ?></div>
                    <div class="label">Individual Customers</div>

                </div>

            </div>

            <div class="directory-card" id="customers">

                <small>CUSTOMER DIRECTORY</small>
                <h3>Manage Customers</h3>

                <p style="margin-bottom:20px; color:#6b7280;">Search and manage individual and corporate customer records.</p>

                <form method="get" style="margin-bottom:25px;">

                    <div class="field">

                        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by customer ID, customer name, company name or email...">
                        <button type="submit" class="btn btn-primary">Search</button>
                        <a href="customer_service_dashboard.php" class="btn btn-outline">Clear</a>

                    </div>

                </form>

                <table class="customer-table">

                    <thead>

                        <tr>
                            <th>Customer ID</th>
                            <th>Type</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                        </tr>

                    </thead>

                    <tbody>

                        <?php foreach ($customers as $customer): ?>

                        <tr>
                            <td><?= $customer['userId'] ?></td>
                            <td><?= !empty($customer['companyName']) ? 'Corporate' : 'Individual' ?></td>
                            <td><?= $customer['individualName'] ?? $customer['companyName'] ?></td>
                            <td><?= $customer['email'] ?></td>
                            <td><?= $customer['contactNumber'] ?></td>
                        </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

            <div class="head">

                <small>MANAGEMENT ACTIONS</small>
                <h2>Quick Actions</h2>

            </div>

            <div class="quick-actions">

                <div class="action-card">

                    <h4>Register Corporate Customer</h4>
                    <p>Create and manage corporate customer accounts.</p>

                    <br>

                    <a href="register_corporate_customer.php" class="btn btn-primary">Register Corporate Customer</a>

                </div>

                <div class="action-card">

                    <h4>Update Customer</h4>
                    <p>Update customer information and details.</p>

                    <br>

                    <a href="update_customer.php" class="btn btn-primary">Update Customer</a>

                </div>

                <div class="action-card">

                    <h4>Deactivate Customer</h4>
                    <p>Deactivate customer accounts when required.</p>

                    <br>

                    <a href="deactivate_customer.php" class="btn btn-primary">Deactivate Customer</a>

                </div>

            </div>

        </div>

    </section>

</body>

</html>