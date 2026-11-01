<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/warehouse_methods.php';

$role = $_SESSION['role'] ?? 'Warehouse Officer';
if (stripos($role, 'manager') === false) {
    http_response_code(403);
    wh_header('Access restricted');
    ?>
    <section class="wh-panel">
        <p class="wh-empty">This report is available to Warehouse Managers only.</p>
    </section>
    <?php
    wh_footer();
    exit;
}

$report = null;
$error = '';

try {
    $report = generateStorageReport($pdo, (string)($_SESSION['user_id'] ?? ''));
} catch (RuntimeException $e) {
    $error = $e->getMessage();
} catch (PDOException $e) {
    error_log('Storage report error: ' . $e->getMessage());
    $error = 'Unable to generate the storage report.';
}

wh_header('Storage report', 'report');

if ($report === null):
?>
<section class="wh-panel">
    <p class="wh-error"><?= wh_h($error) ?></p>
</section>
<?php else:
    $w = $report['warehouse'];
?>
<section class="wh-panel">
    <div class="wh-panel-head">
        <div>
            <h2><?= wh_h($w['warehouseName']) ?></h2>
            <p class="wh-muted">
                Warehouse ID: <?= wh_h($w['warehouseId']) ?> · Zone: <?= wh_h($w['zoneCode']) ?>
                · Generated: <?= wh_h($report['generatedAt']) ?>
            </p>
        </div>
        <span class="wh-role-pill">Manager report</span>
    </div>

    <div class="wh-grid wh-report-grid">
        <div class="wh-card">
            <div class="wh-stat-label">Parcels stored</div>
            <div class="wh-stat-value"><?= (int)$report['parcelsStored'] ?></div>
        </div>
        <div class="wh-card">
            <div class="wh-stat-label">Unclaimed parcels</div>
            <div class="wh-stat-value"><?= (int)$report['unclaimedParcels'] ?></div>
        </div>
        <div class="wh-card">
            <div class="wh-stat-label">Capacity utilisation</div>
            <div class="wh-stat-value"><?= wh_h($report['capacityUtilisation']) ?>%</div>
        </div>
    </div>

    <h2 class="wh-report-heading">Parcel status summary</h2>
    <div class="wh-table-wrap">
        <table class="wh-table">
            <thead>
                <tr><th>Status</th><th>Parcel count</th></tr>
            </thead>
            <tbody>
                <?php if (empty($report['byStatus'])): ?>
                    <tr><td colspan="2">No parcels recorded for this warehouse.</td></tr>
                <?php else: ?>
                    <?php foreach ($report['byStatus'] as $s => $n): ?>
                        <tr>
                            <td><?= wh_h(wh_status_label($s)) ?></td>
                            <td><strong><?= (int)$n ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
<?php endif; ?>
<?php wh_footer(); ?>
