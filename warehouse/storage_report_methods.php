<?php
/**
 * HELMS Warehouse module - saving Storage Reports.
 *
 * generateStorageReport() (warehouse_methods.php) works out the live numbers.
 * saveStorageReport() stores them as a row in StorageReport so the report can be
 * opened as a PDF (reports/storage_report.jrxml) and seen by the Managing Director.
 */

require_once __DIR__ . '/warehouse_methods.php';

/**
 * Save a new StorageReport for this warehouse manager and return its reportId.
 * The figures are a snapshot of "right now"; the period is month-to-date.
 */
function saveStorageReport(PDO $pdo, string $managerUserId): string
{
    // Throws a RuntimeException if the manager has no warehouse assigned.
    $report = generateStorageReport($pdo, $managerUserId);

    $stmt = $pdo->prepare(
        "INSERT INTO StorageReport
            (reportId, generatedAt, periodStart, periodEnd,
             parcelsStored, unclaimedParcels, capacityUtilisation, warehouseMgrId)
         VALUES
            (:id, NOW(), :start, :end, :stored, :unclaimed, :util, :mgr)"
    );

    for ($attempt = 0; $attempt < 3; $attempt++) {
        // reportId is VARCHAR(20): "RPT-STO-" (8) + yymmddhhmmss (12) = 20 characters.
        $reportId = 'RPT-STO-' . date('ymdHis');

        try {
            $stmt->execute([
                'id'        => $reportId,
                'start'     => date('Y-m-01'),
                'end'       => date('Y-m-d'),
                'stored'    => (int) $report['parcelsStored'],
                'unclaimed' => (int) $report['unclaimedParcels'],
                'util'      => min(999.99, (float) $report['capacityUtilisation']),
                'mgr'       => $managerUserId,
            ]);

            return $reportId;
        } catch (PDOException $e) {
            // Two clicks in the same second -> duplicate key. Wait a second and retry.
            if ($e->getCode() === '23000' && $attempt < 2) {
                sleep(1);
                continue;
            }
            throw $e;
        }
    }

    throw new RuntimeException('Could not save the report. Please try again.');
}

/** This manager's saved reports, newest first. */
function listStorageReports(PDO $pdo, string $managerUserId): array
{
    $stmt = $pdo->prepare(
        "SELECT reportId, generatedAt, periodStart, periodEnd,
                parcelsStored, unclaimedParcels, capacityUtilisation
         FROM StorageReport
         WHERE warehouseMgrId = :mgr
         ORDER BY generatedAt DESC
         LIMIT 20"
    );
    $stmt->execute(['mgr' => $managerUserId]);

    return $stmt->fetchAll();
}
