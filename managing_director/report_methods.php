<?php
/**
 * HELMS Managing Director report and JasperReports helpers.
 */

/**
 * The four report types the Managing Director can open.
 * Table / column names come ONLY from this list (never from user input),
 * so it is safe to place them inside SQL strings below.
 */
function md_report_types(): array
{
    return [
        'payment' => [
            'label' => 'Payment Report',
            'table' => 'PaymentReport',
            'views' => 'MDViewsPaymentReport',
            'mgr' => 'financeMgrId',
            'jrxml' => 'payment_report.jrxml',
        ],
        'delivery' => [
            'label' => 'Delivery Report',
            'table' => 'DeliveryReport',
            'views' => 'MDViewsDeliveryReport',
            'mgr' => 'transportMgrId',
            'jrxml' => 'delivery_report.jrxml',
        ],
        'storage' => [
            'label' => 'Storage Report',
            'table' => 'StorageReport',
            'views' => 'MDViewsStorageReport',
            'mgr' => 'warehouseMgrId',
            'jrxml' => 'storage_report.jrxml',
        ],
        'customer' => [
            'label' => 'Customer Report',
            'table' => 'CustomerReport',
            'views' => 'MDViewsCustomerReport',
            'mgr' => 'csMgrId',
            'jrxml' => 'customer_report.jrxml',
        ],
    ];
}

/** Every saved report of every type, newest first. */
function md_list_reports(PDO $pdo): array
{
    $all = [];

    foreach (md_report_types() as $key => $t) {
        $sql = "SELECT r.reportId, r.generatedAt, r.periodStart, r.periodEnd,
                       COALESCE(u.username, 'N/A') AS preparedBy
                FROM {$t['table']} r
                LEFT JOIN User u ON u.userId = r.{$t['mgr']}
                ORDER BY r.generatedAt DESC";

        foreach ($pdo->query($sql) as $row) {
            $row['type'] = $key;
            $all[] = $row;
        }
    }

    usort($all, function ($a, $b) {
        return strcmp($b['generatedAt'], $a['generatedAt']);
    });

    return $all;
}

function md_report_exists(PDO $pdo, string $type, string $reportId): bool
{
    $types = md_report_types();
    if (!isset($types[$type])) {
        return false;
    }

    $stmt = $pdo->prepare(
        "SELECT 1 FROM {$types[$type]['table']} WHERE reportId = ? LIMIT 1"
    );
    $stmt->execute([$reportId]);

    return (bool) $stmt->fetchColumn();
}

/** Remember that this director opened this report (MDViews... tables). Never fatal. */
function md_record_view(PDO $pdo, string $directorId, string $type, string $reportId): void
{
    $types = md_report_types();

    try {
        $stmt = $pdo->prepare(
            "INSERT IGNORE INTO {$types[$type]['views']} (directorId, reportId) VALUES (?, ?)"
        );
        $stmt->execute([$directorId, $reportId]);
    } catch (PDOException $e) {
        error_log('MD view log failed: ' . $e->getMessage());
    }
}

/** One headline number per report type, taken from the latest saved report. */
function md_headlines(PDO $pdo): array
{
    $queries = [
        'payment' => [
            'Net collected',
            "SELECT reportId, periodStart, periodEnd,
                    COALESCE(totalCollected,0) - COALESCE(totalRefunded,0) AS v
             FROM PaymentReport ORDER BY generatedAt DESC LIMIT 1",
            function ($v) {
                return 'LKR ' . number_format((float) $v, 2);
            },
        ],
        'delivery' => [
            'Total deliveries',
            "SELECT reportId, periodStart, periodEnd, COALESCE(totalDeliveries,0) AS v
             FROM DeliveryReport ORDER BY generatedAt DESC LIMIT 1",
            function ($v) {
                return number_format((int) $v);
            },
        ],
        'storage' => [
            'Capacity utilisation',
            "SELECT reportId, periodStart, periodEnd, COALESCE(capacityUtilisation,0) AS v
             FROM StorageReport ORDER BY generatedAt DESC LIMIT 1",
            function ($v) {
                return number_format((float) $v, 1) . '%';
            },
        ],
        'customer' => [
            'Active customers',
            "SELECT reportId, periodStart, periodEnd, COALESCE(activeCustomers,0) AS v
             FROM CustomerReport ORDER BY generatedAt DESC LIMIT 1",
            function ($v) {
                return number_format((int) $v);
            },
        ],
    ];

    $types = md_report_types();
    $out = [];

    foreach ($queries as $key => [$label, $sql, $fmt]) {
        $row = $pdo->query($sql)->fetch();
        $out[] = [
            'type'   => $key,
            'title'  => $types[$key]['label'],
            'label'  => $label,
            'value'  => $row ? $fmt($row['v']) : '-',
            'period' => $row
                ? date('d M', strtotime($row['periodStart'])) . ' - '
                    . date('d M Y', strtotime($row['periodEnd']))
                : 'No report saved yet',
        ];
    }

    return $out;
}

