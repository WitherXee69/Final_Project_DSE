<?php

require_once __DIR__ . '/_layout.php';
require_once __DIR__ . '/pickup_functions.php';
$uid = cust_id();
$p = pickup_get($pdo, (int)($_GET['id'] ?? 0), $uid);
if (!$p) { cust_flash('error', 'Pickup not found.'); header('Location: pickups.php'); exit; }


$s = $p['status'];
$steps = [['Requested', 'done']];
if ($s === PK_REJECTED)       { $steps[] = ['Rejected', 'bad']; }
elseif ($s === PK_CANCELLED)  { $steps[] = ['Cancelled', 'bad']; }
else {
    $steps[] = ['Approved', $s === PK_REQUESTED ? 'now' : 'done'];
    $steps[] = $s === 'Failed' ? ['Collection failed', 'bad']
             : ['Collected', $s === 'Successful' ? 'done' : 'todo'];
}

cust_header($pdo, 'Pickup #' . $p['pickupId'], 'pickups');
cust_page_head('PICKUP MANAGEMENT', 'Pickup #' . (int)$p['pickupId'], '<a class="cu-btn cu-btn-light" href="pickups.php">← Back</a>');
cust_show_flash();
?>
<section class="cu-panel">
  <div class="cu-steps">
    <?php foreach ($steps as $i => [$label, $state]): ?>
      <div class="cu-step cu-step-<?= $state ?>"><span class="cu-dot"><?= $state === 'done' ? '✓' : ($state === 'bad' ? '✕' : $i + 1) ?></span><?= cust_h($label) ?></div>
    <?php endforeach; ?>
  </div>
</section>

<section class="cu-panel">
  <div class="cu-panel-head"><h2>Pickup details</h2>
    <span class="cu-badge cu-<?= cust_status_class($s) ?>"><?= cust_h($s) ?></span></div>
  <div class="cu-detail-grid">
    <div class="cu-detail"><small>Pickup date</small><strong><?= cust_h(date('D, d M Y', strtotime($p['pickupDate']))) ?></strong></div>
    <div class="cu-detail"><small>Time window</small><strong><?= cust_h($p['preferredTimeWindow']) ?></strong></div>
    <div class="cu-detail"><small>Requested on</small><strong><?= cust_h(date('d M Y, H:i', strtotime($p['requestedAt']))) ?></strong></div>
    <div class="cu-detail cu-wide"><small>Pickup address</small><strong><?= nl2br(cust_h($p['pickupAddress'])) ?></strong></div>
    <?php if ($p['cancelledAt']): ?><div class="cu-detail"><small>Cancelled on</small><strong><?= cust_h(date('d M Y, H:i', strtotime($p['cancelledAt']))) ?></strong></div><?php endif; ?>
    <?php if ($p['rejectionReason']): ?><div class="cu-detail cu-wide"><small>Rejection reason</small><strong><?= cust_h($p['rejectionReason']) ?></strong></div><?php endif; ?>
    <?php if ($p['failureReason']): ?><div class="cu-detail cu-wide"><small>Failure reason</small><strong><?= cust_h($p['failureReason']) ?></strong></div><?php endif; ?>
  </div>

  <?php if (pickup_can_edit($p) || pickup_can_cancel($p)): ?>
  <div class="cu-actions" style="margin-top:20px">
    <?php if (pickup_can_edit($p)): ?><a class="cu-btn cu-btn-primary" href="pickup_edit.php?id=<?= (int)$p['pickupId'] ?>">Edit pickup</a><?php endif; ?>
    <?php if (pickup_can_cancel($p)): ?>
      <form method="post" action="pickup_cancel.php" data-confirm="Pickup #<?= (int)$p['pickupId'] ?> will be cancelled. This cannot be undone.">
        <input type="hidden" name="csrf" value="<?= cust_h(cust_csrf()) ?>">
        <input type="hidden" name="id" value="<?= (int)$p['pickupId'] ?>">
        <button class="cu-btn cu-btn-danger" type="submit">Cancel pickup</button>
      </form>
    <?php endif; ?>
  </div>
  <?php elseif ($s === PK_APPROVED || $s === 'Successful'): ?>
  <?php endif; ?>
</section>
<?php cust_footer(); ?>
