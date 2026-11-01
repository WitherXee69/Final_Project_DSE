<?php
// READ (single): pickup details + progress tracker (reuses the home page's .steps component)
require_once __DIR__ . '/_layout.php';
require_once __DIR__ . '/pickup_functions.php';
$uid = cust_id();
$p = pickup_get($pdo, (string)($_GET['id'] ?? ''), $uid);
if (!$p) { cust_flash('error', 'Pickup not found.'); header('Location: pickups.php'); exit; }
$s = $p['status'];

// Progress: Requested -> Approved -> Driver assigned -> Picked up. Each step = [label, state(done|now|bad|todo), icon]
$labels = ['Requested', 'Approved', 'Driver assigned', 'Picked up'];
$icons  = [CUST_CHECK, CUST_CHECK, CUST_TRUCK, CUST_PIN];
$rank   = [PK_REQUESTED => 0, PK_APPROVED => 1, PK_ASSIGNED => 2, PK_PICKED_UP => 3, PK_FAILED => 2][$s] ?? null;

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

    <div class="cu-card">
      <div class="cu-card-head"><h2>Pickup details</h2></div>
      <div class="cu-detail-grid">
        <div class="cu-detail"><small>PICKUP DATE</small><strong><?= cust_h(date('D, d M Y', strtotime($p['pickupDate']))) ?></strong></div>
        <div class="cu-detail"><small>TIME WINDOW</small><strong><?= cust_h($p['preferredTimeWindow']) ?></strong></div>
        <div class="cu-detail"><small>REFERENCE</small><strong><?= cust_h($p['pickupId']) ?></strong></div>
        <div class="cu-detail cu-wide"><small>PICKUP ADDRESS</small><strong><?= nl2br(cust_h($p['pickupAddress'])) ?></strong></div>
        <?php if ($p['cancelledAt']): ?><div class="cu-detail"><small>CANCELLED ON</small><strong><?= cust_h(date('d M Y, H:i', strtotime($p['cancelledAt']))) ?></strong></div><?php endif; ?>
        <?php if ($p['rejectionReason']): ?><div class="cu-detail cu-wide"><small>REJECTION REASON</small><strong><?= cust_h($p['rejectionReason']) ?></strong></div><?php endif; ?>
        <?php if ($p['failureReason']): ?><div class="cu-detail cu-wide"><small>FAILURE REASON</small><strong><?= cust_h($p['failureReason']) ?></strong></div><?php endif; ?>
      </div>

      <?php if (pickup_can_edit($p) || pickup_can_cancel($p)): ?>
      <div class="cu-actions" style="margin-top:22px">
        <?php if (pickup_can_edit($p)): ?><a class="btn btn-primary" href="pickup_edit.php?id=<?= urlencode($p['pickupId']) ?>">Edit pickup</a><?php endif; ?>
        <?php if (pickup_can_cancel($p)): ?>
          <form method="post" action="pickup_cancel.php" data-confirm="Pickup <?= cust_h($p['pickupId']) ?> will be cancelled and permanently deleted. This cannot be undone.">
            <input type="hidden" name="csrf_token" value="<?= cust_h(cust_csrf()) ?>">
            <input type="hidden" name="id" value="<?= cust_h($p['pickupId']) ?>">
            <button class="btn btn-outline cu-danger-outline" type="submit">Cancel pickup</button>
          </form>
        <?php endif; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php cust_footer(); ?>
