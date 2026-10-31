<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/_layout.php';

// Get total parcel count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM Parcel");
$stmt->execute();
$totalParcels = $stmt->fetchColumn();

// Get total warehouse count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM Warehouse");
$stmt->execute();
$totalWarehouses = $stmt->fetchColumn();

// Get parcels received at warehouses
$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM Parcel
    WHERE status = 'RECEIVED_AT_WAREHOUSE'
");
$stmt->execute();
$totalStoredParcels = $stmt->fetchColumn();

// Get packed parcel count
$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM Parcel
    WHERE status = 'PACKED'
");
$stmt->execute();
$totalPackedParcels = $stmt->fetchColumn();

// Get parcels awaiting confirmation
$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM Parcel
    WHERE status = 'RECEIVED_AT_WAREHOUSE'
    AND detailsConfirmed = FALSE
");
$stmt->execute();
$totalPendingParcels = $stmt->fetchColumn();

// Get recent parcels
$stmt = $pdo->prepare("
    SELECT parcelId, description, parcelReceivedDate,
           storageLocationCode, status
    FROM Parcel
    ORDER BY parcelReceivedDate DESC, parcelId DESC
    LIMIT 5
");
$stmt->execute();
$parcels = $stmt->fetchAll();

// Get combined warehouse capacity
$stmt = $pdo->prepare("
    SELECT
        COALESCE(SUM(capacity), 0) AS totalCapacity,
        COALESCE(SUM(currentLoad), 0) AS currentLoad
    FROM Warehouse
");
$stmt->execute();
$warehouseCapacity = $stmt->fetch();

$totalCapacity = (int)$warehouseCapacity['totalCapacity'];
$currentLoad = (int)$warehouseCapacity['currentLoad'];

$percent = $totalCapacity > 0
    ? min(100, round(($currentLoad / $totalCapacity) * 100))
    : 0;

wh_header('Warehouse overview', 'dashboard');
?>

<div class="wh-grid">

    <div class="wh-card">
        <div class="wh-stat-label">Parcels in register</div>
        <div class="wh-stat-value"><?= $totalParcels ?></div>
        <div class="wh-stat-foot">All registered parcels</div>
    </div>

    <div class="wh-card">
        <div class="wh-stat-label">Awaiting confirmation</div>
        <div class="wh-stat-value"><?= $totalPendingParcels ?></div>
        <div class="wh-stat-foot">Details need checking</div>
    </div>

    <div class="wh-card">
        <div class="wh-stat-label">Packed parcels</div>
        <div class="wh-stat-value"><?= $totalPackedParcels ?></div>
        <div class="wh-stat-foot">Ready for next handling step</div>
    </div>

    <div class="wh-card">
        <div class="wh-stat-label">Assigned storage</div>
        <div class="wh-stat-value"><?= $totalStoredParcels ?></div>
        <div class="wh-stat-foot">Parcels received at warehouses</div>
    </div>

    <div class="wh-card">
        <div class="wh-stat-label">Total warehouses</div>
        <div class="wh-stat-value"><?= $totalWarehouses ?></div>
        <div class="wh-stat-foot">Registered warehouses</div>
    </div>

</div>

<section class="wh-panel">
    <div class="wh-panel-head">
        <div>
            <h2>Warehouse capacity</h2>
            <p class="wh-muted">Combined capacity across all warehouses</p>
        </div>

        <strong><?= $percent ?>%</strong>
    </div>

    <div class="wh-progress">
        <span style="width:<?= $percent ?>%"></span>
    </div>

    <p class="wh-muted">
        <?= $currentLoad ?> of <?= $totalCapacity ?>
        capacity units currently in use.
    </p>
</section>

<section class="wh-panel">

    <div class="wh-panel-head">
        <div>
            <h2>Recent parcel activity</h2>
            <p class="wh-muted">
                Review and process recently received parcels.
            </p>
        </div>

        <a class="wh-btn secondary" href="parcels.php">
            View all parcels
        </a>
    </div>

    <div class="wh-table-wrap">
        <table class="wh-table">

            <thead>
                <tr>
                    <th>Parcel ID</th>
                    <th>Description</th>
                    <th>Received</th>
                    <th>Location</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>

            <tbody>

                <?php if (empty($parcels)): ?>

                    <tr>
                        <td colspan="6">No parcels found.</td>
                    </tr>

                <?php else: ?>

                    <?php foreach ($parcels as $p): ?>

                        <tr>
                            <td>
                                <strong><?= wh_h($p['parcelId']) ?></strong>
                            </td>

                            <td><?= wh_h($p['description']) ?></td>

                            <td><?= wh_h($p['parcelReceivedDate']) ?></td>

                            <td>
                                <?= wh_h(
                                    $p['storageLocationCode'] ?: 'Not assigned'
                                ) ?>
                            </td>

                            <td>
                                <span class="wh-badge <?= wh_status_class($p['status']) ?>">
                                    <?= wh_h($p['status']) ?>
                                </span>
                            </td>

                            <td>
                                <a class="text-link"
                                   href="view.php?id=<?= urlencode($p['parcelId']) ?>">
                                    Open
                                </a>
                            </td>
                        </tr>

                    <?php endforeach; ?>

                <?php endif; ?>

            </tbody>
        </table>
    </div>

</section>

<div class="wh-actions">
    <a class="wh-btn" href="receive.php">＋ Receive a parcel</a>
    <a class="wh-btn secondary" href="parcels.php">
        Search parcel register
    </a>
</div>

<?php wh_footer(); ?>