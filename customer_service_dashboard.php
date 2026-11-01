<?php
require_once 'config/database.php';

$totalCustomers = $pdo->query(
    "SELECT COUNT(*) FROM Customer"
)->fetchColumn();

$corporateCustomers = $pdo->query(
    "SELECT COUNT(*) FROM CorporateCustomer"
)->fetchColumn();

$activeAgreements = $pdo->query(
    "SELECT COUNT(*) FROM CorporateAgreement"
)->fetchColumn();

$individualCustomers = $totalCustomers - $corporateCustomers;
?>

<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Customer Service Manager Portal | HELMS</title>

    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/customer_service_dashboard.css">

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

                <a class="link" href="view_agreements.php">Agreements</a>

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

                <p>Manage individual customers, corporate customers, customer agreements and customer records from one central HELMS workspace.</p>

                <div class="csm-buttons">

                    <a href="register_corporate_customer.php" class="btn btn-primary">Register Corporate Customer</a>
                    <a href="view_agreements.php" class="btn btn-primary">View Agreements</a>                    

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

                <div class="stat-card">
                    <div class="icon">📄</div>
                    <div class="number"><?= $activeAgreements ?></div>
                    <div class="label">Active Agreements</div>

                </div>

            </div>

            <div class="directory-card" id="customers">

                <small>CUSTOMER DIRECTORY</small>
                <h3>Manage Customers</h3>

                <p style="margin-bottom:20px; color:#6b7280;">Search and manage individual and corporate customer records.</p>

                <form style="margin-bottom:25px;">

                    <div class="field">

                        <input type="text" name="search" placeholder="Search by customer ID, customer name, company name or email...">

                        <button type="submit" class="btn btn-primary">Search</button>

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
                            <th>Actions</th>
                        </tr>

                    </thead>

                    <tbody>

                        <tr>
                            <td>USR-000013</td>
                            <td>Individual</td>
                            <td>Amal Rathnayake</td>
                            <td>amal.r@example.com</td>
                            <td>+94771234013</td>

                            <td>
                                <a href="update_customer.php" class="btn btn-outline">Edit</a>
                            </td>
                        </tr>

                        <tr>
                            <td>USR-000014</td>
                            <td>Individual</td>
                            <td>Sachini Wijesinghe</td>
                            <td>sachini.w@example.com</td>
                            <td>+94771234014</td>

                            <td>
                                <a href="update_customer.php" class="btn btn-outline">Edit</a>
                            </td>
                        </tr>

                        <tr>
                            <td>USR-000015</td>
                            <td>Corporate</td>
                            <td>Lanka Tea Exporters (Pvt) Ltd</td>
                            <td>orders@lankatea.example.com</td>
                            <td>+94112223015</td>

                            <td>
                                <a href="update_customer.php" class="btn btn-outline">Edit</a>
                            </td>
                        </tr>

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

                <div class="action-card">

                    <h4>View Agreements</h4>
                    <p>View corporate agreements and contract details.</p>

                    <br>

                    <a href="view_agreements.php" class="btn btn-primary">View Agreements</a>

                </div>

            </div>

        </div>

    </section>

</body>

</html>