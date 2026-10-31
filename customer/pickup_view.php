<?php
// READ (single): pickup details + progress tracker (reuses the home page's .steps component)
require_once __DIR__ . '/_layout.php';
require_once __DIR__ . '/pickup_functions.php';
$uid = cust_id();
$p = pickup_get($pdo, (int)($_GET['id'] ?? 0), $uid);
if (!$p) { cust_flash('error', 'Pickup not found.'); header('Location: pickups.php'); exit; }
$s = $p['status'];

// [label, state(done|now|bad|todo), icon]
$steps = [['Requested', 'done', CUST_CHECK]];
if ($s === PK_REJECTED)      $steps[] = ['Rejected', 'bad', CUST_X];
elseif ($s === PK_CANCELLED) $steps[] = ['Cancelled', 'bad', CUST_X];
else {
    $steps[] = ['Approved', $s === PK_REQUESTED ? 'now' : 'done', $s === PK_REQUESTED ? CUST_CLOCK : CUST_CHECK];
    $steps[] = $s === 'Failed' ? ['Collection failed', 'bad', CUST_X]
             : ['Collected', $s === 'Successful' ? 'done' : 'todo', $s === 'Successful' ? CUST_CHECK : CUST_TRUCK];
}

cust_header($pdo, 'Pickup #' . $p['pickupId'], 'pickups');
cust_hero('PICKUP MANAGEMENT', 'Pickup <span>#' . (int)$p['pickupId'] . '</span>',
    'Requested on ' . date('d M Y, H:i', strtotime($p['requestedAt'])) . '.',
    '<a class="btn btn-ghost" href="pickups.php">← Back to my pickups</a>');
?>
<section class="cu-over">
  <div class="container">
    <?php cust_show_flash(); ?>
    <div class="cu-card">
      <div class="cu-card-head"><h2>Progress</h2><span class="cu-badge cu-<?= cust_status_class($s) ?>"><?= cust_h($s) ?></span></div>
      <div class="steps cu-steps">
        <?php foreach ($steps as [$label, $state, $icon]): ?>
          <div class="step <?= $state === 'todo' ? '' : cust_h($state) ?>"><div class="dot"><?= $icon ?></div><?= cust_h($label) ?></div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="cu-card">
      <div class="cu-card-head"><h2>Pickup details</h2></div>
      <div class="cu-detail-grid">
        <div class="cu-detail"><small>PICKUP DATE</small><strong><?= cust_h(date('D, d M Y', strtotime($p['pickupDate']))) ?></strong></div>
        <div class="cu-detail"><small>TIME WINDOW</small><strong><?= cust_h($p['preferredTimeWindow']) ?></strong></div>
        <div class="cu-detail"><small>REFERENCE</small><strong>#<?= (int)$p['pickupId'] ?></strong></div>
        <div class="cu-detail cu-wide"><small>PICKUP ADDRESS</small><strong><?= nl2br(cust_h($p['pickupAddress'])) ?></strong></div>
        <?php if ($p['cancelledAt']): ?><div class="cu-detail"><small>CANCELLED ON</small><strong><?= cust_h(date('d M Y, H:i', strtotime($p['cancelledAt']))) ?></strong></div><?php endif; ?>
        <?php if ($p['rejectionReason']): ?><div class="cu-detail cu-wide"><small>REJECTION REASON</small><strong><?= cust_h($p['rejectionReason']) ?></strong></div><?php endif; ?>
        <?php if ($p['failureReason']): ?><div class="cu-detail cu-wide"><small>FAILURE REASON</small><strong><?= cust_h($p['failureReason']) ?></strong></div><?php endif; ?>
      </div>

      <?php if (pickup_can_edit($p) || pickup_can_cancel($p)): ?>
      <div class="cu-actions" style="margin-top:22px">
        <?php if (pickup_can_edit($p)): ?><a class="btn btn-primary" href="pickup_edit.php?id=<?= (int)$p['pickupId'] ?>">Edit pickup</a><?php endif; ?>
        <?php if (pickup_can_cancel($p)): ?>
          <form method="post" action="pickup_cancel.php" data-confirm="Pickup #<?= (int)$p['pickupId'] ?> will be cancelled. This cannot be undone.">
            <input type="hidden" name="csrf" value="<?= cust_h(cust_csrf()) ?>">
            <input type="hidden" name="id" value="<?= (int)$p['pickupId'] ?>">
            <button class="btn btn-outline cu-danger-outline" type="submit">Cancel pickup</button>
          </form>
        <?php endif; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php cust_footer(); ?>
