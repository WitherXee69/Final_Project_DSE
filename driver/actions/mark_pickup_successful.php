<?php

function markPickupSuccessful(PDO $pdo, string $pickupId, string $driverId): string
{
    // Check whether the pickup is assigned to this driver.
    $checkPickup = $pdo->prepare(
        "SELECT p.pickupId, p.status
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

    $pickup = $checkPickup->fetch();

    if (!$pickup) {
        throw new RuntimeException('You are not assigned to this pickup.');
    }

    // Update the pickup status.
    $updatePickup = $pdo->prepare(
        "UPDATE Pickup
         SET status = 'COMPLETED',
             failureReason = NULL
         WHERE pickupId = :pickupId"
    );

    $updatePickup->execute([
        'pickupId' => $pickupId
    ]);

    return 'Pickup ' . $pickupId . ' has been marked as completed.';
}

?>
