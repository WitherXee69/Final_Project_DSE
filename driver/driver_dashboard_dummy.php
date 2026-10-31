<?php

require_once __DIR__ . '/layout.php';

// Get dummy data from layout.php
$driver = dr_driver();
$manifests = dr_manifests();
$pickups = dr_pickups();
$deliveries = dr_deliveries();

// Calculate dashboard statistics
$totalManifests = count($manifests);
$totalPickups = count($pickups);
$totalDeliveries = count($deliveries);

$completedDeliveries = 0;

foreach ($deliveries as $delivery) {
    $status = strtolower($delivery['status'] ?? '');

    if ($status === 'delivered' || $status === 'completed') {
        $completedDeliveries++;
    }
}

// Escape output safely
function driver_h($value)
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

// Generate a CSS class for each status
function driver_status_class($status)
{
    $status = strtolower(trim((string) $status));
    $status = preg_replace('/[^a-z0-9]+/', '-', $status);

    return 'status-' . trim($status, '-');
}

// Display the shared layout
dr_header('Driver Dashboard', 'dashboard');

?>

<!-- Welcome Section -->
<section class="driver-welcome">

    <div class="driver-welcome-content">
        <p class="driver-eyebrow">DRIVER PORTAL</p>

        <h1>
            Welcome back,
            <?= driver_h($driver['fullName']); ?>!
        </h1>

        <p>
            Here's an overview of your assigned manifests,
            pickups and deliveries.
        </p>
    </div>

    <div class="driver-welcome-date">
        <span>Today's Date</span>
        <strong><?= date('d M Y'); ?></strong>
    </div>

</section>


<!-- Driver Profile -->
<section class="driver-profile-strip">

    <div class="driver-profile-avatar">
        <?= strtoupper(substr($driver['fullName'] ?? 'D', 0, 1)); ?>
    </div>

    <div class="driver-profile-details">
        <h2><?= driver_h($driver['fullName']); ?></h2>

        <p>
            Driver ID:
            <strong><?= driver_h($driver['driverId']); ?></strong>
        </p>
    </div>

    <div class="driver-profile-info">
        <span>Licence Number</span>
        <strong><?= driver_h($driver['licenseNumber']); ?></strong>
    </div>

    <div class="driver-profile-info">
        <span>Contact Number</span>
        <strong><?= driver_h($driver['contactNumber']); ?></strong>
    </div>

    <div class="driver-profile-info">
        <span>Assigned Vehicle</span>
        <strong><?= driver_h($driver['vehicle']); ?></strong>
    </div>

    <div class="driver-profile-status">
        <span class="driver-status-label">Availability</span>

        <span class="status-pill <?= driver_status_class($driver['status']); ?>">
            <?= driver_h($driver['status']); ?>
        </span>
    </div>

</section>


<!-- Dashboard Statistics -->
<section class="driver-stats">

    <div class="driver-stat-card">
        <div class="driver-stat-icon icon-manifests">
            <span>▤</span>
        </div>

        <div class="driver-stat-content">
            <p>Total Manifests</p>
            <h2><?= $totalManifests; ?></h2>
            <span>Assigned to you</span>
        </div>
    </div>


    <div class="driver-stat-card">
        <div class="driver-stat-icon icon-pickups">
            <span>↗</span>
        </div>

        <div class="driver-stat-content">
            <p>Pickup Assignments</p>
            <h2><?= $totalPickups; ?></h2>
            <span>Assigned pickups</span>
        </div>
    </div>


    <div class="driver-stat-card">
        <div class="driver-stat-icon icon-deliveries">
            <span>▣</span>
        </div>

        <div class="driver-stat-content">
            <p>Total Deliveries</p>
            <h2><?= $totalDeliveries; ?></h2>
            <span>Assigned deliveries</span>
        </div>
    </div>


    <div class="driver-stat-card">
        <div class="driver-stat-icon icon-completed">
            <span>✓</span>
        </div>

        <div class="driver-stat-content">
            <p>Completed Deliveries</p>
            <h2><?= $completedDeliveries; ?></h2>
            <span>Successfully delivered</span>
        </div>
    </div>

</section>


