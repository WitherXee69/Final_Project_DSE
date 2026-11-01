<?php

function submitDeliveryPIN(PDO $pdo, string $deliveryId, string $driverId, string $pin): string
{
    $pin = trim($pin);

    // Validate the PIN format.
    if (!preg_match('/^[0-9]{3,6}$/', $pin)) {
        throw new InvalidArgumentException(
            'Please enter a valid numeric PIN.'
        );
    }

    $pdo->beginTransaction();

    try {

        // Verify the delivery belongs to this driver.
        $checkDelivery = $pdo->prepare(
            "SELECT de.deliveryId,
                    de.parcelId,
                    de.status
             FROM Delivery de
             INNER JOIN DriverManifest dm
                ON dm.driverManifestId = de.driverManifestId
             WHERE de.deliveryId = :deliveryId
               AND dm.driverId = :driverId
             LIMIT 1
             FOR UPDATE"
        );

        $checkDelivery->execute([
            'deliveryId' => $deliveryId,
            'driverId' => $driverId
        ]);

        $delivery = $checkDelivery->fetch();

        if (!$delivery) {
            throw new RuntimeException(
                'You are not assigned to this delivery.'
            );
        }

        if ($delivery['status'] === 'DELIVERED') {
            throw new RuntimeException(
                'This delivery has already been completed.'
            );
        }

        if ($delivery['status'] !== 'ARRIVED') {
            throw new RuntimeException(
                'The delivery must be marked as arrived first.'
            );
        }

        // Find the latest delivery attempt.
        $attemptStmt = $pdo->prepare(
            "SELECT attemptId
             FROM DeliveryAttempt
             WHERE deliveryId = :deliveryId
             ORDER BY attemptNumber DESC
             LIMIT 1
             FOR UPDATE"
        );

        $attemptStmt->execute([
            'deliveryId' => $deliveryId
        ]);

        $attempt = $attemptStmt->fetch();

        if (!$attempt) {
            throw new RuntimeException(
                'No delivery attempt was found.'
            );
        }

        // Find an existing PIN verification for this attempt.
        $verificationStmt = $pdo->prepare(
            "SELECT verificationId,
                    attemptCount,
                    status
             FROM DeliveryVerification
             WHERE attemptId = :attemptId
             LIMIT 1
             FOR UPDATE"
        );

        $verificationStmt->execute([
            'attemptId' => $attempt['attemptId']
        ]);

        $verification = $verificationStmt->fetch();

        if (
            $verification &&
            in_array($verification['status'], ['VERIFIED', 'FAILED'], true)
        ) {
            throw new RuntimeException(
                'PIN verification for this delivery attempt is already finished.'
            );
        }

        $attemptCount = $verification
            ? (int) $verification['attemptCount']
            : 0;

        if ($attemptCount >= 3) {
            throw new RuntimeException(
                'No PIN attempts remaining. Delivery attempt unsuccessful.'
            );
        }

        $attemptCount++;

        // Hash the submitted PIN.
        $pinHash = password_hash(
            $pin,
            PASSWORD_DEFAULT
        );

        // PIN 000 is used to simulate an incorrect PIN.
        $pinFailed = ($pin === '000');

        if ($pinFailed && $attemptCount < 3) {

            // First or second incorrect PIN.
            $verificationStatus = 'ACTIVE';

        } elseif ($pinFailed && $attemptCount === 3) {

            // Third incorrect PIN.
            $verificationStatus = 'FAILED';

        } else {

            // Any other valid PIN succeeds in the demonstration.
            $verificationStatus = 'VERIFIED';
        }

        if ($verification) {

            // Update the existing verification record.
            $updateVerification = $pdo->prepare(
                "UPDATE DeliveryVerification
                 SET pinHash = :pinHash,
                     attemptCount = :attemptCount,
                     status = :status,
                     verifiedAt = :verifiedAt
                 WHERE verificationId = :verificationId"
            );

            $updateVerification->execute([
                'pinHash' => $pinHash,
                'attemptCount' => $attemptCount,
                'status' => $verificationStatus,
                'verifiedAt' => $verificationStatus === 'VERIFIED'
                    ? date('Y-m-d H:i:s')
                    : null,
                'verificationId' => $verification['verificationId']
            ]);

        } else {

            // Generate the next verification ID.
            $idStmt = $pdo->query(
                "SELECT COALESCE(
                    MAX(CAST(SUBSTRING(verificationId, 5) AS UNSIGNED)), 0
                 ) + 1
                 FROM DeliveryVerification
                 WHERE verificationId LIKE 'VER-%'"
            );

            $nextId = (int) $idStmt->fetchColumn();

            $verificationId = 'VER-' .
                str_pad($nextId, 6, '0', STR_PAD_LEFT);

            // Create the verification record.
            $insertVerification = $pdo->prepare(
                "INSERT INTO DeliveryVerification
                    (
                        verificationId,
                        pinHash,
                        generatedAt,
                        expiresAt,
                        verifiedAt,
                        attemptCount,
                        status,
                        attemptId
                    )
                 VALUES
                    (
                        :verificationId,
                        :pinHash,
                        NOW(),
                        DATE_ADD(NOW(), INTERVAL 15 MINUTE),
                        :verifiedAt,
                        :attemptCount,
                        :status,
                        :attemptId
                    )"
            );

            $insertVerification->execute([
                'verificationId' => $verificationId,
                'pinHash' => $pinHash,
                'verifiedAt' => $verificationStatus === 'VERIFIED'
                    ? date('Y-m-d H:i:s')
                    : null,
                'attemptCount' => $attemptCount,
                'status' => $verificationStatus,
                'attemptId' => $attempt['attemptId']
            ]);
        }

        if ($verificationStatus === 'ACTIVE') {

            $pdo->commit();

            $remainingAttempts = 3 - $attemptCount;

            return 'PIN failed. ' . $remainingAttempts .
                   ' attempts remaining.';
        }

        if ($verificationStatus === 'FAILED') {

            // Mark the delivery attempt as unsuccessful.
            $updateAttempt = $pdo->prepare(
                "UPDATE DeliveryAttempt
                 SET outcome = 'FAILED',
                     failureReason = 'Receiver PIN verification failed after 3 attempts.'
                 WHERE attemptId = :attemptId"
            );

            $updateAttempt->execute([
                'attemptId' => $attempt['attemptId']
            ]);

            // Mark the delivery as failed.
            $updateDelivery = $pdo->prepare(
                "UPDATE Delivery
                 SET status = 'FAILED',
                     failedAttemptCount = failedAttemptCount + 1
                 WHERE deliveryId = :deliveryId"
            );

            $updateDelivery->execute([
                'deliveryId' => $deliveryId
            ]);

            $message = 'Delivery attempt unsuccessful.';

        } else {

            // Mark the delivery attempt as successful.
            $updateAttempt = $pdo->prepare(
                "UPDATE DeliveryAttempt
                 SET outcome = 'DELIVERED',
                     failureReason = NULL
                 WHERE attemptId = :attemptId"
            );

            $updateAttempt->execute([
                'attemptId' => $attempt['attemptId']
            ]);

            // Mark the delivery as delivered.
            $updateDelivery = $pdo->prepare(
                "UPDATE Delivery
                 SET status = 'DELIVERED'
                 WHERE deliveryId = :deliveryId"
            );

            $updateDelivery->execute([
                'deliveryId' => $deliveryId
            ]);

            $message = 'Delivery ' . $deliveryId .
                       ' has been successfully completed.';
        }

        // Generate the next tracking event ID.
        $eventIdStmt = $pdo->query(
            "SELECT COALESCE(
                MAX(CAST(SUBSTRING(eventId, 5) AS UNSIGNED)), 0
             ) + 1
             FROM TrackingEvent
             WHERE eventId LIKE 'EVT-%'"
        );

        $nextEventId = (int) $eventIdStmt->fetchColumn();

        $eventId = 'EVT-' .
            str_pad($nextEventId, 6, '0', STR_PAD_LEFT);

        // Record the final delivery result.
        $trackingStmt = $pdo->prepare(
            "INSERT INTO TrackingEvent
                (
                    eventId,
                    eventType,
                    occurredAt,
                    remarks,
                    actorType,
                    actorId,
                    parcelId,
                    deliveryId
                )
             VALUES
                (
                    :eventId,
                    :eventType,
                    NOW(),
                    :remarks,
                    'Driver',
                    :driverId,
                    :parcelId,
                    :deliveryId
                )"
        );

        $trackingStmt->execute([
            'eventId' => $eventId,
            'eventType' => $verificationStatus === 'VERIFIED'
                ? 'Delivery Completed'
                : 'Delivery Attempt Failed',
            'remarks' => $message,
            'driverId' => $driverId,
            'parcelId' => $delivery['parcelId'],
            'deliveryId' => $deliveryId
        ]);

        $pdo->commit();

        return $message;

    } catch (Throwable $e) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $e;
    }
}

?>
