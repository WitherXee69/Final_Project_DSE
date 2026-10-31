<?php
// HELMS Warehouse module - shared layout and temporary demo data.
// Replace the session store with the project's MySQL/PDO layer when the schema is connected.
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function wh_h($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function wh_seed()
{
    if (!isset($_SESSION['wh_parcels'])) {
        $_SESSION['wh_parcels'] = [
            'HEN-2026-000123' => [
                'parcelId' => 'HEN-2026-000123',
                'description' => 'Laptop accessories',
                'parcelCategory' => 'Electronics',
                'isFragile' => true,
                'serviceType' => 'Express',
                'intakeMethod' => 'Customer Drop-off',
                'declaredWeight' => '1.20',
                'confirmedWeight' => '1.25',
                'dimensions' => '30 x 20 x 10 cm',
                'detailsConfirmed' => true,
                'parcelReceivedDate' => date('Y-m-d'),
                'storageLocationCode' => 'A-01-03',
                'status' => 'Received at Warehouse',
                'holdExpiresAt' => '',
                'failureReason' => '',
            ],
            'HEN-2026-000124' => [
                'parcelId' => 'HEN-2026-000124',
                'description' => 'Clothing package',
                'parcelCategory' => 'General',
                'isFragile' => false,
                'serviceType' => 'Standard',
                'intakeMethod' => 'Driver Pickup',
                'declaredWeight' => '2.00',
                'confirmedWeight' => '2.00',
                'dimensions' => '35 x 25 x 12 cm',
                'detailsConfirmed' => true,
                'parcelReceivedDate' => date('Y-m-d'),
                'storageLocationCode' => 'B-02-01',
                'status' => 'Packed',
                'holdExpiresAt' => '',
                'failureReason' => '',
            ],
            'HEN-2026-000125' => [
                'parcelId' => 'HEN-2026-000125',
                'description' => 'Glassware set',
                'parcelCategory' => 'Fragile',
                'isFragile' => true,
                'serviceType' => 'Express',
                'intakeMethod' => 'Customer Drop-off',
                'declaredWeight' => '3.50',
                'confirmedWeight' => '',
                'dimensions' => '40 x 30 x 25 cm',
                'detailsConfirmed' => false,
                'parcelReceivedDate' => date('Y-m-d'),
                'storageLocationCode' => '',
                'status' => 'Pending Confirmation',
                'holdExpiresAt' => '',
                'failureReason' => '',
            ],
        ];
    }

    if (!isset($_SESSION['wh_warehouse'])) {
        $_SESSION['wh_warehouse'] = [
            'warehouseName' => 'Colombo Central Warehouse',
            'zoneCode' => 'CMB-01',
            'capacity' => 500,
            'currentLoad' => 128,
            'assignedZone' => 'Colombo Central',
            'shift' => 'Day Shift',
            'officeNumber' => 'WH-01',
            'assignedWarehouseId' => 'WH-CMB-01',
        ];
    }
}

wh_seed();

function wh_parcels()
{
    return $_SESSION['wh_parcels'];
}

function wh_find($id)
{
    return $_SESSION['wh_parcels'][$id] ?? null;
}

function wh_save($parcel)
{
    $_SESSION['wh_parcels'][$parcel['parcelId']] = $parcel;
}

function wh_status_class($status)
{
    $s = strtolower($status);
    if (strpos($s, 'packed') !== false || strpos($s, 'released') !== false) {
        return 'good';
    }
    if (strpos($s, 'pending') !== false || strpos($s, 'await') !== false) {
        return 'warning';
    }
    if (strpos($s, 'failed') !== false || strpos($s, 'unclaimed') !== false) {
        return 'danger';
    }

    return 'info';
}

function wh_header($title, $active = 'dashboard')
{
    $name = $_SESSION['full_name'] ?? 'Warehouse Team Member';
    $role = $_SESSION['role'] ?? 'Warehouse Officer';
    $isManager = stripos($role, 'manager') !== false;
    ?>
    <!doctype html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= wh_h($title) ?> | HELMS</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="../assets/css/style.css">
        <link rel="stylesheet" href="warehouse.css">
    </head>
    <body class="wh-body">
        <header>
            <div class="container nav">
                <a href="../index.php" class="brand">
                    <div class="logo">H</div>
                    <div><b>HELMS</b><small>Henowic Inventors (Pvt) Ltd.</small></div>
                </a>
                <div class="wh-user">
                    <span><?= wh_h($name) ?></span>
                    <small><?= wh_h($role) ?></small>
                </div>
            </div>
        </header>
        <div class="wh-shell">
            <aside class="wh-sidebar">
                <div class="wh-side-label">WAREHOUSE WORKSPACE</div>
                <a class="<?= $active === 'dashboard' ? 'active' : '' ?>" href="index.php">Overview</a>
                <a class="<?= $active === 'parcels' ? 'active' : '' ?>" href="parcels.php">Parcel register</a>
                <a class="<?= $active === 'receive' ? 'active' : '' ?>" href="receive.php">Receive parcel</a>
                <?php if ($isManager): ?>
                    <a class="<?= $active === 'report' ? 'active' : '' ?>" href="report.php">Storage report</a>
                <?php endif; ?>
                <div class="wh-side-bottom">
                    <a href="../index.php">← Back to HELMS home</a>
                </div>
            </aside>
            <main class="wh-main">
                <div class="wh-page-head">
                    <div>
                        <span class="wh-eyebrow">HELMS / WAREHOUSE</span>
                        <h1><?= wh_h($title) ?></h1>
                    </div>
                    <span class="wh-role-pill"><?= wh_h($role) ?></span>
                </div>
                <div class="wh-demo-notice">
                    <strong>Prototype mode:</strong> this module currently uses temporary PHP session data for demonstration. It is not connected to MySQL yet; changes reset when the session ends.
                </div>
    <?php
}

function wh_footer()
{
    ?>
            </main>
        </div>
    </body>
    </html>
<?php
}
