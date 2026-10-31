
<?php

require_once __DIR__ . '/layout.php';

$driver = dr_driver();
$manifests = dr_manifests();
$pickups = dr_pickups();
$deliveries = dr_deliveries();

$totalManifests = count($manifests);
$totalPickups = count($pickups);
$totalDeliveries = count($deliveries);

$completedDeliveries = 0;

foreach ($deliveries as $delivery) {
    if (strtolower($delivery['status']) === 'delivered') {
        $completedDeliveries++;
    }
}

$pendingDeliveries = $totalDeliveries - $completedDeliveries;

function driver_h($value)
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function driver_status_class($status)
{
    $status = strtolower(trim((string) $status));
    $status = preg_replace('/[^a-z0-9]+/', '-', $status);

    return 'status-' . trim($status, '-');
}

$flash = dr_get_flash();

dr_header('Driver Dashboard', 'dashboard');

?>

<!-- FLASH MESSAGE -->

<?php if ($flash): ?>

    <div class="driver-action-message <?= driver_h($flash['type']); ?>">
        <?= driver_h($flash['message']); ?>
    </div>

<?php endif; ?>


<!-- WELCOME -->

<section class="driver-welcome">

    <div>
        <span class="driver-eyebrow">DRIVER WORKSPACE</span>

        <h1>
            Good day, <?= driver_h($driver['fullName']); ?>.
        </h1>

        <p>
            Here is an overview of your assigned manifests,
            pickups and deliveries.
        </p>
    </div>

    <div class="driver-status">
        <span class="status-indicator"></span>
        <span>Driver status:</span>
        <strong><?= driver_h($driver['status']); ?></strong>
    </div>

</section>


<!-- AVAILABILITY -->

<section class="driver-availability-card">

    <div>
        <h2>Update your availability</h2>

        <p>
            Let the transport manager know whether you are available for assignments.
        </p>
    </div>

    <form class="driver-availability-form"
          method="POST"
          action="driver_actions_dummy.php">

        <input type="hidden" name="action" value="updateAvailability">

        <select name="availability" required>
            <option value="Available"
                <?= $driver['status'] === 'Available' ? 'selected' : ''; ?>>
                Available
            </option>

            <option value="Unavailable"
                <?= $driver['status'] === 'Unavailable' ? 'selected' : ''; ?>>
                Unavailable
            </option>
        </select>

        <button type="submit" class="driver-action-button driver-action-primary">
            Update availability
        </button>

    </form>

</section>


<!-- DRIVER PROFILE -->

<section class="driver-profile-strip">

    <div>
        <span class="profile-label">Driver ID</span>
        <strong><?= driver_h($driver['driverId']); ?></strong>
    </div>

    <div>
        <span class="profile-label">License number</span>
        <strong><?= driver_h($driver['licenseNumber']); ?></strong>
    </div>

    <div>
        <span class="profile-label">Contact number</span>
        <strong><?= driver_h($driver['contactNumber']); ?></strong>
    </div>

</section>


<!-- SUMMARY CARDS -->

<section class="driver-stats">

    <article class="driver-stat-card">
        <div class="stat-icon navy-icon">▤</div>

        <div>
            <span>Total manifests</span>
            <strong><?= $totalManifests; ?></strong>
            <small>Assigned to you</small>
        </div>
    </article>

    <article class="driver-stat-card">
        <div class="stat-icon amber-icon">↗</div>

        <div>
            <span>Pickup assignments</span>
            <strong><?= $totalPickups; ?></strong>
            <small>Across your manifests</small>
        </div>
    </article>

    <article class="driver-stat-card">
        <div class="stat-icon blue-icon">▣</div>

        <div>
            <span>Total deliveries</span>
            <strong><?= $totalDeliveries; ?></strong>
            <small>Assigned to you</small>
        </div>
    </article>

    <article class="driver-stat-card">
        <div class="stat-icon green-icon">✓</div>

        <div>
            <span>Completed deliveries</span>
            <strong><?= $completedDeliveries; ?></strong>
            <small><?= $pendingDeliveries; ?> not marked delivered</small>
        </div>
    </article>

</section>


<!-- MANIFESTS -->

<section class="driver-section" id="manifests">

    <div class="driver-section-heading">
        <div>
            <span class="section-kicker">YOUR ASSIGNMENTS</span>
            <h2>My manifests</h2>
        </div>

        <span class="section-count">
            <?= $totalManifests; ?> shown
        </span>
    </div>

    <?php if (!empty($manifests)): ?>

        <div class="driver-table-wrap">

            <table class="driver-table">

                <thead>
                    <tr>
                        <th>Manifest</th>
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
                                <strong><?= driver_h($manifest['id']); ?></strong>
                            </td>

                            <td><?= driver_h($manifest['date']); ?></td>

                            <td><?= driver_h($manifest['route']); ?></td>

                            <td><?= driver_h($manifest['vehicle']); ?></td>

                            <td><?= driver_h($manifest['schedule']); ?></td>

                            <td>
                                <span class="status-pill <?= driver_status_class($manifest['status']); ?>">
                                    <?= driver_h($manifest['status']); ?>
                                </span>
                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    <?php else: ?>

        <div class="driver-empty">
            <strong>No manifests assigned yet</strong>
        </div>

    <?php endif; ?>

</section>


