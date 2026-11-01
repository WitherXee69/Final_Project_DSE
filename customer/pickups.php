<?php
// READ (list): my pickups with parcel and receiver summary; status tabs and search
require_once __DIR__ . '/_layout.php';
$uid     = cust_id();
$status  = strtoupper((string)($_GET['status'] ?? ''));
$q       = trim((string)($_GET['q'] ?? ''));
$pickups = pickup_list($pdo, $uid, $status, $q);
$counts  = pickup_counts($pdo, $uid);

cust_header($pdo, 'My pickups', 'pickups');
cust_hero('PICKUP MANAGEMENT', 'My <span>pickups</span>',
    'View, edit or cancel your pickup requests.',
    '<a class="btn btn-primary" href="pickup_new.php">' . CUST_PLUS . ' Request a Pickup</a>');
?>
<section class="cu-over">
  <div class="container">
    <?php cust_show_flash(); ?>
    <div class="cu-card">
      <div class="chips cu-tabs">
        <a class="<?= $status === '' ? 'active' : '' ?>" href="?<?= http_build_query(['q' => $q]) ?>"><span>All (<?= (int)array_sum($counts) ?>)</span></a>
        <?php foreach (PK_ALL as $s): if (empty($counts[$s]) && $status !== $s) continue; ?>
          <a class="<?= $status === $s ? 'active' : '' ?>" href="?<?= http_build_query(['status' => $s, 'q' => $q]) ?>"><span><?= cust_h(pickup_label($s)) ?> (<?= (int)($counts[$s] ?? 0) ?>)</span></a>
        <?php endforeach; ?>
      </div>

      <form class="field cu-search" method="get">
        <?php if ($status): ?><input type="hidden" name="status" value="<?= cust_h($status) ?>"><?php endif; ?>
        <input type="search" name="q" value="<?= cust_h($q) ?>" placeholder="Search by reference, parcel, receiver or address">
        <button class="btn btn-primary" type="submit">Search</button>
        <?php if ($q || $status): ?><a class="btn btn-outline" href="pickups.php">Clear</a><?php endif; ?>
      </form>

      <?php if ($pickups): ?>
      <div class="cu-table-wrap"><table class="cu-table">
        <thead><tr><th>Ref</th><th>Pickup</th><th>Parcel</th><th>Receiver</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($pickups as $p): ?>
          <tr>
            <td><strong><?= cust_h($p['pickupId']) ?></strong></td>
            <td><?= cust_h($p['pickupDate'] ?: '–') ?><div class="cu-sub"><?= cust_h($p['preferredTimeWindow']) ?></div><div class="cu-sub cu-addr"><?= cust_h($p['pickupAddress']) ?></div></td>
            <td><?= $p['parcelDescription'] ? cust_h($p['parcelDescription']) : '<span class="cu-sub">–</span>' ?>
                <?php if ($p['parcelService']): ?><div class="cu-sub"><?= cust_h(SERVICE_LABELS[$p['parcelService']] ?? $p['parcelService']) ?></div><?php endif; ?></td>
            <td><?= $p['receiverName'] ? cust_h($p['receiverName']) : '<span class="cu-sub">–</span>' ?></td>
            <td><span class="cu-badge cu-<?= cust_status_class($p['status']) ?>"><?= cust_h(pickup_label($p['status'])) ?></span></td>
            <td><div class="cu-row-actions">
              <a class="btn btn-outline cu-sm" href="pickup_view.php?id=<?= urlencode($p['pickupId']) ?>">View</a>
              <?php if (pickup_can_edit($p)): ?><a class="btn btn-outline cu-sm" href="pickup_edit.php?id=<?= urlencode($p['pickupId']) ?>">Edit</a><?php endif; ?>
              <?php if (pickup_can_cancel($p)): ?>
                <form method="post" action="pickup_cancel.php" data-confirm="Pickup <?= cust_h($p['pickupId']) ?> will be cancelled and permanently deleted, together with its parcel details and receiver. This cannot be undone.">
                  <input type="hidden" name="csrf_token" value="<?= cust_h(cust_csrf()) ?>">
                  <input type="hidden" name="id" value="<?= cust_h($p['pickupId']) ?>">
                  <button class="btn cu-sm cu-danger" type="submit">Cancel</button>
                </form>
              <?php endif; ?>
            </div></td>
          </tr>
        <?php endforeach; ?>
        </tbody></table></div>
      <?php else: ?>
        <div class="cu-empty">
          <?= ($q || $status) ? 'No pickups match your search.' : 'You have not requested any pickups yet.' ?>
          <div style="margin-top:14px"><a class="btn btn-primary" href="pickup_new.php">Request a Pickup</a></div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php cust_footer(); ?>
