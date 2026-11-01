<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php?portal=staff');
    exit;
}

require_once __DIR__ . '/../config/database.php';

// Keep user IDs as strings because the database uses VARCHAR IDs.
$driverId = (string) $_SESSION['user_id'];

// Load the driver action functions.
require_once __DIR__ . '/actions/update_availability.php';
require_once __DIR__ . '/actions/view_driver_manifest.php';
require_once __DIR__ . '/actions/mark_pickup_successful.php';
require_once __DIR__ . '/actions/mark_pickup_failed.php';
require_once __DIR__ . '/actions/mark_delivery_arrived.php';
require_once __DIR__ . '/actions/submit_pin.php';

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

function get_posted_id($field)
{
    $id = trim($_POST[$field] ?? '');

    if ($id === '' || strlen($id) > 20) {
        return false;
    }

    return $id;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: driver_dashboard.php');
    exit;
}

verify_csrf();

$action = $_POST['action'] ?? '';

try {

    // Update driver availability.
    if ($action === 'update_availability') {

        $status = $_POST['status'] ?? '';

        $message = updateAvailability(
            $pdo,
            $driverId,
            $status
        );

        redirect_dashboard($message);
    }

    // Mark a pickup as successfully completed.
    if ($action === 'pickup_success') {

        $pickupId = get_posted_id('pickup_id');

        if ($pickupId === false) {
            redirect_dashboard('Invalid pickup ID.', 'error');
        }

        $message = markPickupSuccessful(
            $pdo,
            $pickupId,
            $driverId
        );

        redirect_dashboard($message);
    }

    // Mark a pickup as failed.
    if ($action === 'pickup_failed') {

        $pickupId = get_posted_id('pickup_id');

        if ($pickupId === false) {
            redirect_dashboard('Invalid pickup ID.', 'error');
        }

        $failureReason = trim(
            $_POST['failure_reason'] ?? ''
        );

        $message = markPickupFailed(
            $pdo,
            $pickupId,
            $driverId,
            $failureReason
        );

        redirect_dashboard($message);
    }

    // Mark a delivery as arrived and open the PIN page.
    if ($action === 'delivery_arrived') {

        $deliveryId = get_posted_id('delivery_id');

        if ($deliveryId === false) {
            redirect_dashboard('Invalid delivery ID.', 'error');
        }

        $message = markDeliveryArrived(
            $pdo,
            $deliveryId,
            $driverId
        );

        $_SESSION['driver_message'] = $message;
        $_SESSION['driver_message_type'] = 'success';

        header(
            'Location: delivery_pin.php?delivery_id=' .
            urlencode($deliveryId)
        );
        exit;
    }

    redirect_dashboard('Unknown driver action.', 'error');

} catch (InvalidArgumentException | RuntimeException $e) {

    redirect_dashboard($e->getMessage(), 'error');

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('Driver action error: ' . $e->getMessage());

    redirect_dashboard(
        'The requested action could not be completed.',
        'error'
    );
}

?>
