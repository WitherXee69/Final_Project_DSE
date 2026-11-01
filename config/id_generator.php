<?php
declare(strict_types=1);

/**
 * Generate the next parcel ID using existing Parcel records.
 */
function generateNextId(
    PDO $pdo,
    string $sequenceName,
    string $prefix,
    int $padding = 6
): string {

    // This generator is currently intended for parcel IDs.
    if (!preg_match('/^PARCEL_(\d{4})$/', $sequenceName, $matches)) {
        throw new InvalidArgumentException('Invalid parcel sequence name.');
    }

    $year = $matches[1];

    // Find the highest existing ID for this year.
    $stmt = $pdo->prepare(
        "SELECT MAX(CAST(SUBSTRING(parcelId, 10) AS UNSIGNED))
         FROM Parcel
         WHERE parcelId LIKE :pattern"
    );

    $stmt->execute([
        'pattern' => 'HEN-' . $year . '-%'
    ]);

    $lastValue = (int) $stmt->fetchColumn();

    $nextValue = $lastValue + 1;

    return $prefix . str_pad(
        (string) $nextValue,
        $padding,
        '0',
        STR_PAD_LEFT
    );
}
?>