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

function driver_date($value)
{
    if (empty($value)) {
        return 'Not scheduled';
    }

    $timestamp = strtotime($value);

    return $timestamp ? date('d M Y', $timestamp) : $value;
}

function driver_status_class($status)
{
    $status = strtoupper(trim((string) $status));

    $classes = [
        'AVAILABLE' => 'status-success',
        'ON_DUTY' => 'status-info',
        'OFF_DUTY' => 'status-muted',
        'COMPLETED' => 'status-success',
        'DELIVERED' => 'status-success',
        'ARRIVED' => 'status-info',
        'IN_PROGRESS' => 'status-info',
        'IN_TRANSIT' => 'status-info',
        'SCHEDULED' => 'status-warning',
        'PENDING' => 'status-warning',
        'FAILED' => 'status-danger',
        'CANCELLED' => 'status-muted'
    ];

    return $classes[$status] ?? 'status-muted';
}

/*
|--------------------------------------------------------------------------
| CSRF protection
|--------------------------------------------------------------------------
*/

if (empty($_SESSION['driver_csrf_token'])) {
    $_SESSION['driver_csrf_token'] = bin2hex(random_bytes(32));
}

$csrfToken = $_SESSION['driver_csrf_token'];

/*
|--------------------------------------------------------------------------
| Flash messages
|--------------------------------------------------------------------------
*/

$driverMessage = $_SESSION['driver_message'] ?? null;
$driverMessageType = $_SESSION['driver_message_type'] ?? 'success';

unset($_SESSION['driver_message']);
unset($_SESSION['driver_message_type']);

if (!in_array($driverMessageType, ['success', 'error'], true)) {
    $driverMessageType = 'success';
}

/*
|--------------------------------------------------------------------------
| Retrieve driver information and assignments
|--------------------------------------------------------------------------
*/

try {

    /*
     * Driver profile
     */

    $profileStmt = $pdo->prepare(
        "SELECT
            u.userId,
            u.username,
            u.role,
            d.licenseNumber,
            d.contactNumber,
            d.status AS driverStatus
         FROM User u
         INNER JOIN Driver d
            ON d.userId = u.userId
         WHERE u.userId = :userId
           AND LOWER(u.role) = 'driver'
         LIMIT 1"
    );

    $profileStmt->execute([
        'userId' => $userId
    ]);

    $driver = $profileStmt->fetch(PDO::FETCH_ASSOC);

    if (!$driver) {
        http_response_code(403);
        exit('Access denied. This account is not registered as a driver.');
    }

    /*
     * Driver manifests
     */

    $manifestStmt = $pdo->prepare(
        "SELECT
            dm.driverManifestId,
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
                  dm.driverManifestId DESC"
    );

    $manifestStmt->execute([
        'driverId' => $userId
    ]);

    $manifests = $manifestStmt->fetchAll(PDO::FETCH_ASSOC);

    /*
     * Assigned pickups
     */

    $pickupStmt = $pdo->prepare(
        "SELECT
            p.pickupId,
            p.pickupDate,
            p.preferredTimeWindow,
            p.pickupAddress,
            p.status,
            p.failureReason,
            p.driverManifestId
         FROM Pickup p
         INNER JOIN DriverManifest dm
            ON dm.driverManifestId = p.driverManifestId
         WHERE dm.driverId = :driverId
         ORDER BY p.pickupDate ASC,
                  p.pickupId DESC"
    );

    $pickupStmt->execute([
        'driverId' => $userId
    ]);

    $pickups = $pickupStmt->fetchAll(PDO::FETCH_ASSOC);

    /*
     * Assigned deliveries
     */

    $deliveryStmt = $pdo->prepare(
        "SELECT
            de.deliveryId,
            de.deliveryDate,
            de.deliveryAddress,
            de.status,
            de.direction,
            de.failedAttemptCount,
            de.parcelId,
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
         ORDER BY de.deliveryDate ASC,
                  de.deliveryId DESC"
    );

    $deliveryStmt->execute([
        'driverId' => $userId
    ]);

    $deliveries = $deliveryStmt->fetchAll(PDO::FETCH_ASSOC);

    /*
     * Dashboard statistics
     */

    $summaryStmt = $pdo->prepare(
        "SELECT
            (
                SELECT COUNT(*)
                FROM DriverManifest
                WHERE driverId = :manifestDriver
            ) AS manifestCount,

            (
                SELECT COUNT(*)
                FROM Pickup p
                INNER JOIN DriverManifest dm
                    ON dm.driverManifestId = p.driverManifestId
                WHERE dm.driverId = :pickupDriver
            ) AS pickupCount,

            (
                SELECT COUNT(*)
                FROM Delivery de
                INNER JOIN DriverManifest dm
                    ON dm.driverManifestId = de.driverManifestId
                WHERE dm.driverId = :deliveryDriver
            ) AS deliveryCount,

            (
                SELECT COUNT(*)
                FROM Delivery de
                INNER JOIN DriverManifest dm
                    ON dm.driverManifestId = de.driverManifestId
                WHERE dm.driverId = :completedDriver
                  AND UPPER(COALESCE(de.status, '')) = 'DELIVERED'
            ) AS completedCount"
    );

    $summaryStmt->execute([
        'manifestDriver' => $userId,
        'pickupDriver' => $userId,
        'deliveryDriver' => $userId,
        'completedDriver' => $userId
    ]);

    $summary = $summaryStmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    error_log('Driver dashboard error: ' . $e->getMessage());

    http_response_code(500);

    exit(
        'Unable to load the driver dashboard right now. Please try again later.'
    );
}

