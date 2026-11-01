<?php

function updateAvailability(PDO $pdo, string $driverId, string $status): string
{
    $allowedStatuses = [
        'AVAILABLE',
        'ON_DUTY',
        'OFF_DUTY'
    ];

    if (!in_array($status, $allowedStatuses, true)) {
        throw new InvalidArgumentException('Invalid availability status.');
    }

    $checkDriver = $pdo->prepare(
        "SELECT userId
         FROM Driver
         WHERE userId = :driverId
         LIMIT 1"
    );

    $checkDriver->execute([
        'driverId' => $driverId
    ]);

    if (!$checkDriver->fetch()) {
        throw new RuntimeException('This account is not registered as a driver.');
    }

    $updateDriver = $pdo->prepare(
        "UPDATE Driver
         SET status = :status
         WHERE userId = :driverId"
    );

    $updateDriver->execute([
        'status' => $status,
        'driverId' => $driverId
    ]);

    return 'Your availability has been updated.';
}

?>
