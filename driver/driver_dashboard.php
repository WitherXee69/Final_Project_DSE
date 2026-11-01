<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php?portal=staff');
    exit;
}

require_once __DIR__ . '/../config/database.php';

$userId = $_SESSION['user_id'];

function h($value)
{
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

$driverMessage = $_SESSION['driver_message'] ?? null;
$driverMessageType = $_SESSION['driver_message_type'] ?? 'success';

unset($_SESSION['driver_message']);
unset($_SESSION['driver_message_type']);

try {

    // Retrieve the logged-in driver's profile.
    $profileStmt = $pdo->prepare(
        "SELECT u.userId,
                u.username,
                u.role,
                d.licenseNumber,
                d.contactNumber,
                d.status AS driverStatus
         FROM User u
         INNER JOIN Driver d
            ON d.userId = u.userId
         WHERE u.userId = :userId
         LIMIT 1"
    );

    $profileStmt->execute([
        'userId' => $userId
    ]);

    $driver = $profileStmt->fetch();

    if (!$driver) {
        http_response_code(403);
        exit(
            'Access denied. This account is not registered as a driver.'
        );
    }

    // Retrieve the driver's assigned manifests.
    $manifestStmt = $pdo->prepare(
        "SELECT dm.driverManifestId,
                dm.assignedDate,
                dm.schedule,
                dm.status,
                dmf.manifestDate,
                dmf.route,
                dmf.manifestStatus,
                v.registrationNumber,
                v.vehicleType
         FROM DriverManifest dm
         LEFT JOIN DeliveryManifest dmf
            ON dmf.deliveryManifestId = dm.deliveryManifestId
         LEFT JOIN Vehicle v
            ON v.vehicleId = dm.vehicleId
         WHERE dm.driverId = :driverId
         ORDER BY dm.assignedDate DESC,
                  dm.driverManifestId DESC
         LIMIT 8"
    );

    $manifestStmt->execute([
        'driverId' => $userId
    ]);

    $manifests = $manifestStmt->fetchAll();

    // Retrieve assigned pickups.
    $pickupStmt = $pdo->prepare(
        "SELECT p.pickupId,
                p.pickupDate,
                p.preferredTimeWindow,
                p.pickupAddress,
                p.status,
                p.failureReason
         FROM Pickup p
         INNER JOIN DriverManifest dm
            ON dm.driverManifestId = p.driverManifestId
         WHERE dm.driverId = :driverId
         ORDER BY p.pickupDate DESC,
                  p.pickupId DESC
         LIMIT 8"
    );

    $pickupStmt->execute([
        'driverId' => $userId
    ]);

    $pickups = $pickupStmt->fetchAll();

    // Retrieve assigned deliveries.
    $deliveryStmt = $pdo->prepare(
        "SELECT de.deliveryId,
                de.deliveryDate,
                de.deliveryAddress,
                de.status,
                de.direction,
                de.failedAttemptCount,
                p.parcelId,
                p.description AS parcelDescription,
                r.receiverName,
                r.receiverPhone
         FROM Delivery de
         INNER JOIN DriverManifest dm
            ON dm.driverManifestId = de.driverManifestId
         INNER JOIN Parcel p
            ON p.parcelId = de.parcelId
         LEFT JOIN Receiver r
            ON r.receiverId = p.receiverId
         WHERE dm.driverId = :driverId
         ORDER BY de.deliveryDate DESC,
                  de.deliveryId DESC
         LIMIT 8"
    );

    $deliveryStmt->execute([
        'driverId' => $userId
    ]);

    $deliveries = $deliveryStmt->fetchAll();

    // Dashboard summary counts.
    $summaryStmt = $pdo->prepare(
        "SELECT
            (SELECT COUNT(*)
             FROM DriverManifest
             WHERE driverId = :manifestDriver) AS manifestCount,

            (SELECT COUNT(*)
             FROM Pickup p
             INNER JOIN DriverManifest dm
                ON dm.driverManifestId = p.driverManifestId
             WHERE dm.driverId = :pickupDriver) AS pickupCount,

            (SELECT COUNT(*)
             FROM Delivery de
             INNER JOIN DriverManifest dm
                ON dm.driverManifestId = de.driverManifestId
             WHERE dm.driverId = :deliveryDriver) AS deliveryCount,

            (SELECT COUNT(*)
             FROM Delivery de
             INNER JOIN DriverManifest dm
                ON dm.driverManifestId = de.driverManifestId
             WHERE dm.driverId = :completedDriver
             AND LOWER(COALESCE(de.status, ''))
                IN ('delivered', 'completed')) AS completedCount"
    );

    $summaryStmt->execute([
        'manifestDriver' => $userId,
        'pickupDriver' => $userId,
        'deliveryDriver' => $userId,
        'completedDriver' => $userId
    ]);

    $summary = $summaryStmt->fetch();

} catch (PDOException $e) {

    error_log(
        'Driver dashboard error: ' . $e->getMessage()
    );

    http_response_code(500);

    exit(
        'Unable to load the driver dashboard right now. Please try again later.'
    );
}

$driverName = $driver['username'];
$driverStatus = $driver['driverStatus'] ?: 'Not set';

$completedCount = (int) $summary['completedCount'];
$deliveryCount = (int) $summary['deliveryCount'];
$pendingCount = max(
    0,
    $deliveryCount - $completedCount
);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>Driver Dashboard | HELMS</title>

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

            <a
                class="active"
                href="driver_dashboard.php"
            >
                Dashboard
            </a>

            <a href="#manifests">
                My manifests
            </a>

            <a href="#pickups">
                Pickups
            </a>

            <a href="#deliveries">
                Deliveries
            </a>

        </nav>

        <div class="driver-account">

            <span class="driver-avatar">
                <?= h(
                    strtoupper(
                        substr($driverName, 0, 1)
                    )
                ) ?>
            </span>

            <span class="driver-account-name">
                <?= h($driverName) ?>
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

    <?php if ($driverMessage): ?>

        <div
            class="driver-action-message <?= h($driverMessageType) ?>"
        >
            <?= h($driverMessage) ?>
        </div>

    <?php endif; ?>


    <!-- Welcome section -->

    <section class="driver-welcome">

        <div>

            <span class="driver-eyebrow">
                DRIVER WORKSPACE
            </span>

            <h1>
                Good day, <?= h($driverName) ?>.
            </h1>

            <p>
                Here is an overview of your assigned manifests,
                pickups and deliveries.
            </p>

        </div>


        <div class="driver-status">

            <span class="status-indicator"></span>

            <span>
                Driver status:
            </span>

            <strong>
                <?= h($driverStatus) ?>
            </strong>

        </div>

    </section>


    <!-- Availability -->

    <section class="driver-availability-card">

        <div>

            <span class="section-kicker">
                DRIVER AVAILABILITY
            </span>

            <h2>
                Update your availability
            </h2>

            <p>
                Let the transport team know whether you are
                currently available for assignments.
            </p>

        </div>

        <form
            method="POST"
            action="driver_actions.php"
            class="driver-availability-form"
        >

            <input
                type="hidden"
                name="action"
                value="update_availability"
            >

            <select
                name="status"
                required
            >

                <option
                    value="Available"
                    <?= $driverStatus === 'Available'
                        ? 'selected'
                        : '' ?>
                >
                    Available
                </option>

                <option
                    value="Unavailable"
                    <?= $driverStatus === 'Unavailable'
                        ? 'selected'
                        : '' ?>
                >
                    Unavailable
                </option>

            </select>

            <button
                type="submit"
                class="driver-action-button driver-action-primary"
            >
                Update status
            </button>

        </form>

    </section>


    <!-- Driver profile -->

    <section class="driver-profile-strip">

        <div>

            <span class="profile-label">
                Driver ID
            </span>

            <strong>
                #<?= h($driver['userId']) ?>
            </strong>

        </div>


        <div>

            <span class="profile-label">
                License number
            </span>

            <strong>
                <?= h($driver['licenseNumber']) ?>
            </strong>

        </div>


        <div>

            <span class="profile-label">
                Contact number
            </span>

            <strong>
                <?= h(
                    $driver['contactNumber']
                    ?: 'Not provided'
                ) ?>
            </strong>

        </div>

    </section>


    <!-- Summary cards -->

    <section class="driver-stats">

        <article class="driver-stat-card">

            <div class="stat-icon navy-icon">
                ▤
            </div>

            <div>

                <span>
                    Total manifests
                </span>

                <strong>
                    <?= (int) $summary['manifestCount'] ?>
                </strong>

                <small>
                    Assigned to you
                </small>

            </div>

        </article>


        <article class="driver-stat-card">

            <div class="stat-icon amber-icon">
                ↗
            </div>

            <div>

                <span>
                    Pickup assignments
                </span>

                <strong>
                    <?= (int) $summary['pickupCount'] ?>
                </strong>

                <small>
                    Across your manifests
                </small>

            </div>

        </article>


        <article class="driver-stat-card">

            <div class="stat-icon blue-icon">
                ▣
            </div>

            <div>

                <span>
                    Total deliveries
                </span>

                <strong>
                    <?= $deliveryCount ?>
                </strong>

                <small>
                    Assigned to you
                </small>

            </div>

        </article>


        <article class="driver-stat-card">

            <div class="stat-icon green-icon">
                ✓
            </div>

            <div>

                <span>
                    Completed deliveries
                </span>

                <strong>
                    <?= $completedCount ?>
                </strong>

                <small>
                    <?= $pendingCount ?>
                    not marked delivered
                </small>

            </div>

        </article>

    </section>


    <!-- Manifests -->

    <section
        class="driver-section"
        id="manifests"
    >

        <div class="driver-section-heading">

            <div>

                <span class="section-kicker">
                    YOUR ASSIGNMENTS
                </span>

                <h2>
                    My manifests
                </h2>

            </div>

            <span class="section-count">
                <?= count($manifests) ?> shown
            </span>

        </div>


        <?php if ($manifests): ?>

            <div class="driver-table-wrap">

                <table class="driver-table">

                    <thead>

                        <tr>

                            <th>
                                Manifest
                            </th>

                            <th>
                                Assigned date
                            </th>

                            <th>
                                Route
                            </th>

                            <th>
                                Vehicle
                            </th>

                            <th>
                                Schedule
                            </th>

                            <th>
                                Status
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php foreach ($manifests as $manifest): ?>

                        <tr>

                            <td>

                                <strong>
                                    #<?= h(
                                        $manifest['driverManifestId']
                                    ) ?>
                                </strong>

                            </td>


                            <td>
                                <?= h(
                                    $manifest['assignedDate']
                                ) ?>
                            </td>


                            <td>
                                <?= h(
                                    $manifest['route']
                                    ?: 'Not specified'
                                ) ?>
                            </td>


                            <td>

                                <?= h(
                                    $manifest['registrationNumber']
                                    ?: 'Not assigned'
                                ) ?>

                                <?php if (!empty($manifest['vehicleType'])): ?>

                                    <small class="table-subtext">
                                        <?= h(
                                            $manifest['vehicleType']
                                        ) ?>
                                    </small>

                                <?php endif; ?>

                            </td>


                            <td>
                                <?= h(
                                    $manifest['schedule']
                                    ?: 'Not specified'
                                ) ?>
                            </td>


                            <td>

                                <span class="status-pill">
                                    <?= h(
                                        $manifest['status']
                                        ?: 'Pending'
                                    ) ?>
                                </span>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        <?php else: ?>

            <div class="driver-empty">

                <span class="empty-icon">
                    ▤
                </span>

                <strong>
                    No manifests assigned yet
                </strong>

                <p>
                    Your assigned manifests will appear here
                    once the transport team assigns them.
                </p>

            </div>

        <?php endif; ?>

    </section>


    <!-- Pickups -->

    <section
        class="driver-section"
        id="pickups"
    >

        <div class="driver-section-heading">

            <div>

                <span class="section-kicker">
                    COLLECTIONS
                </span>

                <h2>
                    Assigned pickups
                </h2>

            </div>

            <span class="section-count">
                <?= count($pickups) ?> shown
            </span>

        </div>


        <?php if ($pickups): ?>

            <div class="driver-table-wrap">

                <table class="driver-table">

                    <thead>

                        <tr>

                            <th>
                                Pickup ID
                            </th>

                            <th>
                                Pickup date
                            </th>

                            <th>
                                Time window
                            </th>

                            <th>
                                Collection address
                            </th>

                            <th>
                                Status
                            </th>

                            <th>
                                Action
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                    <?php foreach ($pickups as $pickup): ?>

                        <tr>

                            <td>

                                <strong>
                                    #<?= h(
                                        $pickup['pickupId']
                                    ) ?>
                                </strong>

                            </td>


                            <td>
                                <?= h(
                                    $pickup['pickupDate']
                                    ?: 'Not scheduled'
                                ) ?>
                            </td>


                            <td>
                                <?= h(
                                    $pickup['preferredTimeWindow']
                                    ?: 'Not specified'
                                ) ?>
                            </td>


                            <td>
                                <?= h(
                                    $pickup['pickupAddress']
                                ) ?>
                            </td>


                            <td>

                                <span class="status-pill">
                                    <?= h(
                                        $pickup['status']
                                        ?: 'Pending'
                                    ) ?>
                                </span>

                                <?php if (
                                    !empty(
                                        $pickup['failureReason']
                                    )
                                ): ?>

                                    <small class="table-subtext">
                                        <?= h(
                                            $pickup['failureReason']
                                        ) ?>
                                    </small>

                                <?php endif; ?>

                            </td>


                            <td>

                                <?php
                                $pickupStatus = strtolower(
                                    trim(
                                        $pickup['status'] ?? ''
                                    )
                                );
                                ?>

                                <?php if (
                                    $pickupStatus !== 'completed'
                                    && $pickupStatus !== 'successful'
                                    && $pickupStatus !== 'failed'
                                ): ?>

                                    <div class="driver-action-group">

                                        <form
                                            method="POST"
                                            action="driver_actions.php"
                                        >

                                            <input
                                                type="hidden"
                                                name="action"
                                                value="pickup_success"
                                            >

                                            <input
                                                type="hidden"
                                                name="pickup_id"
                                                value="<?= h(
                                                    $pickup['pickupId']
                                                ) ?>"
                                            >

                                            <button
                                                type="submit"
                                                class="driver-action-button driver-action-success"
                                            >
                                                Successful
                                            </button>

                                        </form>


                                        <form
                                            method="POST"
                                            action="driver_actions.php"
                                            class="driver-failed-form"
                                        >

                                            <input
                                                type="hidden"
                                                name="action"
                                                value="pickup_failed"
                                            >

                                            <input
                                                type="hidden"
                                                name="pickup_id"
                                                value="<?= h(
                                                    $pickup['pickupId']
                                                ) ?>"
                                            >

                                            <input
                                                type="text"
                                                name="failure_reason"
                                                maxlength="255"
                                                placeholder="Failure reason"
                                                required
                                            >

                                            <button
                                                type="submit"
                                                class="driver-action-button driver-action-danger"
                                            >
                                                Failed
                                            </button>

                                        </form>

                                    </div>

                                <?php else: ?>

                                    <span class="action-complete">
                                        No action needed
                                    </span>

                                <?php endif; ?>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        <?php else: ?>

            <div class="driver-empty">

                <span class="empty-icon">
                    ↗
                </span>

                <strong>
                    No pickup assignments
                </strong>

                <p>
                    Pickups assigned to your manifests will be listed here.
                </p>

            </div>

        <?php endif; ?>

    </section>


    <!-- Deliveries -->

    <section
        class="driver-section"
        id="deliveries"
    >

        <div class="driver-section-heading">

            <div>

                <span class="section-kicker">
                    DELIVERY WORK
                </span>

                <h2>
                    Assigned deliveries
                </h2>

            </div>

            <span class="section-count">
                <?= count($deliveries) ?> shown
            </span>

        </div>


        <?php if ($deliveries): ?>

            <div class="driver-table-wrap">

                <table class="driver-table">

                    <thead>

                        <tr>

                            <th>
                                Delivery
                            </th>

                            <th>
                                Parcel
                            </th>

                            <th>
                                Receiver
                            </th>

                            <th>
                                Delivery address
                            </th>

                            <th>
                                Date
                            </th>

                            <th>
                                Status
                            </th>

                            <th>
                                Action
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                    <?php foreach ($deliveries as $delivery): ?>

                        <tr>

                            <td>

                                <strong>
                                    #<?= h(
                                        $delivery['deliveryId']
                                    ) ?>
                                </strong>

                                <small class="table-subtext">
                                    <?= h(
                                        $delivery['direction']
                                        ?: 'Delivery'
                                    ) ?>
                                </small>

                            </td>


                            <td>

                                <strong>
                                    #<?= h(
                                        $delivery['parcelId']
                                    ) ?>
                                </strong>

                                <small class="table-subtext">
                                    <?= h(
                                        $delivery['parcelDescription']
                                        ?: 'No description'
                                    ) ?>
                                </small>

                            </td>


                            <td>

                                <?= h(
                                    $delivery['receiverName']
                                    ?: 'Not provided'
                                ) ?>

                                <?php if (
                                    !empty(
                                        $delivery['receiverPhone']
                                    )
                                ): ?>

                                    <small class="table-subtext">
                                        <?= h(
                                            $delivery['receiverPhone']
                                        ) ?>
                                    </small>

                                <?php endif; ?>

                            </td>


                            <td>
                                <?= h(
                                    $delivery['deliveryAddress']
                                    ?: 'Not specified'
                                ) ?>
                            </td>


                            <td>
                                <?= h(
                                    $delivery['deliveryDate']
                                    ?: 'Not scheduled'
                                ) ?>
                            </td>


                            <td>

                                <span class="status-pill">
                                    <?= h(
                                        $delivery['status']
                                        ?: 'Pending'
                                    ) ?>
                                </span>

                            </td>


                            <td>

                                <?php
                                $deliveryStatus = strtolower(
                                    trim(
                                        $delivery['status'] ?? ''
                                    )
                                );
                                ?>


                                <?php if (
                                    $deliveryStatus === 'delivered'
                                    || $deliveryStatus === 'completed'
                                ): ?>

                                    <span class="action-complete">
                                        Completed
                                    </span>


                                <?php elseif (
                                    $deliveryStatus === 'arrived'
                                ): ?>

                                    <a
                                        href="delivery_pin.php?delivery_id=<?= h(
                                            $delivery['deliveryId']
                                        ) ?>"
                                        class="driver-action-button driver-action-primary"
                                    >
                                        Enter PIN
                                    </a>


                                <?php else: ?>

                                    <form
                                        method="POST"
                                        action="driver_actions.php"
                                    >

                                        <input
                                            type="hidden"
                                            name="action"
                                            value="delivery_arrived"
                                        >

                                        <input
                                            type="hidden"
                                            name="delivery_id"
                                            value="<?= h(
                                                $delivery['deliveryId']
                                            ) ?>"
                                        >

                                        <button
                                            type="submit"
                                            class="driver-action-button driver-action-primary"
                                        >
                                            Mark Arrived
                                        </button>

                                    </form>

                                <?php endif; ?>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        <?php else: ?>

            <div class="driver-empty">

                <span class="empty-icon">
                    ▣
                </span>

                <strong>
                    No delivery assignments
                </strong>

                <p>
                    Deliveries assigned to your manifests will appear here.
                </p>

            </div>

        <?php endif; ?>

    </section>


    <p class="driver-footnote">
        Update your availability, record pickup outcomes,
        mark deliveries as arrived and complete deliveries
        using the receiver PIN.
    </p>

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