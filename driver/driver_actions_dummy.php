<?php

require_once __DIR__ . '/layout.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: driver_dashboard_dummy.php');
    exit;
}

$data = &$_SESSION['driver_dummy_data'];

$action = $_POST['action'] ?? '';
$id = trim($_POST['id'] ?? '');

switch ($action) {

    /* =====================================
       UPDATE AVAILABILITY
    ===================================== */

    case 'updateAvailability':

        $availability = $_POST['availability'] ?? '';

        if (!in_array($availability, ['Available', 'Unavailable'], true)) {
            dr_set_flash('error', 'Invalid availability selection.');
            break;
        }

        $data['driver']['status'] = $availability;

        dr_set_flash(
            'success',
            'Your availability has been updated to ' . $availability . '.'
        );

        break;


    /* =====================================
       MARK PICKUP SUCCESSFUL
    ===================================== */

    case 'markPickupSuccessful':

        $found = false;

        foreach ($data['pickups'] as &$pickup) {

            if ($pickup['id'] === $id) {

                if (in_array($pickup['status'], ['Completed', 'Failed'], true)) {
                    break;
                }

                $pickup['status'] = 'Completed';
                $pickup['failureReason'] = '';

                $found = true;
                break;
            }
        }

        unset($pickup);

        if ($found) {
            dr_set_flash('success', 'Pickup ' . $id . ' has been marked as completed.');
        } else {
            dr_set_flash('error', 'Pickup not found or already processed.');
        }

        break;


    /* =====================================
       MARK PICKUP FAILED
    ===================================== */

    case 'markPickupFailed':

        $reason = trim($_POST['failureReason'] ?? '');

        if ($reason === '') {
            dr_set_flash('error', 'Please provide a reason for the failed pickup.');
            break;
        }

        $found = false;

        foreach ($data['pickups'] as &$pickup) {

            if ($pickup['id'] === $id) {

                if (in_array($pickup['status'], ['Completed', 'Failed'], true)) {
                    break;
                }

                $pickup['status'] = 'Failed';
                $pickup['failureReason'] = $reason;

                $found = true;
                break;
            }
        }

        unset($pickup);

        if ($found) {
            dr_set_flash('success', 'Pickup ' . $id . ' has been marked as failed.');
        } else {
            dr_set_flash('error', 'Pickup not found or already processed.');
        }

        break;


    /* =====================================
       MARK DELIVERY ARRIVED
    ===================================== */

    case 'markDeliveryArrived':

        $found = false;

        foreach ($data['deliveries'] as &$delivery) {

            if ($delivery['id'] === $id) {

                if (in_array($delivery['status'], ['Delivered', 'Arrived'], true)) {
                    break;
                }

                $delivery['status'] = 'Arrived';
                $delivery['attempts']++;
                $delivery['arrivedAt'] = date('Y-m-d H:i:s');

                $found = true;
                break;
            }
        }

        unset($delivery);

        if ($found) {

            $_SESSION['driver_dummy_pending_delivery'] = $id;

            dr_set_flash(
                'success',
                'Arrival recorded. Please complete the receiver PIN verification.'
            );

            header('Location: delivery_pin_dummy.php?id=' . urlencode($id));
            exit;

        } else {

            dr_set_flash('error', 'Delivery not found or already processed.');

        }

        break;


    /* =====================================
       SUBMIT DEMONSTRATION PIN
    ===================================== */

    case 'submitDeliveryPIN':

        $pin = trim($_POST['pin'] ?? '');
        $deliveryId = trim($_POST['deliveryId'] ?? '');

        $pendingId = $_SESSION['driver_dummy_pending_delivery'] ?? '';

        if ($deliveryId === '' || $deliveryId !== $pendingId) {
            dr_set_flash('error', 'Invalid or expired delivery verification session.');
            header('Location: driver_dashboard_dummy.php');
            exit;
        }

        if (!preg_match('/^[0-9]+$/', $pin)) {
            dr_set_flash('error', 'Please enter a valid numeric PIN.');
            header('Location: delivery_pin_dummy.php?id=' . urlencode($deliveryId));
            exit;
        }

        $found = false;

        foreach ($data['deliveries'] as &$delivery) {

            if ($delivery['id'] === $deliveryId) {

                if ($delivery['status'] !== 'Arrived') {
                    break;
                }

                $delivery['status'] = 'Delivered';

                $found = true;
                break;
            }
        }

        unset($delivery);

        if ($found) {

            unset($_SESSION['driver_dummy_pending_delivery']);

            dr_set_flash(
                'success',
                'PIN accepted. Delivery ' . $deliveryId . ' has been marked as delivered.'
            );

            header('Location: driver_dashboard_dummy.php#deliveries');
            exit;

        } else {

            dr_set_flash('error', 'Unable to complete this delivery.');

        }

        break;


    default:

        dr_set_flash('error', 'Unknown action.');

        break;
}

header('Location: driver_dashboard_dummy.php');
exit;

?>
