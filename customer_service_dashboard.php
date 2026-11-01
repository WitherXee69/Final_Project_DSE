<?php
require_once 'config/database.php';

$totalCustomers = $pdo->query(
    "SELECT COUNT(*) FROM customer"
)->fetchColumn();

$corporateCustomers = $pdo->query(
    "SELECT COUNT(*) FROM corporatecustomer"
)->fetchColumn();

$activeAgreements = $pdo->query(
    "SELECT COUNT(*) FROM corporateagreement"
)->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Service Manager Dashboard | HELMS</title>

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

            </nav>

        </div>

    </header>

    <section class="block">

        <div class="container">

            <div class="head">

                <small>CUSTOMER SERVICE MANAGER</small>

                <h2>Customer Management Dashboard</h2>

                <p>Manage corporate customers, customer profiles and agreements.</p>

            </div>

            <div class="svc-grid">

                <div class="svc">

                    <h4>Total Customers</h4>
                    <h2><?= $totalCustomers ?></h2>

                </div>

                <div class="svc">

                    <h4>Corporate Customers</h4>
                    <h2><?= $corporateCustomers ?></h2>

                </div>

                <div class="svc">

                    <h4>Active Agreements</h4>
                    <h2><?= $activeAgreements ?></h2>

                </div>

            </div>

            <br><br><br>

            <div class="head">

                <small>CUSTOMER DIRECTORY</small>
                <h2>Customers</h2>

            </div>

            <form style="margin-bottom:30px;">

                <div class="field">

                    <input type="text" name="search" placeholder="Search by customer ID, customer name, company name or email...">

                    <button type="submit" class="btn btn-primary">Search</button>

                </div>

            </form>

            <div class="svc">

                <h4>Customer Records</h4>

                <table style="width:100%; border-collapse:collapse; margin-top:15px; text-align:center;">

                    <tr>
                        <th style="padding:10px; border-bottom:2px solid #e3e8ef;">Customer ID</th>
                        <th style="padding:10px; border-bottom:2px solid #e3e8ef;">Customer Type</th>
                        <th style="padding:10px; border-bottom:2px solid #e3e8ef;">Customer Name</th>
                        <th style="padding:10px; border-bottom:2px solid #e3e8ef;">Email</th>
                        <th style="padding:10px; border-bottom:2px solid #e3e8ef;">Phone</th>
                    </tr>

                    <tr>
                        <td style="padding:8px;">USR-000013</td>
                        <td style="padding:8px;">Individual</td>
                        <td style="padding:8px;">Amal Rathnayake</td>
                        <td style="padding:8px;">amal.r@example.com</td>
                        <td style="padding:8px;">+94771234013</td>
                    </tr>

                    <tr>
                        <td style="padding:8px;">USR-000014</td>
                        <td style="padding:8px;">Individual</td>
                        <td style="padding:8px;">Sachini Wijesinghe</td>
                        <td style="padding:8px;">sachini.w@example.com</td>
                        <td style="padding:8px;">+94771234014</td>
                    </tr>

                    <tr>
                        <td style="padding:8px;">USR-000015</td>
                        <td style="padding:8px;">Corporate</td>
                        <td style="padding:8px;">Lanka Tea Exporters (Pvt) Ltd</td>
                        <td style="padding:8px;">orders@lankatea.example.com</td>
                        <td style="padding:8px;">+94112223015</td>
                    </tr>

                </table>

            </div>

            <br><br><br>

            <div class="head">

                <small>FUNCTIONS</small>
                <h2>Customer Management</h2>

            </div>

            <div class="svc-grid">

                <div class="svc">

                    <h4>Register Corporate Customer</h4>
                    <p>Create a new corporate customer account.</p>

                    <br>

                    <a href="register_corporate_customer.php" class="btn btn-primary">Open</a>

                </div>

                <div class="svc">

                    <h4>Update Customer</h4>
                    <p>Update customer details and records.</p>

                    <br>

                    <a href="update_customer.php" class="btn btn-primary">Open</a>

                </div>

                <div class="svc">

                    <h4>Deactivate Customer</h4>
                    <p>Deactivate customer accounts.</p>

                    <br>

                    <a href="deactivate_customer.php" class="btn btn-primary">Open</a>

                </div>

                <div class="svc">

                    <h4>View Agreements</h4>
                    <p>View corporate customer agreements.</p>

                    <br>

                    <a href="view_agreements.php" class="btn btn-primary">Open</a>

                </div>

            </div>

        </div>

    </section>

</body>

</html>