<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/warehouse_methods.php';
require_once __DIR__ . '/storage_report_methods.php';

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

$managerId = (string)($_SESSION['user_id'] ?? '');
$report = null;
$error = '';
$saveError = '';

// "Generate new report" button: save a snapshot, then reopen this page showing its PDF.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_report'])) {
    if (!verify_csrf()) {
        $saveError = 'Your session has expired. Please refresh the page and try again.';
    } else {
        try {
            $newId = saveStorageReport($pdo, $managerId);
            header('Location: report.php?open=' . rawurlencode($newId));
            exit;
        } catch (RuntimeException $e) {
            $saveError = $e->getMessage();
        } catch (PDOException $e) {
            error_log('Save storage report error: ' . $e->getMessage());
            $saveError = 'Unable to save the storage report.';
        }
    }
}

try {
    $report = generateStorageReport($pdo, $managerId);
} catch (RuntimeException $e) {
    $error = $e->getMessage();
} catch (PDOException $e) {
    error_log('Storage report error: ' . $e->getMessage());
    $error = 'Unable to generate the storage report.';
}

$saved = [];
try {
    $saved = listStorageReports($pdo, $managerId);
} catch (PDOException $e) {
    error_log('List storage reports error: ' . $e->getMessage());
}

$openId = (string)($_GET['open'] ?? '');
if (!preg_match('/^RPT-STO-[A-Za-z0-9-]{1,12}$/', $openId)) {
    $openId = '';
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

<style>
    .wh-viewer{display:block;width:100%;height:760px;border:1px solid #e3e8ef;border-radius:12px;background:#fff;margin:16px 0}
    .wh-viewer[hidden]{display:none}
    .wh-inline-form{display:inline}
</style>

<section class="wh-panel" id="official">
    <div class="wh-panel-head">
        <div>
            <h2>Official storage reports (PDF)</h2>
            <p class="wh-muted">
                Saving a report records today's figures. It is then available as a PDF
                and is visible to the Managing Director.
            </p>
        </div>

        <form method="post" action="report.php#official" class="wh-inline-form">
            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
            <button class="wh-btn" type="submit" name="generate_report" value="1">
                Generate new report
            </button>
        </form>
    </div>

    <?php if ($saveError !== ''): ?>
        <p class="wh-error"><?= wh_h($saveError) ?></p>
    <?php endif; ?>

    <iframe id="reportFrame" name="reportFrame" class="wh-viewer" title="Storage report PDF"
            <?= $openId === '' ? 'hidden' : '' ?>
            <?= $openId !== '' ? 'src="report_view.php?id=' . wh_h(rawurlencode($openId)) . '"' : '' ?>></iframe>

    <div class="wh-table-wrap">
        <table class="wh-table">
            <thead>
                <tr>
                    <th>Report ID</th>
                    <th>Period</th>
                    <th>Generated</th>
                    <th>Stored</th>
                    <th>Unclaimed</th>
                    <th>Capacity</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($saved)): ?>
                    <tr><td colspan="7">No saved reports yet. Click "Generate new report".</td></tr>
                <?php endif; ?>

                <?php foreach ($saved as $r): ?>
                    <?php $q = rawurlencode($r['reportId']); ?>
                    <tr>
                        <td><strong><?= wh_h($r['reportId']) ?></strong></td>
                        <td>
                            <?= wh_h(date('d M', strtotime($r['periodStart']))) ?> -
                            <?= wh_h(date('d M Y', strtotime($r['periodEnd']))) ?>
                        </td>
                        <td><?= wh_h(date('d M Y, H:i', strtotime($r['generatedAt']))) ?></td>
                        <td><?= (int)$r['parcelsStored'] ?></td>
                        <td><?= (int)$r['unclaimedParcels'] ?></td>
                        <td><?= wh_h(number_format((float)$r['capacityUtilisation'], 1)) ?>%</td>
                        <td>
                            <div class="wh-actions">
                                <a class="wh-btn secondary view-link" target="reportFrame"
                                   href="report_view.php?id=<?= $q ?>">View</a>
                                <a class="wh-btn secondary"
                                   href="report_view.php?id=<?= $q ?>&amp;download=1">PDF</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<script>
(function () {
    var frame = document.getElementById('reportFrame');
    document.querySelectorAll('.view-link').forEach(function (a) {
        a.addEventListener('click', function () {
            frame.hidden = false;
            frame.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });
    if (!frame.hidden) { frame.scrollIntoView({ block: 'start' }); }
})();
</script>
<?php endif; ?>
<?php wh_footer(); ?>
