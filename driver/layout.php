
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* =========================================
   DUMMY DRIVER DATA
========================================= */

function dr_seed()
{
    if (!isset($_SESSION['driver_dummy_data'])) {

        $_SESSION['driver_dummy_data'] = [

            'driver' => [
                'driverId' => 'DRV-001',
                'fullName' => 'Kasun Perera',
                'username' => 'kasun.driver',
                'licenseNumber' => 'B1234567',
                'contactNumber' => '0771234567',
                'status' => 'Available',
                'vehicle' => 'CAB-1234'
            ],

            'manifests' => [
                [
                    'id' => 'DM-001',
                    'date' => date('d M Y'),
                    'schedule' => '08:00 AM - 12:00 PM',
                    'route' => 'Colombo - Nugegoda - Maharagama',
                    'vehicle' => 'CAB-1234',
                    'status' => 'Assigned'
                ],
                [
                    'id' => 'DM-002',
                    'date' => date('d M Y', strtotime('-1 day')),
                    'schedule' => '01:00 PM - 05:00 PM',
                    'route' => 'Colombo - Dehiwala - Mount Lavinia',
                    'vehicle' => 'CAB-1234',
                    'status' => 'Completed'
                ],
                [
                    'id' => 'DM-003',
                    'date' => date('d M Y', strtotime('-2 days')),
                    'schedule' => '08:00 AM - 12:00 PM',
                    'route' => 'Colombo - Rajagiriya - Battaramulla',
                    'vehicle' => 'CAB-1234',
                    'status' => 'Completed'
                ]
            ],

            'pickups' => [
                [
                    'id' => 'PU-001',
                    'date' => date('d M Y'),
                    'time' => '09:00 AM - 11:00 AM',
                    'address' => 'No. 25, High Level Road, Nugegoda',
                    'status' => 'Assigned',
                    'failureReason' => ''
                ],
                [
                    'id' => 'PU-002',
                    'date' => date('d M Y'),
                    'time' => '11:00 AM - 01:00 PM',
                    'address' => 'No. 18, Galle Road, Dehiwala',
                    'status' => 'Pending',
                    'failureReason' => ''
                ],
                [
                    'id' => 'PU-003',
                    'date' => date('d M Y', strtotime('-1 day')),
                    'time' => '02:00 PM - 04:00 PM',
                    'address' => 'No. 42, Kotte Road, Rajagiriya',
                    'status' => 'Completed',
                    'failureReason' => ''
                ]
            ],

            'deliveries' => [
                [
                    'id' => 'DEL-001',
                    'parcelId' => 'PAR-001',
                    'date' => date('d M Y'),
                    'address' => 'No. 12, Park Road, Colombo 05',
                    'receiver' => 'Nimal Fernando',
                    'phone' => '0712345678',
                    'attempts' => 0,
                    'status' => 'Pending',
                    'arrivedAt' => null
                ],
                [
                    'id' => 'DEL-002',
                    'parcelId' => 'PAR-002',
                    'date' => date('d M Y'),
                    'address' => 'No. 45, Station Road, Nugegoda',
                    'receiver' => 'Amali Perera',
                    'phone' => '0774567890',
                    'attempts' => 1,
                    'status' => 'In Progress',
                    'arrivedAt' => null
                ],
                [
                    'id' => 'DEL-003',
                    'parcelId' => 'PAR-003',
                    'date' => date('d M Y', strtotime('-1 day')),
                    'address' => 'No. 08, Temple Road, Maharagama',
                    'receiver' => 'Sahan Silva',
                    'phone' => '0761234567',
                    'attempts' => 1,
                    'status' => 'Delivered',
                    'arrivedAt' => null
                ],
                [
                    'id' => 'DEL-004',
                    'parcelId' => 'PAR-004',
                    'date' => date('d M Y', strtotime('-1 day')),
                    'address' => 'No. 67, Main Street, Dehiwala',
                    'receiver' => 'Tharushi Jayawardena',
                    'phone' => '0759876543',
                    'attempts' => 2,
                    'status' => 'Failed',
                    'arrivedAt' => null
                ]
            ]
        ];
    }

    // Add new fields when an older session already exists.
    foreach ($_SESSION['driver_dummy_data']['pickups'] as &$pickup) {
        if (!isset($pickup['failureReason'])) {
            $pickup['failureReason'] = '';
        }
    }
    unset($pickup);

    foreach ($_SESSION['driver_dummy_data']['deliveries'] as &$delivery) {
        if (!isset($delivery['arrivedAt'])) {
            $delivery['arrivedAt'] = null;
        }
    }
    unset($delivery);
}


