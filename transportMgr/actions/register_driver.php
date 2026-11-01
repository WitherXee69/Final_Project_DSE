<?php

function registerDriver($pdo, $username, $password, $licenseNumber, $contactNumber, $status, $transportMgrId)
{
    $username = trim($username);
    $licenseNumber = trim($licenseNumber);
    $contactNumber = trim($contactNumber);
    $status = trim($status);

    if (empty($username) || empty($password) || empty($licenseNumber) || empty($contactNumber)) {
        throw new InvalidArgumentException("Please fill in all required fields.");
    }

    $allowedStatuses = ['AVAILABLE', 'ON_DUTY', 'OFF_DUTY'];

    if (!in_array($status, $allowedStatuses)) {
        throw new InvalidArgumentException("Invalid driver status.");
    }

    if (strlen($username) > 50 || strlen($licenseNumber) > 50 || strlen($contactNumber) > 30) {
        throw new InvalidArgumentException("One or more fields exceed the allowed length.");
    }

    try {
        $pdo->beginTransaction();

        // Check whether username already exists
        $stmt = $pdo->prepare("SELECT userId FROM User WHERE username = ?");
        $stmt->execute([$username]);

        if ($stmt->fetch()) {
            throw new RuntimeException("Username already exists.");
        }

        // Check whether license number already exists
        $stmt = $pdo->prepare("SELECT userId FROM Driver WHERE licenseNumber = ?");
        $stmt->execute([$licenseNumber]);

        if ($stmt->fetch()) {
            throw new RuntimeException("License number already exists.");
        }

        // Generate the next user ID
        $stmt = $pdo->query("
            SELECT MAX(CAST(SUBSTRING(userId, 5) AS UNSIGNED))
            FROM User
            WHERE userId LIKE 'USR-%'
        ");

        $lastId = $stmt->fetchColumn();
        $nextId = $lastId + 1;
        $userId = 'USR-' . str_pad($nextId, 6, '0', STR_PAD_LEFT);

        // Hash the password
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // Insert into User table
        $stmt = $pdo->prepare("
            INSERT INTO User (userId, username, passwordHash, role, adminId)
            VALUES (?, ?, ?, 'Driver', NULL)
        ");

        $stmt->execute([$userId, $username, $passwordHash]);

        // Insert into Driver table
        $stmt = $pdo->prepare("
            INSERT INTO Driver (userId, licenseNumber, contactNumber, status, transportMgrId)
            VALUES (?, ?, ?, ?, ?)
        ");

        $stmt->execute([$userId, $licenseNumber, $contactNumber, $status, $transportMgrId]);

        $pdo->commit();

        return "Driver registered successfully. Driver ID: " . $userId;

    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $e;
    }
}