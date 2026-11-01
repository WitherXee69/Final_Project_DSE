<?php
// READ (single): pickup, parcel, receiver and progress
require_once __DIR__ . '/_layout.php';
$uid = cust_id();
$p = pickup_details($pdo, (string)($_GET['id'] ?? ''), $uid);
if (!$p) { cust_flash('error', 'Pickup not found.'); header('Location: pickups.php'); exit; }
$s  = pickup_status($p['status']);
$pc = $p['parcel']; $rc = $p['receiver'];

// Progress: Requested -> Approved -> Driver assigned -> Collected. Each step = [label, state(done|now|bad|todo), icon]
$labels = ['Requested', 'Approved', 'Driver assigned', 'Collected'];
$icons  = [CUST_CHECK, CUST_CHECK, CUST_TRUCK, CUST_PIN];
$rank   = [PK_REQUESTED => 0, PK_APPROVED => 1, PK_SCHEDULED => 2, PK_COMPLETED => 3, PK_FAILED => 2][$s] ?? null;

$steps = [['Requested', 'done', CUST_CHECK]];
if ($s === PK_REJECTED)      { $steps[] = ['Rejected', 'bad', CUST_X]; }
elseif ($s === PK_CANCELLED) { $steps[] = ['Cancelled', 'bad', CUST_X]; }
else {
    for ($i = 1; $i <= 3; $i++) {
        if ($s === PK_FAILED && $i === 3) { $steps[] = ['Pickup failed', 'bad', CUST_X]; break; }
        $state = $rank > $i || ($rank === $i && $i === 3) ? 'done' : ($rank === $i ? 'now' : 'todo');
        if ($s === PK_FAILED && $i <= 2) $state = 'done';
        $steps[] = [$labels[$i], $state, $state === 'done' ? CUST_CHECK : $icons[$i]];
    }
}

cust_header($pdo, 'Pickup ' . $p['pickupId'], 'pickups');
cust_hero('PICKUP MANAGEMENT', 'Pickup <span>' . cust_h($p['pickupId']) . '</span>',
    'Requested on ' . date('d M Y, H:i', strtotime($p['requestedAt'])) . '.',
    '<a class="btn btn-ghost" href="pickups.php">← Back to my pickups</a>');
?>
<section class="cu-over">
  <div class="container">
    <?php cust_show_flash(); ?>

    <div class="cu-card">
      <div class="cu-card-head"><h2>Progress</h2><span class="cu-badge cu-<?= cust_status_class($s) ?>"><?= cust_h(pickup_label($s)) ?></span></div>
      <div class="steps cu-steps">
        <?php foreach ($steps as [$label, $state, $icon]): ?>
          <div class="step <?= $state === 'todo' ? '' : cust_h($state) ?>"><div class="dot"><?= $icon ?></div><?= cust_h($label) ?></div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="cu-viewgrid">
      <div class="cu-col">
        <div class="cu-card">
          <div class="cu-card-head"><h2>Pickup</h2></div>
          <div class="cu-detail-grid cu-d2">
            <div class="cu-detail"><small>PICKUP DATE</small><strong><?= $p['pickupDate'] ? cust_h(date('D, d M Y', strtotime($p['pickupDate']))) : '–' ?></strong></div>
            <div class="cu-detail"><small>TIME WINDOW</small><strong><?= cust_h($p['preferredTimeWindow'] ?: '–') ?></strong></div>
            <div class="cu-detail cu-wide"><small>PICKUP ADDRESS</small><strong><?= nl2br(cust_h($p['pickupAddress'])) ?></strong></div>
            <?php if ($p['rejectionReason']): ?><div class="cu-detail cu-wide"><small>REJECTION REASON</small><strong><?= cust_h($p['rejectionReason']) ?></strong></div><?php endif; ?>
            <?php if ($p['failureReason']): ?><div class="cu-detail cu-wide"><small>FAILURE REASON</small><strong><?= cust_h($p['failureReason']) ?></strong></div><?php endif; ?>
          </div>
        </div>

        <div class="cu-card">
          <div class="cu-card-head"><h2>Parcel</h2></div>
          <?php if ($pc): ?>
          <div class="cu-detail-grid cu-d2">
            <div class="cu-detail cu-wide"><small>DESCRIPTION</small><strong><?= cust_h($pc['description']) ?></strong></div>
            <div class="cu-detail"><small>CATEGORY</small><strong><?= cust_h(PARCEL_CATEGORIES[$pc['parcelCategory']] ?? $pc['parcelCategory']) ?></strong></div>
            <div class="cu-detail"><small>DELIVERY SERVICE</small><strong><?= cust_h(SERVICE_LABELS[$pc['serviceType']] ?? $pc['serviceType']) ?></strong></div>
            <div class="cu-detail"><small>WEIGHT</small><strong><?= cust_h(rtrim(rtrim((string)$pc['declaredWeight'], '0'), '.')) ?> kg</strong></div>
            <div class="cu-detail"><small>SIZE</small><strong><?= $pc['dimensions'] ? cust_h(str_replace('x', ' × ', $pc['dimensions'])) . ' cm' : '–' ?></strong></div>
            <div class="cu-detail"><small>HANDLING</small><strong><?= $pc['isFragile'] ? 'Fragile' : 'Standard' ?></strong></div>
          </div>
          <?php else: ?>
            <div class="cu-empty">No parcel details were recorded for this pickup.</div>
          <?php endif; ?>
        </div>

        <div class="cu-card">
          <div class="cu-card-head"><h2>Receiver</h2></div>
          <?php if ($rc): ?>
          <div class="cu-detail-grid cu-d2">
            <div class="cu-detail"><small>NAME</small><strong><?= cust_h($rc['receiverName']) ?></strong></div>
            <div class="cu-detail"><small>PHONE</small><strong><?= cust_h($rc['receiverPhone'] ?: '–') ?></strong></div>
            <div class="cu-detail cu-wide"><small>DELIVERY ADDRESS</small><strong><?= nl2br(cust_h($rc['receiverAddress'] ?: '–')) ?></strong></div>
            <div class="cu-detail"><small>ZONE</small><strong><?= $rc['zoneCode'] ? cust_h((ZONE_LABELS[$rc['zoneCode']] ?? $rc['zoneCode']) . ' (' . $rc['zoneCode'] . ')') : '–' ?></strong></div>
          </div>
          <?php else: ?>
            <div class="cu-empty">No receiver details were recorded for this pickup.</div>
          <?php endif; ?>
        </div>
      </div>

      <div class="cu-col">
        <?php if (pickup_can_edit($p) || pickup_can_cancel($p)): ?>
        <aside class="cu-card">
          <div class="cu-card-head"><h2>Manage</h2></div>
          <div class="cu-actions">
            <?php if (pickup_can_edit($p)): ?><a class="btn btn-primary" href="pickup_edit.php?id=<?= urlencode($p['pickupId']) ?>">Edit pickup</a><?php endif; ?>
            <?php if (pickup_can_cancel($p)): ?>
              <form method="post" action="pickup_cancel.php" data-confirm="Pickup <?= cust_h($p['pickupId']) ?> will be cancelled and permanently deleted, together with its parcel details and receiver. This cannot be undone.">
                <input type="hidden" name="csrf_token" value="<?= cust_h(cust_csrf()) ?>">
                <input type="hidden" name="id" value="<?= cust_h($p['pickupId']) ?>">
                <button class="btn btn-outline cu-danger-outline" type="submit">Cancel pickup</button>
              </form>
            <?php endif; ?>
          </div>
        </aside>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
<?php cust_footer(); ?>