/*
|--------------------------------------------------------------------------
| Dashboard values
|--------------------------------------------------------------------------
*/

$driverName = $driver['username'];
$driverStatus = strtoupper($driver['driverStatus'] ?? 'OFF_DUTY');

$manifestCount = (int) $summary['manifestCount'];
$pickupCount = (int) $summary['pickupCount'];
$deliveryCount = (int) $summary['deliveryCount'];
$completedCount = (int) $summary['completedCount'];

$pendingCount = max(0, $deliveryCount - $completedCount);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Driver Dashboard | HELMS</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="../assets/css/style.css">

    <link rel="stylesheet" href="../assets/css/driver-dashboard.css">

</head>

<body class="driver-dashboard-page">

<header class="driver-header">

    <div class="container driver-nav">

        <a href="../index.php" class="brand">

            <div class="logo">H</div>

            <div>
                <b>HELMS</b>
                <small>Henowic Inventors (Pvt) Ltd.</small>
            </div>

        </a>

        <nav class="driver-menu">

            <a class="active" href="driver_dashboard.php">
                Dashboard
            </a>

            <a href="#manifests">
                Manifests
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
                <?= h(strtoupper(substr($driverName, 0, 1))) ?>
            </span>

            <span class="driver-account-name">
                <?= h($driverName) ?>
            </span>

            <a href="../logout.php" class="driver-logout">
                Logout
            </a>

        </div>

    </div>

</header>


