<?php
/**
 * Shows / downloads one of the Warehouse Manager's OWN saved storage reports as a PDF,
 * built from reports/storage_report.jrxml through JasperReports.
 *   report_view.php?id=RPT-STO-261101153045
 *   report_view.php?id=RPT-STO-261101153045&download=1
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/jasper.php';
require_once __DIR__ . '/../managing_director/report_methods.php';   // md_run_jasper()

function wh_pdf_error(int $status, string $title, string $message, string $detail = ''): void
{
    http_response_code($status);
    echo '<!doctype html><meta charset="utf-8"><title>' . h($title) . '</title>'
       . '<body style="font-family:system-ui,Arial,sans-serif;padding:28px;color:#16202e">'
       . '<h2 style="color:#b91c1c;font-size:1.1rem;margin:0 0 8px">' . h($title) . '</h2>'
       . '<p style="color:#475569;font-size:.92rem">' . h($message) . '</p>';
    if ($detail !== '') {
        echo '<pre style="background:#f8fafc;border:1px solid #e3e8ef;border-radius:8px;padding:12px;'
           . 'font-size:.78rem;white-space:pre-wrap;word-break:break-word">' . h($detail) . '</pre>';
    }
    echo '</body>';
    exit;
}

// Must be a logged-in Warehouse Manager.
if (empty($_SESSION['user_id'])) {
    wh_pdf_error(401, 'Please log in', 'Your session has ended. Log in again and retry.');
}
if (($_SESSION['role'] ?? '') !== 'WarehouseManager') {
    wh_pdf_error(403, 'Access restricted', 'Storage reports are available to Warehouse Managers only.');
}

$reportId = (string) ($_GET['id'] ?? '');
if (!preg_match('/^RPT-STO-[A-Za-z0-9-]{1,12}$/', $reportId)) {
    wh_pdf_error(400, 'No report selected', 'Please choose a report from the list.');
}

// A manager may only open reports that belong to them.
$stmt = $pdo->prepare('SELECT 1 FROM StorageReport WHERE reportId = ? AND warehouseMgrId = ? LIMIT 1');
$stmt->execute([$reportId, $_SESSION['user_id']]);
if (!$stmt->fetchColumn()) {
    wh_pdf_error(404, 'Report not found', 'None of your saved reports has the ID ' . $reportId . '.');
}

session_write_close();   // do not block other pages while Java runs

try {
    $pdf = md_run_jasper(md_report_types()['storage']['jrxml'], $reportId);
} catch (Throwable $e) {
    error_log('Storage report PDF error: ' . $e->getMessage());
    wh_pdf_error(
        500,
        'The report could not be generated',
        'Please tell the system administrator. (Setup help: SETUP_JASPER.md)',
        JASPER_DEBUG ? $e->getMessage() : ''
    );
}

header('Content-Type: application/pdf');
header('Content-Disposition: ' . (isset($_GET['download']) ? 'attachment' : 'inline') . '; filename="' . $reportId . '.pdf"');
header('Content-Length: ' . strlen($pdf));
header('Cache-Control: private, no-store');
echo $pdf;
