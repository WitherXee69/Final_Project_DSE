<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/warehouse_methods.php';

$id = trim($_GET['id'] ?? '');
$parcel = null;
$databaseError = false;

if ($id !== '') {
    try {
        $parcel = viewParcelDetails($pdo, $id);
    } catch (PDOException $e) {
        error_log('Warehouse parcel view error: ' . $e->getMessage());
        $databaseError = true;
    }
}

wh_header('Parcel details', 'parcels');

$showValue = static function ($value) {
    if ($value === null || $value === '') {
        return '—';
    }

    return (string) $value;
};

$yesNo = static function ($value) {
    return (bool) $value ? 'Yes' : 'No';
};

?>

<?php if ($databaseError): ?>

    <section class="wh-panel">
        <p class="wh-empty">
            Unable to retrieve parcel information.
            Please try again later.
        </p>
    </section>

<?php elseif (!$parcel): ?>

    <section class="wh-panel">
        <p class="wh-empty">
            Parcel not found.
            <a class="text-link" href="parcels.php">
                Return to parcel register
            </a>.
        </p>
    </section>

<?php else: ?>

    <?php if (isset($_GET['created'])): ?>
        <div class="wh-demo-notice">
            Parcel record successfully saved to the database.
        </div>
    <?php endif; ?>

    <section class="wh-panel">

        <div class="wh-panel-head">
            <div>
                <h2><?= wh_h($parcel['parcelId']) ?></h2>
                <p class="wh-muted">
                    <?= wh_h($showValue($parcel['description'])) ?>
                </p>
            </div>

            <span class="wh-badge <?= wh_status_class($parcel['status']) ?>">
                <?= wh_h(wh_status_label($parcel['status'])) ?>
            </span>
        </div>

        <h3>Parcel information</h3>

        <div class="wh-detail-grid">

            <div class="wh-detail">
                <small>Category</small>
                <strong><?= wh_h($showValue($parcel['parcelCategory'])) ?></strong>
            </div>

            <div class="wh-detail">
                <small>Service type</small>
                <strong><?= wh_h($showValue($parcel['serviceType'])) ?></strong>
            </div>

            <div class="wh-detail">
                <small>Intake method</small>
                <strong><?= wh_h($showValue($parcel['intakeMethod'])) ?></strong>
            </div>

            <div class="wh-detail">
                <small>Fragile</small>
                <strong><?= wh_h($yesNo($parcel['isFragile'])) ?></strong>
            </div>

            <div class="wh-detail">
                <small>Declared weight (kg)</small>
                <strong><?= wh_h($showValue($parcel['declaredWeight'])) ?></strong>
            </div>

            <div class="wh-detail">
                <small>Confirmed weight (kg)</small>
                <strong><?= wh_h($showValue($parcel['confirmedWeight'])) ?></strong>
            </div>

            <div class="wh-detail">
                <small>Dimensions</small>
                <strong><?= wh_h($showValue($parcel['dimensions'])) ?></strong>
            </div>

            <div class="wh-detail">
                <small>Details confirmed</small>
                <strong><?= wh_h($yesNo($parcel['detailsConfirmed'])) ?></strong>
            </div>

            <div class="wh-detail">
                <small>Received date</small>
                <strong><?= wh_h($showValue($parcel['parcelReceivedDate'])) ?></strong>
            </div>

            <div class="wh-detail">
                <small>Storage location</small>
                <strong><?= wh_h($showValue($parcel['storageLocationCode'])) ?></strong>
            </div>

            <div class="wh-detail">
                <small>Hold expires at</small>
                <strong><?= wh_h($showValue($parcel['holdExpiresAt'])) ?></strong>
            </div>

            <div class="wh-detail">
                <small>Failure reason</small>
                <strong><?= wh_h($showValue($parcel['failureReason'])) ?></strong>
            </div>

        </div>

        <h3>Customer information</h3>

        <div class="wh-detail-grid">

            <div class="wh-detail">
                <small>Customer ID</small>
                <strong><?= wh_h($showValue($parcel['customerId'])) ?></strong>
            </div>

            <div class="wh-detail">
                <small>Customer name</small>
                <strong><?= wh_h($showValue($parcel['customerName'])) ?></strong>
            </div>

            <div class="wh-detail">
                <small>Contact number</small>
                <strong><?= wh_h($showValue($parcel['customerPhone'])) ?></strong>
            </div>

            <div class="wh-detail">
                <small>Email</small>
                <strong><?= wh_h($showValue($parcel['customerEmail'])) ?></strong>
            </div>

            <div class="wh-detail">
                <small>Address</small>
                <strong><?= wh_h($showValue($parcel['customerAddress'])) ?></strong>
            </div>

        </div>

        <h3>Receiver information</h3>

        <div class="wh-detail-grid">

            <div class="wh-detail">
                <small>Receiver ID</small>
                <strong><?= wh_h($showValue($parcel['receiverId'])) ?></strong>
            </div>

            <div class="wh-detail">
                <small>Receiver name</small>
                <strong><?= wh_h($showValue($parcel['receiverName'])) ?></strong>
            </div>

            <div class="wh-detail">
                <small>Contact number</small>
                <strong><?= wh_h($showValue($parcel['receiverPhone'])) ?></strong>
            </div>

            <div class="wh-detail">
                <small>Address</small>
                <strong><?= wh_h($showValue($parcel['receiverAddress'])) ?></strong>
            </div>

            <div class="wh-detail">
                <small>Receiver zone</small>
                <strong><?= wh_h($showValue($parcel['receiverZone'])) ?></strong>
            </div>

        </div>

        <h3>Warehouse information</h3>

        <div class="wh-detail-grid">

            <div class="wh-detail">
                <small>Warehouse ID</small>
                <strong><?= wh_h($showValue($parcel['warehouseId'])) ?></strong>
            </div>

            <div class="wh-detail">
                <small>Warehouse name</small>
                <strong><?= wh_h($showValue($parcel['warehouseName'])) ?></strong>
            </div>

            <div class="wh-detail">
                <small>Warehouse zone</small>
                <strong><?= wh_h($showValue($parcel['warehouseZone'])) ?></strong>
            </div>

        </div>

        <div class="wh-form-actions wh-form-actions-spaced">

            <a class="wh-btn"
               href="edit.php?id=<?= urlencode($parcel['parcelId']) ?>">
                Edit parcel details
            </a>

            <a class="wh-btn secondary"
               href="process.php?id=<?= urlencode($parcel['parcelId']) ?>">
                Warehouse processing
            </a>

            <a class="wh-btn secondary" href="parcels.php">
                Back to register
            </a>

        </div>

    </section>

<?php endif; ?>

<?php wh_footer(); ?>
