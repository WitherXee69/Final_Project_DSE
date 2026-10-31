<?php
// READ (list): My pickups, with search and status tabs
require_once __DIR__ . '/_layout.php';
require_once __DIR__ . '/pickup_functions.php';
$uid    = cust_id();
$status = $_GET['status'] ?? '';
$q      = trim($_GET['q'] ?? '');
$pickups = pickup_list($pdo, $uid, $status, $q);
$counts  = pickup_counts($pdo, $uid);

cust_header($pdo, 'My pickups', 'pickups');
cust_page_head('PICKUP MANAGEMENT', 'My pickups', '<a class="cu-btn cu-btn-primary" href="pickup_new.php">＋ Request a pickup</a>');
cust_show_flash();
?>
<section class="cu-panel">
  <div class="cu-tabs" role="tablist">
    <a class="<?= $status === '' ? 'active' : '' ?>" href="?<?= http_build_query(['q' => $q]) ?>">All <span><?= (int)array_sum($counts) ?></span></a>
    <?php foreach (PK_ALL as $s): if (empty($counts[$s]) && $status !== $s) continue; ?>
      <a class="<?= $status === $s ? 'active' : '' ?>" href="?<?= http_build_query(['status' => $s, 'q' => $q]) ?>"><?= cust_h($s) ?> <span><?= (int)($counts[$s] ?? 0) ?></span></a>
    <?php endforeach; ?>
  </div>

  <form class="cu-search" method="get">
    <?php if ($status): ?><input type="hidden" name="status" value="<?= cust_h($status) ?>"><?php endif; ?>
    <input type="search" name="q" value="<?= cust_h($q) ?>" placeholder="Search by reference number or address">
    <button class="cu-btn cu-btn-light" type="submit">Search</button>
    <?php if ($q || $status): ?><a class="cu-btn cu-btn-light" href="pickups.php">Clear</a><?php endif; ?>
  </form>

  <?php if ($pickups): ?>
  <div class="cu-table-wrap"><table class="cu-table">
    <thead><tr><th>Ref</th><th>Pickup date</th><th>Time window</th><th>Address</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach ($pickups as $p): ?>
      <tr>
        <td><strong>#<?= (int)$p['pickupId'] ?></strong></td>
        <td><?= cust_h($p['pickupDate']) ?></td>
        <td><?= cust_h($p['preferredTimeWindow']) ?></td>
        <td class="cu-addr"><?= cust_h($p['pickupAddress']) ?></td>
        <td><span class="cu-badge cu-<?= cust_status_class($p['status']) ?>"><?= cust_h($p['status']) ?></span></td>
        <td><div class="cu-row-actions">
          <a class="cu-btn cu-btn-sm cu-btn-light" href="pickup_view.php?id=<?= (int)$p['pickupId'] ?>">View</a>
          <?php if (pickup_can_edit($p)): ?><a class="cu-btn cu-btn-sm cu-btn-light" href="pickup_edit.php?id=<?= (int)$p['pickupId'] ?>">Edit</a><?php endif; ?>
          <?php if (pickup_can_cancel($p)): ?>
            <form method="post" action="pickup_cancel.php" data-confirm="Pickup #<?= (int)$p['pickupId'] ?> will be cancelled. This cannot be undone.">
              <input type="hidden" name="csrf" value="<?= cust_h(cust_csrf()) ?>">
              <input type="hidden" name="id" value="<?= (int)$p['pickupId'] ?>">
              <button class="cu-btn cu-btn-sm cu-btn-danger" type="submit">Cancel</button>
            </form>
          <?php endif; ?>
        </div></td>
      </tr>
    <?php endforeach; ?>
    </tbody></table></div>
  <?php else: ?>
    <div class="cu-empty">
      <?= ($q || $status) ? 'No pickups match your search.' : 'You have not requested any pickups yet.' ?>
      <div style="margin-top:14px"><a class="cu-btn cu-btn-primary" href="pickup_new.php">＋ Request a pickup</a></div>
    </div>
  <?php endif; ?>
</section>
<?php cust_footer(); ?>
