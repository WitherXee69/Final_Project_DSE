<?php
// HELMS Warehouse module - shared layout.

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function wh_h($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function wh_status_options()
{
    return [
        'PENDING_PICKUP' => 'Pending Pickup',
        'RECEIVED_AT_WAREHOUSE' => 'Received at Warehouse',
        'DETAILS_CONFIRMED' => 'Details Confirmed',
        'PACKED' => 'Packed',
        'AWAITING_PAYMENT' => 'Awaiting Payment',
        'READY_FOR_DISPATCH' => 'Ready for Dispatch',
        'OUT_FOR_DELIVERY' => 'Out for Delivery',
        'DELIVERED' => 'Delivered',
        'RETURNED_TO_WAREHOUSE' => 'Returned to Warehouse',
        'RETURN_TO_SENDER' => 'Return to Sender',
        'UNCLAIMED' => 'Unclaimed',
        'RELEASED_TO_SENDER' => 'Released to Sender',
    ];
}

function wh_normalize_status($status)
{
    $legacy = [
        'Pending Confirmation' => 'RECEIVED_AT_WAREHOUSE',
        'Received at Warehouse' => 'RECEIVED_AT_WAREHOUSE',
        'Stored' => 'DETAILS_CONFIRMED',
        'Packed' => 'PACKED',
        'Released to Sender' => 'RELEASED_TO_SENDER',
    ];

    if (isset($legacy[$status])) {
        return $legacy[$status];
    }

    $status = strtoupper(trim((string) $status));

    return $status;
}

function wh_status_label($status)
{
    $normalized = wh_normalize_status($status);
    $options = wh_status_options();

    if (isset($options[$normalized])) {
        return $options[$normalized];
    }

    return ucwords(
        str_replace('_', ' ', strtolower((string) $status))
    );
}

function wh_status_class($status)
{
    $status = wh_normalize_status($status);

    if (in_array($status, [
        'PACKED',
        'READY_FOR_DISPATCH',
        'DELIVERED',
        'RELEASED_TO_SENDER'
    ], true)) {
        return 'good';
    }

    if (in_array($status, [
        'PENDING_PICKUP',
        'RECEIVED_AT_WAREHOUSE',
        'AWAITING_PAYMENT'
    ], true)) {
        return 'warning';
    }

    if (in_array($status, [
        'UNCLAIMED',
        'RETURN_TO_SENDER'
    ], true)) {
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

        <link
            href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
            rel="stylesheet"
        >

        <link rel="stylesheet" href="../assets/css/style.css">
        <link rel="stylesheet" href="../assets/css/warehouse.css">
    </head>

    <body class="wh-body">

        <header>
            <div class="container nav">

                <a href="../index.php" class="brand">
                    <div class="logo">H</div>

                    <div>
                        <b>HELMS</b>
                        <small>Henowic Inventors (Pvt) Ltd.</small>
                    </div>
                </a>

                <div class="wh-user">
                    <span><?= wh_h($name) ?></span>
                    <small><?= wh_h($role) ?></small>
                </div>

            </div>
        </header>

        <div class="wh-shell">

            <aside class="wh-sidebar">

                <div class="wh-side-label">
                    WAREHOUSE WORKSPACE
                </div>

                <a
                    class="<?= $active === 'dashboard' ? 'active' : '' ?>"
                    href="index.php"
                >
                    Overview
                </a>

                <a
                    class="<?= $active === 'parcels' ? 'active' : '' ?>"
                    href="parcels.php"
                >
                    Parcel register
                </a>

                <a
                    class="<?= $active === 'receive' ? 'active' : '' ?>"
                    href="receive.php"
                >
                    Receive parcel
                </a>

                <?php if ($isManager): ?>
                    <a
                        class="<?= $active === 'report' ? 'active' : '' ?>"
                        href="report.php"
                    >
                        Storage report
                    </a>
                <?php endif; ?>

                <div class="wh-side-bottom">
                    <a href="../index.php">
                        ← Back to HELMS home
                    </a>
                </div>

            </aside>

            <main class="wh-main">

                <div class="wh-page-head">

                    <div>
                        <span class="wh-eyebrow">
                            HELMS / WAREHOUSE
                        </span>

                        <h1><?= wh_h($title) ?></h1>
                    </div>

                    <span class="wh-role-pill">
                        <?= wh_h($role) ?>
                    </span>

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
?>