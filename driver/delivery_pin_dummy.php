
<?php

require_once __DIR__ . '/layout.php';

$deliveryId = trim($_GET['id'] ?? '');

$pendingId = $_SESSION['driver_dummy_pending_delivery'] ?? '';

if ($deliveryId === '' || $deliveryId !== $pendingId) {
    dr_set_flash('error', 'No active delivery verification was found.');
    header('Location: driver_dashboard_dummy.php');
    exit;
}

$delivery = null;

foreach (dr_deliveries() as $item) {

    if ($item['id'] === $deliveryId) {
        $delivery = $item;
        break;
    }
}

if (!$delivery || $delivery['status'] !== 'Arrived') {
    dr_set_flash('error', 'This delivery is not awaiting PIN verification.');
    header('Location: driver_dashboard_dummy.php');
    exit;
}

$flash = dr_get_flash();

dr_header('Delivery PIN Verification', '');

?>

<section class="driver-section" style="max-width: 620px; margin: 25px auto;">

    <div class="driver-section-heading">

        <div>
            <span class="section-kicker">DELIVERY CONFIRMATION</span>
            <h2>Receiver PIN verification</h2>
        </div>

        <span class="status-pill status-arrived">
            Arrived
        </span>

    </div>

    <div style="padding: 22px;">

        <?php if ($flash): ?>

            <div class="driver-action-message <?= htmlspecialchars($flash['type'], ENT_QUOTES, 'UTF-8'); ?>">
                <?= htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8'); ?>
            </div>

        <?php endif; ?>

        <p style="color: #748093; font-size: 13px; line-height: 1.7; margin-top: 0;">
            You have recorded your arrival at the delivery location.
            Enter the receiver's numeric PIN to complete this delivery.
        </p>

        <div class="driver-profile-strip" style="grid-template-columns: 1fr 1fr;">

            <div>
                <span class="profile-label">Delivery ID</span>
                <strong>
                    <?= htmlspecialchars($delivery['id'], ENT_QUOTES, 'UTF-8'); ?>
                </strong>
            </div>

            <div>
                <span class="profile-label">Parcel ID</span>
                <strong>
                    <?= htmlspecialchars($delivery['parcelId'], ENT_QUOTES, 'UTF-8'); ?>
                </strong>
            </div>

            <div>
                <span class="profile-label">Receiver</span>
                <strong>
                    <?= htmlspecialchars($delivery['receiver'], ENT_QUOTES, 'UTF-8'); ?>
                </strong>
            </div>

            <div>
                <span class="profile-label">Contact number</span>
                <strong>
                    <?= htmlspecialchars($delivery['phone'], ENT_QUOTES, 'UTF-8'); ?>
                </strong>
            </div>

        </div>

        <form method="POST" action="driver_actions_dummy.php">

            <input type="hidden" name="action" value="submitDeliveryPIN">

            <input type="hidden"
                   name="deliveryId"
                   value="<?= htmlspecialchars($delivery['id'], ENT_QUOTES, 'UTF-8'); ?>">

            <div style="margin-bottom: 17px;">

                <label for="pin"
                       style="display: block; font-size: 12px; font-weight: 700; margin-bottom: 8px;">
                    Receiver PIN
                </label>

                <input type="password"
                       id="pin"
                       name="pin"
                       inputmode="numeric"
                       pattern="[0-9]+"
                       maxlength="10"
                       placeholder="Enter numeric PIN"
                       required
                       style="width: 100%; box-sizing: border-box; padding: 13px; border: 1px solid #dce2e9; border-radius: 8px; font-family: inherit; font-size: 15px;">

                <small style="display: block; margin-top: 7px; color: #929baa;">
                    Prototype mode: any numeric PIN will be accepted.
                </small>

            </div>

            <button type="submit"
                    class="driver-action-button driver-action-success"
                    style="width: 100%; min-height: 43px;">
                Verify PIN and complete delivery
            </button>

        </form>

        <div style="margin-top: 14px; text-align: center;">

            <a href="driver_dashboard_dummy.php#deliveries"
               style="color: #687386; font-size: 12px; text-decoration: none;">
                Return to dashboard
            </a>

        </div>

    </div>

</section>

<?php

dr_footer();

?>