<!-- My Manifests -->
<section class="driver-section" id="manifests">

    <div class="driver-section-heading">
        <div>
            <h2>My Manifests</h2>
            <p>Delivery manifests assigned to you.</p>
        </div>

        <span class="driver-section-count">
            <?= $totalManifests; ?> Manifests
        </span>
    </div>


    <div class="driver-table-wrap">

        <table class="driver-table">

            <thead>
                <tr>
                    <th>Manifest ID</th>
                    <th>Assigned Date</th>
                    <th>Schedule</th>
                    <th>Route</th>
                    <th>Vehicle</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody>

                <?php if (!empty($manifests)): ?>

                    <?php foreach ($manifests as $manifest): ?>

                        <tr>
                            <td class="table-primary-text">
                                <?= driver_h($manifest['id']); ?>
                            </td>

                            <td>
                                <?= driver_h($manifest['date']); ?>
                            </td>

                            <td>
                                <?= driver_h($manifest['schedule']); ?>
                            </td>

                            <td>
                                <?= driver_h($manifest['route']); ?>
                            </td>

                            <td>
                                <?= driver_h($manifest['vehicle']); ?>
                            </td>

                            <td>
                                <span class="status-pill <?= driver_status_class($manifest['status']); ?>">
                                    <?= driver_h($manifest['status']); ?>
                                </span>
                            </td>
                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="6" class="driver-empty-state">
                            No manifests have been assigned to you.
                        </td>
                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

</section>


<!-- Assigned Pickups -->
<section class="driver-section" id="pickups">

    <div class="driver-section-heading">
        <div>
            <h2>Assigned Pickups</h2>
            <p>Pickup requests assigned to you.</p>
        </div>

        <span class="driver-section-count">
            <?= $totalPickups; ?> Pickups
        </span>
    </div>


    <div class="driver-table-wrap">

        <table class="driver-table">

            <thead>
                <tr>
                    <th>Pickup ID</th>
                    <th>Pickup Date</th>
                    <th>Preferred Time</th>
                    <th>Pickup Address</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody>

                <?php if (!empty($pickups)): ?>

                    <?php foreach ($pickups as $pickup): ?>

                        <tr>
                            <td class="table-primary-text">
                                <?= driver_h($pickup['id']); ?>
                            </td>

                            <td>
                                <?= driver_h($pickup['date']); ?>
                            </td>

                            <td>
                                <?= driver_h($pickup['time']); ?>
                            </td>

                            <td class="table-address">
                                <?= driver_h($pickup['address']); ?>
                            </td>

                            <td>
                                <span class="status-pill <?= driver_status_class($pickup['status']); ?>">
                                    <?= driver_h($pickup['status']); ?>
                                </span>
                            </td>
                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="5" class="driver-empty-state">
                            No pickups have been assigned to you.
                        </td>
                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

</section>


<!-- Assigned Deliveries -->
<section class="driver-section" id="deliveries">

    <div class="driver-section-heading">
        <div>
            <h2>Assigned Deliveries</h2>
            <p>Deliveries assigned to you.</p>
        </div>

        <span class="driver-section-count">
            <?= $totalDeliveries; ?> Deliveries
        </span>
    </div>


    <div class="driver-table-wrap">

        <table class="driver-table">

            <thead>
                <tr>
                    <th>Delivery ID</th>
                    <th>Parcel ID</th>
                    <th>Delivery Date</th>
                    <th>Delivery Address</th>
                    <th>Receiver</th>
                    <th>Contact Number</th>
                    <th>Attempts</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody>

                <?php if (!empty($deliveries)): ?>

                    <?php foreach ($deliveries as $delivery): ?>

                        <tr>
                            <td class="table-primary-text">
                                <?= driver_h($delivery['id']); ?>
                            </td>

                            <td>
                                <?= driver_h($delivery['parcelId']); ?>
                            </td>

                            <td>
                                <?= driver_h($delivery['date']); ?>
                            </td>

                            <td class="table-address">
                                <?= driver_h($delivery['address']); ?>
                            </td>

                            <td>
                                <?= driver_h($delivery['receiver']); ?>
                            </td>

                            <td>
                                <?= driver_h($delivery['phone']); ?>
                            </td>

                            <td>
                                <?= driver_h($delivery['attempts']); ?>
                            </td>

                            <td>
                                <span class="status-pill <?= driver_status_class($delivery['status']); ?>">
                                    <?= driver_h($delivery['status']); ?>
                                </span>
                            </td>
                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="8" class="driver-empty-state">
                            No deliveries have been assigned to you.
                        </td>
                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

</section>


<?php

dr_footer();

?>