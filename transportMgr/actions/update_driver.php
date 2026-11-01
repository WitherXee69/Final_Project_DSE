<?php

function updateDriver($pdo, $driverId, $licenseNumber, $contactNumber, $status, $transportMgrId)
{
    $driverId = trim($driverId);
    $licenseNumber = trim($licenseNumber);
    $contactNumber = trim($contactNumber);
    $status = trim($status);

    if (empty($driverId) || empty($licenseNumber) || empty($contactNumber)) {
        throw new InvalidArgumentException("Please fill in all required fields.");
    }

    $allowedStatuses = ['AVAILABLE', 'ON_DUTY', 'OFF_DUTY'];

    if (!in_array($status, $allowedStatuses)) {
        throw new InvalidArgumentException("Invalid driver status.");
    }

    if (strlen($licenseNumber) > 50 || strlen($contactNumber) > 30) {
        throw new InvalidArgumentException("One or more fields exceed the allowed length.");
    }

    // Check whether the driver belongs to this Transport Manager
    $stmt = $pdo->prepare("
        SELECT userId
        FROM Driver
        WHERE userId = ? AND transportMgrId = ?
    ");

    $stmt->execute([$driverId, $transportMgrId]);

    if (!$stmt->fetch()) {
        throw new RuntimeException("Driver not found or you do not have permission to update this driver.");
    }

    // Check for duplicate license numbers
    $stmt = $pdo->prepare("
        SELECT userId
        FROM Driver
        WHERE licenseNumber = ? AND userId != ?
    ");

    $stmt->execute([$licenseNumber, $driverId]);

    if ($stmt->fetch()) {
        throw new RuntimeException("License number already exists.");
    }

    // Update driver details
    $stmt = $pdo->prepare("
        UPDATE Driver
        SET licenseNumber = ?, contactNumber = ?, status = ?
        WHERE userId = ? AND transportMgrId = ?
    ");

    $stmt->execute([
        $licenseNumber,
        $contactNumber,
        $status,
        $driverId,
        $transportMgrId
    ]);

    return "Driver details updated successfully.";
}