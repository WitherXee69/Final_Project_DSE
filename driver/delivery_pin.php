
<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php?portal=staff');
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/actions/submit_pin.php';

$userId = (string) $_SESSION['user_id'];

function h($value)
{
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

function redirect_dashboard($message, $type = 'success')
{
    $_SESSION['driver_message'] = $message;
    $_SESSION['driver_message_type'] = $type;

    header('Location: driver_dashboard.php');
    exit;
}

if (!isset($_SESSION['driver_csrf_token'])) {
    $_SESSION['driver_csrf_token'] = bin2hex(random_bytes(32));
}

$csrfToken = $_SESSION['driver_csrf_token'];

$deliveryId = trim(
    $_POST['delivery_id'] ?? $_GET['delivery_id'] ?? ''
);

if ($deliveryId === '' || strlen($deliveryId) > 20) {
    redirect_dashboard('Invalid delivery.', 'error');
}

$error = '';

try {

    // Retrieve the delivery and confirm driver assignment.
    $deliveryStmt = $pdo->prepare(
        "SELECT de.deliveryId,
                de.status,
                de.deliveryAddress,
                de.parcelId,
                p.description AS parcelDescription,
                r.receiverName
         FROM Delivery de
         INNER JOIN DriverManifest dm
            ON dm.driverManifestId = de.driverManifestId
         INNER JOIN Parcel p
            ON p.parcelId = de.parcelId
         LEFT JOIN Receiver r
            ON r.receiverId = p.receiverId
         WHERE de.deliveryId = :deliveryId
           AND dm.driverId = :driverId
         LIMIT 1"
    );

    $deliveryStmt->execute([
        'deliveryId' => $deliveryId,
        'driverId' => $userId
    ]);

    $delivery = $deliveryStmt->fetch();

    if (!$delivery) {
        http_response_code(403);
        exit('You are not assigned to this delivery.');
    }

    $deliveryStatus = strtoupper(
        trim($delivery['status'] ?? '')
    );

    if ($deliveryStatus === 'DELIVERED') {
        redirect_dashboard(
            'This delivery has already been completed.'
        );
    }

    if ($deliveryStatus === 'FAILED') {
        redirect_dashboard(
            'This delivery attempt has already failed.',
            'error'
        );
    }

    if ($deliveryStatus !== 'ARRIVED') {
        redirect_dashboard(
            'The delivery must be marked as arrived before entering the PIN.',
            'error'
        );
    }

    // Confirm that an arrival attempt exists.
    $attemptStmt = $pdo->prepare(
        "SELECT attemptId
         FROM DeliveryAttempt
         WHERE deliveryId = :deliveryId
         ORDER BY attemptNumber DESC
         LIMIT 1"
    );

    $attemptStmt->execute([
        'deliveryId' => $deliveryId
    ]);

    $attempt = $attemptStmt->fetch();

    if (!$attempt) {
        redirect_dashboard(
            'No delivery attempt was found for this arrival.',
            'error'
        );
    }

    // Process the submitted PIN.
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        if (
            !isset($_POST['csrf_token']) ||
            !hash_equals(
                $_SESSION['driver_csrf_token'],
                $_POST['csrf_token']
            )
        ) {
            http_response_code(403);
            exit('Invalid security token.');
        }

        $pin = trim($_POST['pin'] ?? '');

        try {

            $message = submitDeliveryPIN(
                $pdo,
                $deliveryId,
                $userId,
                $pin
            );

            // Keep the page open after the first or second failure.
            if (strpos($message, 'PIN failed.') === 0) {
                $error = $message;
            } else {

                $type = (
                    strpos($message, 'unsuccessful') !== false
                ) ? 'error' : 'success';

                redirect_dashboard($message, $type);
            }

        } catch (InvalidArgumentException | RuntimeException $e) {

            $error = $e->getMessage();

        }
    }

} catch (PDOException $e) {

    error_log('Delivery PIN page error: ' . $e->getMessage());

    redirect_dashboard(
        'The delivery information could not be loaded.',
        'error'
    );
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>Delivery Verification | HELMS</title>

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

    <link
        rel="stylesheet"
        href="../assets/css/driver-dashboard.css"
    >

    <link 
        rel="stylesheet" href="../assets/css/driver-dashboard.css"
    >

    <link
        rel="stylesheet" href="../assets/css/delivery-pin.css"
    >

</head>

<body class="driver-dashboard-page">

<header class="driver-header">

    <div class="container driver-nav">

        <a
            href="../index.php"
            class="brand"
        >

            <div class="logo">H</div>

            <div>
                <b>HELMS</b>
                <small>Henowic Inventors (Pvt) Ltd.</small>
            </div>

        </a>

        <nav class="driver-menu">

            <a href="driver_dashboard.php">
                Dashboard
            </a>

            <a href="driver_dashboard.php#manifests">
                My manifests
            </a>

            <a href="driver_dashboard.php#pickups">
                Pickups
            </a>

            <a href="driver_dashboard.php#deliveries">
                Deliveries
            </a>

        </nav>

        <div class="driver-account">

            <span class="driver-avatar">
                <?= h(strtoupper(substr($userId, 0, 1))) ?>
            </span>

            <span class="driver-account-name">
                <?= h($userId) ?>
            </span>

            <a
                class="driver-logout"
                href="../logout.php"
            >
                Logout
            </a>

        </div>

    </div>

</header>


<main class="container driver-main">

    <div class="pin-page-heading">

        <div>

            <span class="driver-eyebrow">
                DELIVERY VERIFICATION
            </span>

            <h1>Submit Delivery PIN</h1>

            <p>
                Verify the receiver's PIN to complete the delivery.
            </p>

        </div>

        <div class="pin-delivery-reference">
            DELIVERY #<?= h($deliveryId) ?>
        </div>

    </div>


    <section class="pin-card">

        <div class="pin-card-heading">

            <div class="pin-icon">
                ✓
            </div>

            <div>

                <h2>Confirm parcel handover</h2>

                <p>
                    Ask the receiver for their PIN and enter it below
                    to confirm successful delivery.
                </p>

            </div>

        </div>


        <div class="pin-details">

            <div class="pin-detail">

                <span>Receiver</span>

                <strong>
                    <?= h(
                        $delivery['receiverName'] ?: 'Not provided'
                    ) ?>
                </strong>

            </div>


            <div class="pin-detail">

                <span>Parcel ID</span>

                <strong>
                    <?= h($delivery['parcelId']) ?>
                </strong>

            </div>


            <div class="pin-detail">

                <span>Delivery address</span>

                <strong>
                    <?= h(
                        $delivery['deliveryAddress'] ?: 'Not specified'
                    ) ?>
                </strong>

            </div>

        </div>


        <?php if ($error): ?>

            <div class="driver-action-message error pin-feedback">
                <?= h($error) ?>
            </div>

        <?php endif; ?>


        <form
            method="POST"
            class="pin-form"
        >

            <input
                type="hidden"
                name="csrf_token"
                value="<?= h($csrfToken) ?>"
            >

            <input
                type="hidden"
                name="delivery_id"
                value="<?= h($deliveryId) ?>"
            >

            <label for="pin">
                Receiver PIN
            </label>

            <input
                type="text"
                id="pin"
                name="pin"
                inputmode="numeric"
                pattern="[0-9]{3,6}"
                minlength="3"
                maxlength="6"
                autocomplete="off"
                placeholder="Enter receiver PIN"
                required
                autofocus
            >

            <p class="pin-help">
                Enter the PIN provided by the receiver.
                You have a maximum of three attempts.
            </p>

            <button
                type="submit"
                class="driver-action-button driver-action-primary"
            >
                Verify PIN and complete delivery
            </button>

        </form>


        <a
            href="driver_dashboard.php"
            class="pin-back-link"
        >
            &larr; Back to dashboard
        </a>

    </section>

</main>


<footer class="driver-footer">

    <div class="container">

        <span>
            &copy; <?= date('Y') ?>
            Henowic Inventors (Pvt) Ltd.
        </span>

        <span>
            HELMS &middot; Driver Workspace
        </span>

    </div>

</footer>

</body>
</html>
