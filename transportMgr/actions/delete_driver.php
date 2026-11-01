<?php

function deleteDriver($pdo, $driverId, $transportMgrId)
{
    $driverId = trim($driverId);

    if (empty($driverId)) {
        throw new InvalidArgumentException("Invalid driver ID.");
    }

    // Check whether the driver belongs to this Transport Manager
    $stmt = $pdo->prepare("
        SELECT userId
        FROM Driver
        WHERE userId = ? AND transportMgrId = ?
    ");

    $stmt->execute([$driverId, $transportMgrId]);

    if (!$stmt->fetch()) {
        throw new RuntimeException("Driver not found or you do not have permission to delete this driver.");
    }

    // Check whether the driver has existing manifest records
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM DriverManifest
        WHERE driverId = ?
    ");

    $stmt->execute([$driverId]);

    if ($stmt->fetchColumn() > 0) {
        throw new RuntimeException("This driver cannot be deleted because they have existing manifest records.");
    }

    try {
        $pdo->beginTransaction();

        // Delete the Driver record
        $stmt = $pdo->prepare("
            DELETE FROM Driver
            WHERE userId = ? AND transportMgrId = ?
        ");

        $stmt->execute([$driverId, $transportMgrId]);

        // Delete the associated User record
        $stmt = $pdo->prepare("
            DELETE FROM User
            WHERE userId = ? AND role = 'Driver'
        ");

        $stmt->execute([$driverId]);

        $pdo->commit();

        return "Driver deleted successfully.";

    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $e;
    }
}