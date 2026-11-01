<?php

function viewDriverManifest(PDO $pdo, string $driverId): array
{
    // Check whether the user is registered as a driver.
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

    // Retrieve the driver's assigned manifests.
    $manifestStmt = $pdo->prepare(
        "SELECT dm.driverManifestId,
                dm.assignedDate,
                dm.schedule,
                dm.status,
                dmf.manifestDate,
                dmf.route,
                dmf.manifestStatus,
                v.registrationNumber,
                v.vehicleType
         FROM DriverManifest dm
         LEFT JOIN DeliveryManifest dmf
            ON dmf.deliveryManifestId = dm.deliveryManifestId
         LEFT JOIN Vehicle v
            ON v.vehicleId = dm.vehicleId
         WHERE dm.driverId = :driverId
         ORDER BY dm.assignedDate DESC,
                  dm.driverManifestId DESC
         LIMIT 8"
    );

    $manifestStmt->execute([
        'driverId' => $driverId
    ]);

    return $manifestStmt->fetchAll();
}

?>