<main class="container driver-main">

    <?php if ($driverMessage): ?>

        <div class="driver-message <?= h($driverMessageType) ?>">
            <?= h($driverMessage) ?>
        </div>

    <?php endif; ?>


    <!-- Page heading -->

    <section class="driver-page-heading">

        <div>

            <span class="page-eyebrow">DRIVER PORTAL</span>

            <h1>
                Welcome back, <?= h($driverName) ?>
            </h1>

            <p>
                Manage your assignments, pickup requests and deliveries.
            </p>

        </div>

        <div class="driver-current-status">

            <span class="status-dot <?= h(driver_status_class($driverStatus)) ?>"></span>

            <div>
                <small>YOUR CURRENT STATUS</small>
                <strong><?= h($driverStatus) ?></strong>
            </div>

        </div>

    </section>


    <!-- Availability -->

    <section class="driver-availability">

        <div class="availability-information">

            <div class="availability-icon">
                <span>↗</span>
            </div>

            <div>

                <h2>Driver availability</h2>

                <p>
                    Update your availability so the transport team
                    knows whether you can receive assignments.
                </p>

            </div>

        </div>

        <form
            method="POST"
            action="driver_actions.php"
            class="availability-form"
        >

            <input type="hidden" name="csrf_token" value="<?= h($csrfToken) ?>">

            <input type="hidden" name="action" value="update_availability">

            <select name="status" required>

                <option value="AVAILABLE"
                    <?= $driverStatus === 'AVAILABLE' ? 'selected' : '' ?>>
                    Available
                </option>

                <option value="ON_DUTY"
                    <?= $driverStatus === 'ON_DUTY' ? 'selected' : '' ?>>
                    On duty
                </option>

                <option value="OFF_DUTY"
                    <?= $driverStatus === 'OFF_DUTY' ? 'selected' : '' ?>>
                    Off duty
                </option>

            </select>

            <button type="submit" class="btn btn-primary">
                Update status
            </button>

        </form>

    </section>


    <!-- Driver information -->

    <section class="driver-profile">

        <div class="profile-heading">
            <h2>Driver information</h2>
            <span>YOUR PROFILE</span>
        </div>

        <div class="profile-details">

            <div class="profile-item">

                <span>Driver ID</span>

                <strong><?= h($driver['userId']) ?></strong>

            </div>

            <div class="profile-item">

                <span>Username</span>

                <strong><?= h($driverName) ?></strong>

            </div>

            <div class="profile-item">

                <span>License number</span>

                <strong><?= h($driver['licenseNumber']) ?></strong>

            </div>

            <div class="profile-item">

                <span>Contact number</span>

                <strong>
                    <?= h($driver['contactNumber'] ?: 'Not provided') ?>
                </strong>

            </div>

        </div>

    </section>


    <!-- Statistics -->

    <section class="driver-statistics">

        <article class="driver-stat-card">

            <div class="stat-icon stat-navy">▤</div>

            <div>
                <span>Total manifests</span>
                <strong><?= $manifestCount ?></strong>
                <small>Assigned to you</small>
            </div>

        </article>

        <article class="driver-stat-card">

            <div class="stat-icon stat-amber">↗</div>

            <div>
                <span>Pickup assignments</span>
                <strong><?= $pickupCount ?></strong>
                <small>Across your manifests</small>
            </div>

        </article>

        <article class="driver-stat-card">

            <div class="stat-icon stat-blue">▣</div>

            <div>
                <span>Total deliveries</span>
                <strong><?= $deliveryCount ?></strong>
                <small>Assigned to you</small>
            </div>

        </article>

        <article class="driver-stat-card">

            <div class="stat-icon stat-green">✓</div>

            <div>
                <span>Completed deliveries</span>
                <strong><?= $completedCount ?></strong>
                <small><?= $pendingCount ?> remaining</small>
            </div>

        </article>

    </section>


    <!-- Manifests -->

    <section class="driver-section" id="manifests">

        <div class="section-heading">

            <div>
                <span class="section-eyebrow">YOUR ASSIGNMENTS</span>
                <h2>My manifests</h2>
                <p>Delivery routes and schedules assigned to you.</p>
            </div>

            <span class="section-count">
                <?= count($manifests) ?> manifests
            </span>

        </div>

        <?php if ($manifests): ?>

            <div class="driver-table-wrapper">

                <table class="driver-table">

                    <thead>
                        <tr>
                            <th>Manifest ID</th>
                            <th>Assigned date</th>
                            <th>Route</th>
                            <th>Vehicle</th>
                            <th>Schedule</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php foreach ($manifests as $manifest): ?>

                        <tr>

                            <td>
                                <strong><?= h($manifest['driverManifestId']) ?></strong>
                            </td>

                            <td>
                                <?= h(driver_date($manifest['assignedDate'])) ?>
                            </td>

                            <td>
                                <?= h($manifest['route'] ?: 'Not specified') ?>
                            </td>

                            <td>
                                <strong>
                                    <?= h($manifest['registrationNumber'] ?: 'Not assigned') ?>
                                </strong>

                                <small class="table-secondary">
                                    <?= h($manifest['vehicleType'] ?: '') ?>
                                </small>
                            </td>

                            <td>
                                <?= h($manifest['schedule'] ?: 'Not specified') ?>
                            </td>

                            <td>
                                <span class="status-badge <?= h(driver_status_class($manifest['status'])) ?>">
                                    <?= h($manifest['status'] ?: 'PENDING') ?>
                                </span>
                            </td>

                        </tr>

                    <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        <?php else: ?>

            <div class="driver-empty-state">

                <div class="empty-state-icon">▤</div>

                <h3>No manifests assigned</h3>

                <p>
                    Your assigned manifests will appear here once
                    the transport team assigns them.
                </p>

            </div>

        <?php endif; ?>

    </section>


    <!-- Pickups -->

    <section class="driver-section" id="pickups">

        <div class="section-heading">

            <div>
                <span class="section-eyebrow">COLLECTIONS</span>
                <h2>Assigned pickups</h2>
                <p>Pickup requests included in your assigned manifests.</p>
            </div>

            <span class="section-count">
                <?= count($pickups) ?> pickups
            </span>

        </div>

        <?php if ($pickups): ?>

            <div class="driver-table-wrapper">

                <table class="driver-table">

                    <thead>
                        <tr>
                            <th>Pickup ID</th>
                            <th>Pickup date</th>
                            <th>Time window</th>
                            <th>Collection address</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php foreach ($pickups as $pickup): ?>

                        <?php
                        $pickupStatus = strtoupper(trim($pickup['status'] ?? ''));

                        $pickupCanBeUpdated = !in_array(
                            $pickupStatus,
                            ['COMPLETED', 'FAILED', 'CANCELLED'],
                            true
                        );
                        ?>

                        <tr>

                            <td>
                                <strong><?= h($pickup['pickupId']) ?></strong>
                            </td>

                            <td>
                                <?= h(driver_date($pickup['pickupDate'])) ?>
                            </td>

                            <td>
                                <?= h($pickup['preferredTimeWindow'] ?: 'Not specified') ?>
                            </td>

                            <td class="address-cell">
                                <?= h($pickup['pickupAddress']) ?>

                                <?php if (!empty($pickup['failureReason'])): ?>
                                    <small class="table-secondary">
                                        Reason: <?= h($pickup['failureReason']) ?>
                                    </small>
                                <?php endif; ?>
                            </td>

                            <td>
                                <span class="status-badge <?= h(driver_status_class($pickupStatus)) ?>">
                                    <?= h($pickupStatus ?: 'PENDING') ?>
                                </span>
                            </td>

                            <td>

                                <?php if ($pickupCanBeUpdated): ?>

                                    <div class="driver-row-actions">

                                        <form method="POST" action="driver_actions.php">

                                            <input type="hidden" name="csrf_token" value="<?= h($csrfToken) ?>">

                                            <input type="hidden" name="action" value="pickup_success">

                                            <input type="hidden" name="pickup_id" value="<?= h($pickup['pickupId']) ?>">

                                            <button type="submit" class="btn btn-small btn-success">
                                                Successful
                                            </button>

                                        </form>

                                        <form method="POST" action="driver_actions.php" class="pickup-failed-form">

                                            <input type="hidden" name="csrf_token" value="<?= h($csrfToken) ?>">

                                            <input type="hidden" name="action" value="pickup_failed">

                                            <input type="hidden" name="pickup_id" value="<?= h($pickup['pickupId']) ?>">

                                            <input
                                                type="text"
                                                name="failure_reason"
                                                placeholder="Failure reason"
                                                maxlength="255"
                                                required
                                            >

                                            <button type="submit" class="btn btn-small btn-danger">
                                                Failed
                                            </button>

                                        </form>

                                    </div>

                                <?php else: ?>

                                    <span class="action-complete">
                                        No action required
                                    </span>

                                <?php endif; ?>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        <?php else: ?>

            <div class="driver-empty-state">

                <div class="empty-state-icon">↗</div>

                <h3>No pickup assignments</h3>

                <p>
                    Pickup requests assigned to your manifests
                    will appear here.
                </p>

            </div>

        <?php endif; ?>

    </section>


    <!-- Deliveries -->

    <section class="driver-section" id="deliveries">

        <div class="section-heading">

            <div>
                <span class="section-eyebrow">PARCEL DELIVERIES</span>
                <h2>Assigned deliveries</h2>
                <p>Delivery details and actions for your assigned parcels.</p>
            </div>

            <span class="section-count">
                <?= count($deliveries) ?> deliveries
            </span>

        </div>

        <?php if ($deliveries): ?>

            <div class="driver-table-wrapper">

                <table class="driver-table">

                    <thead>
                        <tr>
                            <th>Delivery ID</th>
                            <th>Parcel</th>
                            <th>Receiver</th>
                            <th>Delivery address</th>
                            <th>Delivery date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php foreach ($deliveries as $delivery): ?>

                        <?php
                        $deliveryStatus = strtoupper(trim($delivery['status'] ?? ''));
                        ?>

                        <tr>

                            <td>
                                <strong><?= h($delivery['deliveryId']) ?></strong>
                                <small class="table-secondary">
                                    <?= h($delivery['direction'] ?: '') ?>
                                </small>
                            </td>

                            <td>
                                <strong><?= h($delivery['parcelId']) ?></strong>
                                <small class="table-secondary">
                                    <?= h($delivery['parcelDescription'] ?: 'No description') ?>
                                </small>
                            </td>

                            <td>
                                <strong>
                                    <?= h($delivery['receiverName'] ?: 'Not provided') ?>
                                </strong>
                                <small class="table-secondary">
                                    <?= h($delivery['receiverPhone'] ?: '') ?>
                                </small>
                            </td>

                            <td class="address-cell">
                                <?= h($delivery['deliveryAddress'] ?: 'Not specified') ?>
                            </td>

                            <td>
                                <?= h(driver_date($delivery['deliveryDate'])) ?>
                            </td>

                            <td>
                                <span class="status-badge <?= h(driver_status_class($deliveryStatus)) ?>">
                                    <?= h($deliveryStatus ?: 'PENDING') ?>
                                </span>
                            </td>

                            <td>

                                <?php if ($deliveryStatus === 'DELIVERED'): ?>

                                    <span class="action-complete">
                                        Delivered
                                    </span>

                                <?php elseif ($deliveryStatus === 'ARRIVED'): ?>

                                    <a
                                        href="delivery_pin.php?delivery_id=<?= urlencode($delivery['deliveryId']) ?>"
                                        class="btn btn-small btn-primary"
                                    >
                                        Verify PIN
                                    </a>

                                <?php else: ?>

                                    <form method="POST" action="driver_actions.php">

                                        <input type="hidden" name="csrf_token" value="<?= h($csrfToken) ?>">

                                        <input type="hidden" name="action" value="delivery_arrived">

                                        <input type="hidden" name="delivery_id" value="<?= h($delivery['deliveryId']) ?>">

                                        <button type="submit" class="btn btn-small btn-primary">
                                            Mark arrived
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

            <div class="driver-empty-state">

                <div class="empty-state-icon">▣</div>

                <h3>No delivery assignments</h3>

                <p>
                    Deliveries assigned to your manifests
                    will appear here.
                </p>

            </div>

        <?php endif; ?>

    </section>
</main>

<footer class="driver-footer">
    <div class="container">
        <span>HELMS — Henowic Inventors (Pvt) Ltd.</span>
        <span>Driver Portal</span>
    </div>
</footer>

</body>
</html>