/**
 * Run a .jrxml through JasperStarter and return the PDF bytes.
 * Throws RuntimeException with a readable message if anything goes wrong.
 *
 * Java is started directly with the JasperStarter jars AND the MySQL driver on
 * the classpath. (JasperStarter's own "load the driver at runtime" step crashes
 * on Java 9 and newer, so we avoid it by pointing --jdbc-dir at an empty folder.)
 */
function md_run_jasper(string $jrxmlFile, string $reportId): string
{
    // Database login comes from config/database.php (loaded at the top of the page).
    global $host, $dbname, $username, $password;

    if (!function_exists('proc_open')) {
        throw new RuntimeException(
            'PHP function proc_open() is disabled. Remove it from "disable_functions" in php.ini.'
        );
    }
    if (!is_dir(JASPER_LIB_DIR)) {
        throw new RuntimeException(
            'JasperStarter lib folder not found: ' . JASPER_LIB_DIR . ' (edit config/jasper.php).'
        );
    }
    if (!is_file(JASPER_MYSQL_JAR)) {
        throw new RuntimeException(
            'MySQL driver jar not found: ' . JASPER_MYSQL_JAR . ' (edit config/jasper.php).'
        );
    }

    $jrxml = JASPER_REPORT_DIR . '/' . $jrxmlFile;
    if (!is_file($jrxml)) {
        throw new RuntimeException('Report template not found: ' . $jrxml);
    }

    $tmpDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'helms_reports';
    $emptyJdbc = $tmpDir . DIRECTORY_SEPARATOR . 'empty_jdbc';
    foreach ([$tmpDir, $emptyJdbc] as $dir) {
        if (!is_dir($dir) && !mkdir($dir, 0777, true) && !is_dir($dir)) {
            throw new RuntimeException('Cannot create temp folder: ' . $dir);
        }
    }

    // JasperStarter adds ".pdf" to this name itself.
    $outBase = $tmpDir . DIRECTORY_SEPARATOR . 'rpt_' . bin2hex(random_bytes(8));
    $pdfFile = $outBase . '.pdf';

    $jdbcUrl = 'jdbc:mysql://' . $host . ':' . JASPER_DB_PORT . '/' . $dbname
        . '?useSSL=false&serverTimezone=UTC&allowPublicKeyRetrieval=true';

    // "lib/*" = every jar in that folder (Java expands the * itself).
    $classpath = rtrim(JASPER_LIB_DIR, '/\\')
        . '/*' . PATH_SEPARATOR . JASPER_MYSQL_JAR;

    // Passing an ARRAY to proc_open means PHP handles all quoting (needs PHP 7.4+).
    $cmd = [
        JASPER_JAVA,
        '-Djava.awt.headless=true',
        '-cp',
        $classpath,
        'de.cenote.jasperstarter.App',
        'process',
        $jrxml,
        '-f',
        'pdf',
        '-o',
        $outBase,
        '-t',
        'generic',
        '--jdbc-dir',
        $emptyJdbc,
        '--db-driver',
        JASPER_DB_DRIVER,
        '--db-url',
        $jdbcUrl,
        '-u',
        $username,
    ];
    if ($password !== '') {
        $cmd[] = '-p';
        $cmd[] = $password;
    }
    $cmd[] = '-P';
    $cmd[] = 'REPORT_ID=' . $reportId;

    $process = proc_open($cmd, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);

    if (!is_resource($process)) {
        throw new RuntimeException(
            'Could not start Java. Check JASPER_JAVA in config/jasper.php.'
        );
    }

    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $exitCode = proc_close($process);

    if ($exitCode !== 0 || !is_file($pdfFile)) {
        @unlink($pdfFile);
        $detail = trim($stderr . "\n" . $stdout);
        throw new RuntimeException(
            'JasperStarter failed (exit code ' . $exitCode . '): ' . $detail
        );
    }

    $pdf = file_get_contents($pdfFile);
    @unlink($pdfFile);

    if ($pdf === false || $pdf === '') {
        throw new RuntimeException('JasperStarter produced an empty PDF.');
    }

    return $pdf;
}
