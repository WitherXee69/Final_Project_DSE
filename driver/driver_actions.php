<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php?portal=staff');
    exit;
}

require_once __DIR__ . '/../config/database.php';

$userId = (int) $_SESSION['user_id'];

function redirect_dashboard($message, $type = 'success')
{
    $_SESSION['driver_message'] = $message;
    $_SESSION['driver_message_type'] = $type;

    header('Location: driver_dashboard.php');
    exit;
}

function verify_csrf()
{
    if (
        !isset($_POST['csrf_token']) ||
        !isset($_SESSION['driver_csrf_token']) ||
        !hash_equals(
            $_SESSION['driver_csrf_token'],
            $_POST['csrf_token']
        )
    ) {
        http_response_code(403);
        exit('Invalid security token.');
    }
}

function get_driver_manifest_for_pickup($pdo, $pickupId, $driverId)
{
    $stmt = $pdo->prepare(
        "SELECT p.pickupId
         FROM Pickup p
         INNER JOIN DriverManifest dm
            ON dm.driverManifestId = p.driverManifestId
         WHERE p.pickupId = :pickupId
           AND dm.driverId = :driverId
         LIMIT 1"
    );

    $stmt->execute([
        'pickupId' => $pickupId,
        'driverId' => $driverId
    ]);

    return $stmt->fetch();
}

function get_driver_delivery($pdo, $deliveryId, $driverId)
{
    $stmt = $pdo->prepare(
        "SELECT de.deliveryId,
                de.status,
                de.parcelId,
                de.failedAttemptCount
         FROM Delivery de
         INNER JOIN DriverManifest dm
            ON dm.driverManifestId = de.driverManifestId
         WHERE de.deliveryId = :deliveryId
           AND dm.driverId = :driverId
         LIMIT 1"
    );

    $stmt->execute([
        'deliveryId' => $deliveryId,
        'driverId' => $driverId
    ]);

    return $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: driver_dashboard.php');
    exit;
}

verify_csrf();

$action = $_POST['action'] ?? '';

