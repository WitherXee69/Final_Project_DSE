<?php
require_once __DIR__ . '/_layout.php';
require_once __DIR__ . '/report_methods.php';

md_require_director();

$types     = md_report_types();
$headlines = md_headlines($pdo);
$reports   = md_list_reports($pdo);

// Group the reports by type for the drop-down list.
$grouped = [];
foreach ($reports as $r) {
    $grouped[$r['type']][] = $r;
}

function md_period(array $r): string
{
    return date('d M Y', strtotime($r['periodStart'])) . ' - ' . date('d M Y', strtotime($r['periodEnd']));
}

md_header('Managing Director Dashboard');
?>

<div class="md-head">
    <div>
        <span class="md-eyebrow">HELMS / MANAGING DIRECTOR</span>
        <h1>Management reports</h1>
        <p class="md-sub">Choose a report from the list to open it as a PDF.</p>
    </div>
    <span class="md-pill"><?= count($reports) ?> reports available</span>
</div>

<section class="md-grid">
    <?php foreach ($headlines as $c): ?>
        <div class="md-card">
            <div class="md-card-title"><?= h($c['title']) ?></div>
            <div class="md-card-label"><?= h($c['label']) ?></div>
            <div class="md-card-value"><?= h($c['value']) ?></div>
            <div class="md-card-foot"><?= h($c['period']) ?></div>
        </div>
    <?php endforeach; ?>
</section>

<section class="md-panel">
    <h2>View a report</h2>

    <?php if (empty($reports)): ?>
        <p class="md-empty">No reports have been saved yet.</p>
    <?php else: ?>
        <form id="pickForm" class="md-picker" method="get" action="report_view.php" target="reportFrame">
            <div class="md-field">
                <label for="report">Report</label>
                <select id="report" name="report" required>
                    <option value="">Select a report...</option>
                    <?php foreach ($types as $key => $t): ?>
                        <?php if (empty($grouped[$key])) continue; ?>
                        <optgroup label="<?= h($t['label']) ?>s">
                            <?php foreach ($grouped[$key] as $r): ?>
                                <option value="<?= h($key . ':' . $r['reportId']) ?>">
                                    <?= h($r['reportId']) ?> &nbsp;(<?= h(md_period($r)) ?>)
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endforeach; ?>
                </select>
            </div>

            <button class="md-btn" type="submit">View report</button>
            <button class="md-btn secondary" type="submit" name="download" value="1" formtarget="_self">Download PDF</button>
        </form>

        <div class="md-viewer-wrap">
            <div id="viewerPlaceholder" class="md-placeholder">
                The report will appear here.
            </div>
            <iframe id="reportFrame" name="reportFrame" class="md-viewer" title="Report viewer" hidden></iframe>
        </div>
    <?php endif; ?>
</section>

<section class="md-panel">
    <h2>All saved reports</h2>

    <div class="md-table-wrap">
        <table class="md-table">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Report ID</th>
                    <th>Period</th>
                    <th>Generated</th>
                    <th>Prepared by</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($reports)): ?>
                    <tr><td colspan="6" class="md-empty">No reports found.</td></tr>
                <?php endif; ?>

                <?php foreach ($reports as $r): ?>
                    <?php $q = rawurlencode($r['type'] . ':' . $r['reportId']); ?>
                    <tr>
                        <td><span class="md-badge md-<?= h($r['type']) ?>"><?= h($types[$r['type']]['label']) ?></span></td>
                        <td><strong><?= h($r['reportId']) ?></strong></td>
                        <td><?= h(md_period($r)) ?></td>
                        <td><?= h(date('d M Y, H:i', strtotime($r['generatedAt']))) ?></td>
                        <td><?= h($r['preparedBy']) ?></td>
                        <td class="md-actions">
                            <a class="md-btn small md-view-link" target="reportFrame"
                               href="report_view.php?report=<?= $q ?>">View</a>
                            <a class="md-btn small secondary"
                               href="report_view.php?report=<?= $q ?>&amp;download=1">PDF</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<script>
(function () {
    var form  = document.getElementById('pickForm');
    var frame = document.getElementById('reportFrame');
    var hint  = document.getElementById('viewerPlaceholder');
    if (!frame) { return; }

    function showViewer() {
        hint.hidden = true;
        frame.hidden = false;
        frame.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    // "View report" shows the PDF in the frame; "Download PDF" does not.
    form.addEventListener('submit', function (e) {
        if (!e.submitter || e.submitter.name !== 'download') { showViewer(); }
    });

    document.querySelectorAll('.md-view-link').forEach(function (a) {
        a.addEventListener('click', showViewer);
    });
})();
</script>

<?php md_footer(); ?>
