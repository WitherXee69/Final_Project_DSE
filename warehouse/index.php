<?php
require_once __DIR__ . '/_layout.php';

$parcels = wh_parcels();
$total = count($parcels);
$pending = count(array_filter($parcels, fn($p) => !$p['detailsConfirmed']));
$packed = count(array_filter($parcels, fn($p) => $p['status'] === 'Packed'));
$stored = count(array_filter($parcels, fn($p) => !empty($p['storageLocationCode'])));
$w = $_SESSION['wh_warehouse'];
$percent = $w['capacity'] ? min(100, round(($w['currentLoad'] / $w['capacity']) * 100)) : 0;
wh_header('Warehouse overview', 'dashboard');
?>

<div class="wh-grid">
    <div class="wh-card">
        <div class="wh-stat-label">Parcels in register</div>
        <div class="wh-stat-value"><?= $total ?></div>
        <div class="wh-stat-foot">Current demo records</div>
    </div>
    <div class="wh-card">
        <div class="wh-stat-label">Awaiting confirmation</div>
        <div class="wh-stat-value"><?= $pending ?></div>
        <div class="wh-stat-foot">Details need checking</div>
    </div>
    <div class="wh-card">
        <div class="wh-stat-label">Packed parcels</div>
        <div class="wh-stat-value"><?= $packed ?></div>
        <div class="wh-stat-foot">Ready for next handling step</div>
    </div>
    <div class="wh-card">
        <div class="wh-stat-label">Assigned storage</div>
        <div class="wh-stat-value"><?= $stored ?></div>
        <div class="wh-stat-foot">Parcels with a location code</div>
    </div>
</div>
<section class="wh-panel">
    <div class="wh-panel-head">
        <div>
            <h2>Warehouse capacity</h2>
            <p class="wh-muted"><?= wh_h($w['warehouseName']) ?> · <?= wh_h($w['zoneCode']) ?></p>
        </div>
        <strong><?= $percent ?>%</strong>
    </div>
    <div class="wh-progress"><span style="width:<?= $percent ?>%"></span></div>
    <p class="wh-muted"><?= (int)$w['currentLoad'] ?> of <?= (int)$w['capacity'] ?> capacity units currently in use.</p>
</section>
<section class="wh-panel">
    <div class="wh-panel-head">
        <div>
            <h2>Recent parcel activity</h2>
            <p class="wh-muted">Review and process parcels received by this warehouse.</p>
        </div>
        <a class="wh-btn secondary" href="parcels.php">View all parcels</a>
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
                <?php foreach (array_slice(array_reverse($parcels), 0, 5) as $p): ?>
                    <tr>
                        <td><strong><?= wh_h($p['parcelId']) ?></strong></td>
                        <td><?= wh_h($p['description']) ?></td>
                        <td><?= wh_h($p['parcelReceivedDate']) ?></td>
                        <td><?= wh_h($p['storageLocationCode'] ?: 'Not assigned') ?></td>
                        <td>
                            <span class="wh-badge <?= wh_status_class($p['status']) ?>">
                                <?= wh_h($p['status']) ?>
                            </span>
                        </td>
                        <td><a class="text-link" href="view.php?id=<?= urlencode($p['parcelId']) ?>">Open</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<div class="wh-actions">
    <a class="wh-btn" href="receive.php">＋ Receive a parcel</a>
    <a class="wh-btn secondary" href="parcels.php">Search parcel register</a>
</div>
<?php wh_footer(); ?>
