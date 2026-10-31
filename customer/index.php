<?php
// Customer Dashboard
require_once __DIR__ . '/_layout.php';
require_once __DIR__ . '/pickup_functions.php';
$uid    = cust_id();
$name   = cust_name($pdo, $uid);
$counts = pickup_counts($pdo, $uid);
$next   = pickup_next($pdo, $uid);
$recent = array_slice(pickup_list($pdo, $uid), 0, 5);

cust_header($pdo, 'Customer dashboard', 'dashboard');
cust_hero('CUSTOMER PORTAL · HELMS', 'Welcome back, <span>' . cust_h($name) . '</span>',
    'Request pickups, follow their progress and keep your delivery plans in one place.',
    '<a class="btn btn-primary" href="pickup_new.php">' . CUST_PLUS . ' Request a Pickup</a>
     <a class="btn btn-ghost" href="pickups.php">View my pickups</a>');
?>
<section class="cu-over">
  <div class="container">
    <?php cust_show_flash(); ?>
    <div class="cu-stat-grid">
      <article class="portal cu-stat"><div class="ico c1"><?= CUST_TRUCK ?></div><h3><?= (int)array_sum($counts) ?></h3><p>Total pickups</p></article>
      <article class="portal cu-stat"><div class="ico c2"><?= CUST_CLOCK ?></div><h3><?= (int)($counts[PK_REQUESTED] ?? 0) ?></h3><p>Awaiting approval</p></article>
      <article class="portal cu-stat"><div class="ico c3"><?= CUST_CHECK ?></div><h3><?= (int)($counts[PK_APPROVED] ?? 0) ?></h3><p>Approved</p></article>
      <article class="portal cu-stat"><div class="ico c4"><?= CUST_X ?></div><h3><?= (int)($counts[PK_CANCELLED] ?? 0) ?></h3><p>Cancelled</p></article>
    </div>
  </div>
</section>

<section class="cu-section">
  <div class="container cu-dash-grid">
    <div class="cu-card">
      <div class="cu-card-head"><h2>Recent pickups</h2><a class="text-link" href="pickups.php">View all →</a></div>
      <?php if ($recent): ?>
      <div class="cu-table-wrap"><table class="cu-table">
        <thead><tr><th>Ref</th><th>Date</th><th>Time</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach ($recent as $p): ?>
          <tr class="cu-click" onclick="location.href='pickup_view.php?id=<?= (int)$p['pickupId'] ?>'">
            <td><strong>#<?= (int)$p['pickupId'] ?></strong></td>
            <td><?= cust_h($p['pickupDate']) ?></td>
            <td><?= cust_h($p['preferredTimeWindow']) ?></td>
            <td><span class="cu-badge cu-<?= cust_status_class($p['status']) ?>"><?= cust_h($p['status']) ?></span></td>
          </tr>
        <?php endforeach; ?>
        </tbody></table></div>
      <?php else: ?>
        <div class="cu-empty">You have not requested any pickups yet.</div>
      <?php endif; ?>
    </div>

    <div class="cu-card cu-next">
      <div class="cu-card-head"><h2>Next pickup</h2></div>
      <?php if ($next): ?>
        <div class="ico c1"><?= CUST_PIN ?></div>
        <div class="cu-next-date"><?= cust_h(date('D, d M Y', strtotime($next['pickupDate']))) ?></div>
        <div class="cu-muted"><?= cust_h($next['preferredTimeWindow']) ?></div>
        <p class="cu-addr-line"><?= cust_h($next['pickupAddress']) ?></p>
        <span class="cu-badge cu-<?= cust_status_class($next['status']) ?>"><?= cust_h($next['status']) ?></span>
        <div style="margin-top:18px"><a class="btn btn-outline" href="pickup_view.php?id=<?= (int)$next['pickupId'] ?>">View details</a></div>
      <?php else: ?>
        <div class="cu-empty">No upcoming pickups.<div style="margin-top:14px"><a class="btn btn-primary" href="pickup_new.php">Request one</a></div></div>
      <?php endif; ?>
    </div>
  </div>
</section>

<section class="cu-section">
  <div class="container">
    <div class="band">
      <div>
        <h2>Need something collected?</h2>
        <p>Choose an address, a date and a time window. Our team reviews your request and assigns a driver.</p>
      </div>
      <div class="bullets">
        <div><?= CUST_CHECK ?>Edit while pending</div>
        <div><?= CUST_CHECK ?>Cancel any time</div>
        <div><?= CUST_CHECK ?>Live status updates</div>
        <div><a class="btn btn-primary" href="pickup_new.php">Request a Pickup</a></div>
      </div>
    </div>
  </div>
</section>
<?php cust_footer(); ?>