<!-- PICKUPS -->

<section class="driver-section" id="pickups">

    <div class="driver-section-heading">
        <div>
            <span class="section-kicker">COLLECTIONS</span>
            <h2>Assigned pickups</h2>
        </div>

        <span class="section-count">
            <?= $totalPickups; ?> shown
        </span>
    </div>

    <?php if (!empty($pickups)): ?>

        <div class="driver-table-wrap">

            <table class="driver-table">

                <thead>
                    <tr>
                        <th>Pickup ID</th>
                        <th>Pickup date</th>
                        <th>Time window</th>
                        <th>Collection address</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>

                    <?php foreach ($pickups as $pickup): ?>

                        <tr>

                            <td>
                                <strong><?= driver_h($pickup['id']); ?></strong>
                            </td>

                            <td><?= driver_h($pickup['date']); ?></td>

                            <td><?= driver_h($pickup['time']); ?></td>

                            <td><?= driver_h($pickup['address']); ?></td>

                            <td>
                                <span class="status-pill <?= driver_status_class($pickup['status']); ?>">
                                    <?= driver_h($pickup['status']); ?>
                                </span>

                                <?php if (!empty($pickup['failureReason'])): ?>
                                    <small class="table-subtext">
                                        Reason: <?= driver_h($pickup['failureReason']); ?>
                                    </small>
                                <?php endif; ?>
                            </td>

                            <td>

                                <?php if (!in_array($pickup['status'], ['Completed', 'Failed'], true)): ?>

                                    <div class="driver-action-group">

                                        <form method="POST" action="driver_actions_dummy.php">

                                            <input type="hidden" name="action" value="markPickupSuccessful">
                                            <input type="hidden" name="id" value="<?= driver_h($pickup['id']); ?>">

                                            <button type="submit"
                                                    class="driver-action-button driver-action-success">
                                                Mark successful
                                            </button>

                                        </form>

                                        <form method="POST"
                                              action="driver_actions_dummy.php"
                                              class="driver-failed-form">

                                            <input type="hidden" name="action" value="markPickupFailed">
                                            <input type="hidden" name="id" value="<?= driver_h($pickup['id']); ?>">

                                            <input type="text"
                                                   name="failureReason"
                                                   placeholder="Failure reason"
                                                   maxlength="255"
                                                   required>

                                            <button type="submit"
                                                    class="driver-action-button driver-action-danger">
                                                Failed
                                            </button>

                                        </form>

                                    </div>

                                <?php else: ?>

                                    <span class="action-complete">
                                        Processed
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
            <strong>No pickup assignments</strong>
            <p>Pickups assigned to your manifests will appear here.</p>
        </div>

    <?php endif; ?>

</section>


<!-- DELIVERIES -->

<section class="driver-section" id="deliveries">

    <div class="driver-section-heading">
        <div>
            <span class="section-kicker">DELIVERY WORK</span>
            <h2>Assigned deliveries</h2>
        </div>

        <span class="section-count">
            <?= $totalDeliveries; ?> shown
        </span>
    </div>

    <?php if (!empty($deliveries)): ?>

        <div class="driver-table-wrap">

            <table class="driver-table">

                <thead>
                    <tr>
                        <th>Delivery</th>
                        <th>Parcel</th>
                        <th>Receiver</th>
                        <th>Delivery address</th>
                        <th>Date</th>
                        <th>Attempts</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>

                    <?php foreach ($deliveries as $delivery): ?>

                        <tr>

                            <td>
                                <strong><?= driver_h($delivery['id']); ?></strong>
                            </td>

                            <td><?= driver_h($delivery['parcelId']); ?></td>

                            <td>
                                <strong><?= driver_h($delivery['receiver']); ?></strong>

                                <small class="table-subtext">
                                    <?= driver_h($delivery['phone']); ?>
                                </small>
                            </td>

                            <td><?= driver_h($delivery['address']); ?></td>

                            <td><?= driver_h($delivery['date']); ?></td>

                            <td><?= (int) $delivery['attempts']; ?></td>

                            <td>
                                <span class="status-pill <?= driver_status_class($delivery['status']); ?>">
                                    <?= driver_h($delivery['status']); ?>
                                </span>
                            </td>

                            <td>

                                <?php if ($delivery['status'] === 'Delivered'): ?>

                                    <span class="action-complete">
                                        Delivered
                                    </span>

                                <?php elseif ($delivery['status'] === 'Arrived'): ?>

                                    <a href="delivery_pin_dummy.php?id=<?= urlencode($delivery['id']); ?>"
                                       class="driver-action-button driver-action-primary">
                                        Submit PIN
                                    </a>

                                <?php else: ?>

                                    <form method="POST" action="driver_actions_dummy.php">

                                        <input type="hidden" name="action" value="markDeliveryArrived">
                                        <input type="hidden" name="id" value="<?= driver_h($delivery['id']); ?>">

                                        <button type="submit"
                                                class="driver-action-button driver-action-primary">
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

        <div class="driver-empty">
            <strong>No delivery assignments</strong>
            <p>Deliveries assigned to your manifests will appear here.</p>
        </div>

    <?php endif; ?>

</section>


<p class="driver-footnote">
    This is a demonstration dashboard. Actions update temporary session data
    and do not modify the HELMS database.
</p>


<?php

dr_footer();

?>
