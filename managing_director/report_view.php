<?php
/**
 * Generates one report through JasperReports and sends it back as a PDF.
 *   report_view.php?report=payment:RPT-PAY-2026-10            -> shown in the viewer frame
 *   report_view.php?report=payment:RPT-PAY-2026-10&download=1 -> downloaded
 */
require_once __DIR__ . '/_layout.php';
require_once __DIR__ . '/../config/jasper.php';
require_once __DIR__ . '/report_methods.php';

md_require_director();

/** Small stand-alone page shown inside the viewer frame when something goes wrong. */
function md_error_page(int $status, string $title, string $message, string $detail = ''): void
{
    http_response_code($status);
    ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?= h($title) ?></title>
    <link rel="stylesheet" href="../assets/css/md.css">
</head>
<body class="md-error-page">
    <h2><?= h($title) ?></h2>
    <p><?= h($message) ?></p>
    <?php if ($detail !== ''): ?>
        <pre><?= h($detail) ?></pre>
    <?php endif; ?>
</body>
</html>
    <?php
    exit;
}

$raw = (string) ($_GET['report'] ?? '');

if (!preg_match('/^([a-z]+):([A-Za-z0-9-]{1,30})$/', $raw, $m)) {
    md_error_page(400, 'No report selected', 'Please choose a report from the list.');
}

[$all, $type, $reportId] = $m;
$types = md_report_types();

if (!isset($types[$type])) {
    md_error_page(400, 'Unknown report type', 'That report type does not exist.');
}

if (!md_report_exists($pdo, $type, $reportId)) {
    md_error_page(404, 'Report not found', 'No saved report has the ID ' . $reportId . '.');
}

md_record_view($pdo, (string) $_SESSION['user_id'], $type, $reportId);

session_write_close();

try {
    $pdf = md_run_jasper($types[$type]['jrxml'], $reportId);
} catch (Throwable $e) {
    error_log('Jasper report error: ' . $e->getMessage());

    md_error_page(
        500,
        'The report could not be generated',
        'Please tell the system administrator. (Setup help: SETUP_JASPER.md)',
        JASPER_DEBUG ? $e->getMessage() : ''
    );
}

$disposition = isset($_GET['download']) ? 'attachment' : 'inline';

header('Content-Type: application/pdf');
header('Content-Disposition: ' . $disposition . '; filename="' . $reportId . '.pdf"');
header('Content-Length: ' . strlen($pdf));
header('Cache-Control: private, no-store');
echo $pdf;