/* =========================================
   DATA ACCESS FUNCTIONS
========================================= */

function dr_driver()
{
    return $_SESSION['driver_dummy_data']['driver'];
}

function dr_manifests()
{
    return $_SESSION['driver_dummy_data']['manifests'];
}

function dr_pickups()
{
    return $_SESSION['driver_dummy_data']['pickups'];
}

function dr_deliveries()
{
    return $_SESSION['driver_dummy_data']['deliveries'];
}


/* =========================================
   FLASH MESSAGES
========================================= */

function dr_set_flash($type, $message)
{
    $_SESSION['driver_dummy_flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

function dr_get_flash()
{
    if (!isset($_SESSION['driver_dummy_flash'])) {
        return null;
    }

    $flash = $_SESSION['driver_dummy_flash'];
    unset($_SESSION['driver_dummy_flash']);

    return $flash;
}


/* =========================================
   STATUS CLASS
========================================= */

function dr_status_class($status)
{
    $status = strtolower(trim((string) $status));
    $status = preg_replace('/[^a-z0-9]+/', '-', $status);

    return 'status-' . trim($status, '-');
}


/* =========================================
   SHARED HEADER
========================================= */

function dr_header($pageTitle = 'Driver Dashboard', $activePage = 'dashboard')
{
    ?>

    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?> | HELMS</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

        <link rel="stylesheet" href="../assets/css/style.css">
        <link rel="stylesheet" href="../assets/css/driver-dashboard.css">
    </head>

    <body class="driver-dashboard-page">

        <header class="driver-header">

            <div class="container driver-nav">

                <a href="../index.php" class="brand">
                    <div class="logo">H</div>

                    <div>
                        <b>HELMS</b>
                        <small>Henowic Inventors (Pvt) Ltd.</small>
                    </div>
                </a>

                <nav class="driver-menu">

                    <a class="<?= $activePage === 'dashboard' ? 'active' : ''; ?>"
                       href="driver_dashboard_dummy.php">
                        Dashboard
                    </a>

                    <a href="driver_dashboard_dummy.php#manifests">
                        My manifests
                    </a>

                    <a href="driver_dashboard_dummy.php#pickups">
                        Pickups
                    </a>

                    <a href="driver_dashboard_dummy.php#deliveries">
                        Deliveries
                    </a>

                </nav>

                <div class="driver-account">

                    <span class="driver-account-name">
                        <?= htmlspecialchars(dr_driver()['fullName'], ENT_QUOTES, 'UTF-8'); ?>
                    </span>

                    <a class="driver-logout" href="../index.php">
                        Exit Portal
                    </a>

                </div>

            </div>

        </header>

        <main class="container driver-main">

            <div class="driver-demo-notice">
                <strong>Prototype Mode</strong>
                <span>
                    Sample data is being used. Changes are stored in your PHP session.
                </span>
            </div>

    <?php
}


/* =========================================
   SHARED FOOTER
========================================= */

function dr_footer()
{
    ?>

        </main>

        <footer class="driver-footer">

            <div class="container">

                <span>
                    &copy; <?= date('Y'); ?> Henowic Inventors (Pvt) Ltd.
                </span>

                <span>
                    HELMS &middot; Driver Workspace
                </span>

            </div>

        </footer>

        <script src="../assets/js/main.js"></script>

    </body>

    </html>

    <?php
}


/* =========================================
   INITIALISE DUMMY DATA
========================================= */

dr_seed();

?>
