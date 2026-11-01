
<?php

function markDeliveryArrived(PDO $pdo, string $deliveryId, string $driverId): string
{
    // Verify that the delivery is assigned to this driver.
    $checkDelivery = $pdo->prepare(
        "SELECT de.deliveryId,
                de.parcelId,
                de.status
         FROM Delivery de
         INNER JOIN DriverManifest dm
            ON dm.driverManifestId = de.driverManifestId
         WHERE de.deliveryId = :deliveryId
           AND dm.driverId = :driverId
         LIMIT 1"
    );

    $checkDelivery->execute([
        'deliveryId' => $deliveryId,
        'driverId' => $driverId
    ]);

    $delivery = $checkDelivery->fetch();

    if (!$delivery) {
        throw new RuntimeException('You are not assigned to this delivery.');
    }

    if ($delivery['status'] === 'DELIVERED') {
        throw new RuntimeException('This delivery has already been completed.');
    }

    if ($delivery['status'] === 'ARRIVED') {
        return 'You have already marked this delivery as arrived.';
    }

    $pdo->beginTransaction();

    try {
        // Find the next attempt number.
        $attemptNumberStmt = $pdo->prepare(
            "SELECT COALESCE(MAX(attemptNumber), 0) + 1
             FROM DeliveryAttempt
             WHERE deliveryId = :deliveryId"
        );

        $attemptNumberStmt->execute([
            'deliveryId' => $deliveryId
        ]);

        $attemptNumber = (int) $attemptNumberStmt->fetchColumn();

        // Generate the next attempt ID.
        $attemptIdStmt = $pdo->query(
            "SELECT COALESCE(
                MAX(CAST(SUBSTRING(attemptId, 5) AS UNSIGNED)), 0
             ) + 1
             FROM DeliveryAttempt
             WHERE attemptId LIKE 'ATT-%'"
        );

        $nextAttemptId = (int) $attemptIdStmt->fetchColumn();
        $attemptId = 'ATT-' . str_pad($nextAttemptId, 6, '0', STR_PAD_LEFT);

        // Create the delivery attempt.
        $insertAttempt = $pdo->prepare(
            "INSERT INTO DeliveryAttempt
                (
                    attemptId,
                    attemptNumber,
                    attemptedAt,
                    arrivedAt,
                    outcome,
                    deliveryId
                )
             VALUES
                (
                    :attemptId,
                    :attemptNumber,
                    NOW(),
                    NOW(),
                    'ARRIVED',
                    :deliveryId
                )"
        );

        $insertAttempt->execute([
            'attemptId' => $attemptId,
            'attemptNumber' => $attemptNumber,
            'deliveryId' => $deliveryId
        ]);

        // Update the delivery status.
        $updateDelivery = $pdo->prepare(
            "UPDATE Delivery
             SET status = 'ARRIVED'
             WHERE deliveryId = :deliveryId"
        );

        $updateDelivery->execute([
            'deliveryId' => $deliveryId
        ]);

        // Generate the next tracking event ID.
        $eventIdStmt = $pdo->query(
            "SELECT COALESCE(
                MAX(CAST(SUBSTRING(eventId, 5) AS UNSIGNED)), 0
             ) + 1
             FROM TrackingEvent
             WHERE eventId LIKE 'EVT-%'"
        );

        $nextEventId = (int) $eventIdStmt->fetchColumn();
        $eventId = 'EVT-' . str_pad($nextEventId, 6, '0', STR_PAD_LEFT);

        // Record the tracking event.
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
                    'DELIVERY_ARRIVED',
                    NOW(),
                    'Driver marked the delivery as arrived.',
                    'Driver',
                    :driverId,
                    :parcelId,
                    :deliveryId
                )"
        );

        $trackingStmt->execute([
            'eventId' => $eventId,
            'driverId' => $driverId,
            'parcelId' => $delivery['parcelId'],
            'deliveryId' => $deliveryId
        ]);

        $pdo->commit();

        return 'You have successfully marked delivery ' . $deliveryId . ' as arrived.';

    } catch (Throwable $e) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $e;
    }
}

?>
