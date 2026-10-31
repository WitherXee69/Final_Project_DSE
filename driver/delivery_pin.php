<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php?portal=staff');
    exit;
}

require_once __DIR__ . '/../config/database.php';

$userId = (int) $_SESSION['user_id'];

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
    $_SESSION['driver_csrf_token'] =
        bin2hex(random_bytes(32));
}

$csrfToken = $_SESSION['driver_csrf_token'];

$deliveryId = filter_input(
    INPUT_GET,
    'delivery_id',
    FILTER_VALIDATE_INT
);

if (!$deliveryId) {
    redirect_dashboard(
        'Invalid delivery.',
        'error'
    );
}

try {

    /*
     * Confirm that this delivery belongs
     * to the logged-in driver.
     */
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

    $deliveryStatus = strtolower(
        trim($delivery['status'] ?? '')
    );

    if ($deliveryStatus === 'delivered') {
        redirect_dashboard(
            'This delivery has already been completed.'
        );
    }

    if ($deliveryStatus !== 'arrived') {
        redirect_dashboard(
            'The delivery must be marked as arrived before entering the PIN.',
            'error'
        );
    }

    /*
     * Find the latest arrival attempt.
     */
    $attemptStmt = $pdo->prepare(
        "SELECT attemptId,
                attemptNumber
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

    $error = '';

    /*
     * submitDeliveryPIN()
     */
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

        /*
         * Demo PIN:
         * any 4–6 digit numeric value is accepted.
         */
        if (!preg_match('/^[0-9]{4,6}$/', $pin)) {

            $error =
                'Please enter a valid 4–6 digit numeric PIN.';

        } else {

            $pdo->beginTransaction();

            /*
             * Store a hash rather than the plain PIN.
             */
            $pinHash = password_hash(
                $pin,
                PASSWORD_DEFAULT
            );

            /*
             * Create the verification record.
             *
             * For the current project scope this represents
             * the demonstration PIN verification.
             */
            $verificationStmt = $pdo->prepare(
                "INSERT INTO DeliveryVerification
                    (
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
                        :pinHash,
                        NOW(),
                        DATE_ADD(NOW(), INTERVAL 15 MINUTE),
                        NOW(),
                        1,
                        'Verified',
                        :attemptId
                    )"
            );

            $verificationStmt->execute([
                'pinHash' => $pinHash,
                'attemptId' => $attempt['attemptId']
            ]);

            /*
             * Complete the delivery attempt.
             */
            $updateAttempt = $pdo->prepare(
                "UPDATE DeliveryAttempt
                 SET outcome = 'Delivered'
                 WHERE attemptId = :attemptId"
            );

            $updateAttempt->execute([
                'attemptId' => $attempt['attemptId']
            ]);

            /*
             * Mark delivery as Delivered.
             */
            $updateDelivery = $pdo->prepare(
                "UPDATE Delivery
                 SET status = 'Delivered'
                 WHERE deliveryId = :deliveryId"
            );

            $updateDelivery->execute([
                'deliveryId' => $deliveryId
            ]);

            /*
             * Record tracking event.
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
                        'Delivery Completed',
                        NOW(),
                        'Delivery completed after demonstration PIN verification.',
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

            redirect_dashboard(
                'Delivery #' . $deliveryId .
                ' has been successfully completed.'
            );
        }
    }

} catch (PDOException $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log(
        'Delivery PIN error: ' . $e->getMessage()
    );

    $error =
        'The delivery could not be completed right now.';
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

</head>

<body class="driver-body">

<header class="driver-header">

    <div class="container nav">

        <a
            href="driver_dashboard.php"
            class="brand"
        >
            <div class="logo">H</div>

            <div>
                <b>HELMS</b>
                <small>Henowic Inventors (Pvt) Ltd.</small>
            </div>
        </a>

        <a
            class="driver-logout"
            href="driver_dashboard.php"
        >
            Back to dashboard
        </a>

    </div>

</header>

<main class="driver-main container">

    <div class="driver-page-heading">

        <div>

            <span class="driver-eyebrow">
                DELIVERY VERIFICATION
            </span>

            <h1>Submit Delivery PIN</h1>

        </div>

        <div class="driver-date">
            Delivery #<?= h($deliveryId) ?>
        </div>

    </div>


    <section class="driver-pin-card">

        <div class="driver-pin-icon">
            ✓
        </div>

        <h2>Confirm the delivery</h2>

        <p>
            The receiver provides a PIN when the parcel is handed over.
            Enter the PIN below to complete this delivery.
        </p>

        <div class="driver-pin-details">

            <div>
                <span>Receiver</span>
                <strong>
                    <?= h(
                        $delivery['receiverName']
                        ?: 'Not provided'
                    ) ?>
                </strong>
            </div>

            <div>
                <span>Delivery address</span>
                <strong>
                    <?= h(
                        $delivery['deliveryAddress']
                        ?: 'Not specified'
                    ) ?>
                </strong>
            </div>

            <div>
                <span>Parcel</span>
                <strong>
                    #<?= h($delivery['parcelId']) ?>
                </strong>
            </div>

        </div>

        <?php if ($error): ?>

            <div class="driver-action-error">
                <?= h($error) ?>
            </div>

        <?php endif; ?>


        <form
            method="POST"
            class="driver-pin-form"
        >

            <input
                type="hidden"
                name="csrf_token"
                value="<?= h($csrfToken) ?>"
            >

            <label for="pin">
                Receiver PIN
            </label>

            <input
                type="text"
                id="pin"
                name="pin"
                inputmode="numeric"
                pattern="[0-9]{4,6}"
                minlength="4"
                maxlength="6"
                autocomplete="off"
                placeholder="Enter 4–6 digit PIN"
                required
            >

            <small>
                Demonstration mode: any valid 4–6 digit
                numeric PIN will be accepted.
            </small>

            <button
                type="submit"
                class="driver-action-button driver-action-primary"
            >
                Verify PIN &amp; Complete Delivery
            </button>

        </form>

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