try {

    /*
     * updateAvailability()
     */
    if ($action === 'update_availability') {

        $status = $_POST['status'] ?? '';

        $allowedStatuses = [
            'Available',
            'Unavailable'
        ];

        if (!in_array($status, $allowedStatuses, true)) {
            redirect_dashboard(
                'Invalid availability status.',
                'error'
            );
        }

        $stmt = $pdo->prepare(
            "UPDATE Driver
             SET status = :status
             WHERE userId = :driverId"
        );

        $stmt->execute([
            'status' => $status,
            'driverId' => $userId
        ]);

        redirect_dashboard(
            'Your availability has been updated.'
        );
    }


    /*
     * markPickupSuccessful()
     */
    if ($action === 'pickup_success') {

        $pickupId = filter_input(
            INPUT_POST,
            'pickup_id',
            FILTER_VALIDATE_INT
        );

        if (!$pickupId) {
            redirect_dashboard(
                'Invalid pickup.',
                'error'
            );
        }

        $pickup = get_driver_manifest_for_pickup(
            $pdo,
            $pickupId,
            $userId
        );

        if (!$pickup) {
            http_response_code(403);
            exit('You are not assigned to this pickup.');
        }

        $stmt = $pdo->prepare(
            "UPDATE Pickup
             SET status = 'Completed',
                 failureReason = NULL
             WHERE pickupId = :pickupId"
        );

        $stmt->execute([
            'pickupId' => $pickupId
        ]);

        redirect_dashboard(
            'Pickup #' . $pickupId . ' has been marked as completed.'
        );
    }


    /*
     * markPickupFailed()
     */
    if ($action === 'pickup_failed') {

        $pickupId = filter_input(
            INPUT_POST,
            'pickup_id',
            FILTER_VALIDATE_INT
        );

        $failureReason = trim(
            $_POST['failure_reason'] ?? ''
        );

        if (!$pickupId) {
            redirect_dashboard(
                'Invalid pickup.',
                'error'
            );
        }

        if ($failureReason === '') {
            redirect_dashboard(
                'Please provide a reason for the failed pickup.',
                'error'
            );
        }

        if (strlen($failureReason) > 255) {
            redirect_dashboard(
                'The pickup failure reason is too long.',
                'error'
            );
        }

        $pickup = get_driver_manifest_for_pickup(
            $pdo,
            $pickupId,
            $userId
        );

        if (!$pickup) {
            http_response_code(403);
            exit('You are not assigned to this pickup.');
        }

        $stmt = $pdo->prepare(
            "UPDATE Pickup
             SET status = 'Failed',
                 failureReason = :failureReason
             WHERE pickupId = :pickupId"
        );

        $stmt->execute([
            'failureReason' => $failureReason,
            'pickupId' => $pickupId
        ]);

        redirect_dashboard(
            'Pickup #' . $pickupId . ' has been marked as failed.'
        );
    }


    /*
     * markDeliveryArrived()
     */
    if ($action === 'delivery_arrived') {

        $deliveryId = filter_input(
            INPUT_POST,
            'delivery_id',
            FILTER_VALIDATE_INT
        );

        if (!$deliveryId) {
            redirect_dashboard(
                'Invalid delivery.',
                'error'
            );
        }

        $delivery = get_driver_delivery(
            $pdo,
            $deliveryId,
            $userId
        );

        if (!$delivery) {
            http_response_code(403);
            exit('You are not assigned to this delivery.');
        }

        $currentStatus = strtolower(
            trim($delivery['status'] ?? '')
        );

        if ($currentStatus === 'delivered') {
            redirect_dashboard(
                'This delivery has already been completed.',
                'error'
            );
        }

        if ($currentStatus === 'arrived') {
            header(
                'Location: delivery_pin.php?delivery_id=' .
                urlencode($deliveryId)
            );
            exit;
        }

        $pdo->beginTransaction();

        /*
         * Find the next attempt number.
         */
        $attemptStmt = $pdo->prepare(
            "SELECT COALESCE(MAX(attemptNumber), 0) + 1
             FROM DeliveryAttempt
             WHERE deliveryId = :deliveryId"
        );

        $attemptStmt->execute([
            'deliveryId' => $deliveryId
        ]);

        $attemptNumber = (int) $attemptStmt->fetchColumn();

        /*
         * Create the delivery attempt.
         */
        $insertAttempt = $pdo->prepare(
            "INSERT INTO DeliveryAttempt
                (
                    attemptNumber,
                    attemptedAt,
                    arrivedAt,
                    outcome,
                    deliveryId
                )
             VALUES
                (
                    :attemptNumber,
                    NOW(),
                    NOW(),
                    'Arrived',
                    :deliveryId
                )"
        );

        $insertAttempt->execute([
            'attemptNumber' => $attemptNumber,
            'deliveryId' => $deliveryId
        ]);

        $attemptId = (int) $pdo->lastInsertId();

        /*
         * Mark the delivery as arrived.
         */
        $updateDelivery = $pdo->prepare(
            "UPDATE Delivery
             SET status = 'Arrived'
             WHERE deliveryId = :deliveryId"
        );

        $updateDelivery->execute([
            'deliveryId' => $deliveryId
        ]);

        /*
         * Record the tracking event.
         */
        $trackingStmt = $pdo->prepare(
            "INSERT INTO TrackingEvent
                (
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
                    'Delivery Arrived',
                    NOW(),
                    'Driver marked the delivery as arrived.',
                    'Driver',
                    :driverId,
                    :parcelId,
                    :deliveryId
                )"
        );

        $trackingStmt->execute([
            'driverId' => $userId,
            'parcelId' => $delivery['parcelId'],
            'deliveryId' => $deliveryId
        ]);

        $pdo->commit();

        header(
            'Location: delivery_pin.php?delivery_id=' .
            urlencode($deliveryId)
        );
        exit;
    }


    redirect_dashboard(
        'Unknown driver action.',
        'error'
    );

} catch (PDOException $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log(
        'Driver action error: ' . $e->getMessage()
    );

    redirect_dashboard(
        'The requested action could not be completed.',
        'error'
    );
}
?>