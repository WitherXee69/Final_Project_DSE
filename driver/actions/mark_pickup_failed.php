
<?php

function markPickupFailed(PDO $pdo, string $pickupId, string $driverId, string $failureReason): string
{
    $failureReason = trim($failureReason);

    if ($failureReason === '') {
        throw new InvalidArgumentException('Please provide a reason for the failed pickup.');
    }

    if (strlen($failureReason) > 255) {
        throw new InvalidArgumentException('The pickup failure reason is too long.');
    }

    // Check whether the pickup is assigned to this driver.
    $checkPickup = $pdo->prepare(
        "SELECT p.pickupId
         FROM Pickup p
         INNER JOIN DriverManifest dm
            ON dm.driverManifestId = p.driverManifestId
         WHERE p.pickupId = :pickupId
           AND dm.driverId = :driverId
         LIMIT 1"
    );

    $checkPickup->execute([
        'pickupId' => $pickupId,
        'driverId' => $driverId
    ]);

    if (!$checkPickup->fetch()) {
        throw new RuntimeException('You are not assigned to this pickup.');
    }

    // Update the pickup status and failure reason.
    $updatePickup = $pdo->prepare(
        "UPDATE Pickup
         SET status = 'FAILED',
             failureReason = :failureReason
         WHERE pickupId = :pickupId"
    );

    $updatePickup->execute([
        'failureReason' => $failureReason,
        'pickupId' => $pickupId
    ]);

    return 'Pickup ' . $pickupId . ' has been marked as failed.';
}

?>
