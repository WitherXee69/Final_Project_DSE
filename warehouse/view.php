<?php
require_once __DIR__.'/_layout.php';
$id = $_GET['id'] ?? ''; $p = wh_find($id);
wh_header('Parcel details','parcels');
if (!$p) { ?><section class="wh-panel"><p class="wh-empty">Parcel not found. <a class="text-link" href="parcels.php">Return to parcel register</a>.</p></section><?php wh_footer(); exit; }
if (isset($_GET['created'])): ?><div class="wh-demo-notice">Parcel record created in the temporary demo session.</div><?php endif; ?>
<section class="wh-panel"><div class="wh-panel-head"><div><h2><?= wh_h($p['parcelId']) ?></h2><p class="wh-muted"><?= wh_h($p['description']) ?></p></div><span class="wh-badge <?= wh_status_class($p['status']) ?>"><?= wh_h($p['status']) ?></span></div>
<div class="wh-detail-grid">
<?php $labels=['parcelCategory'=>'Category','serviceType'=>'Service type','intakeMethod'=>'Intake method','isFragile'=>'Fragile','declaredWeight'=>'Declared weight (kg)','confirmedWeight'=>'Confirmed weight (kg)','dimensions'=>'Dimensions','detailsConfirmed'=>'Details confirmed','parcelReceivedDate'=>'Received date','storageLocationCode'=>'Storage location','holdExpiresAt'=>'Hold expires at','failureReason'=>'Failure reason']; foreach($labels as $k=>$label): ?>
<div class="wh-detail"><small><?= wh_h($label) ?></small><strong><?= wh_h(is_bool($p[$k])?($p[$k]?'Yes':'No'):($p[$k]!==''?$p[$k]:'—')) ?></strong></div>
<?php endforeach; ?>
</div><div class="wh-form-actions" style="margin-top:18px"><a class="wh-btn" href="edit.php?id=<?= urlencode($id) ?>">Edit parcel details</a><a class="wh-btn secondary" href="process.php?id=<?= urlencode($id) ?>">Warehouse processing</a><a class="wh-btn secondary" href="parcels.php">Back to register</a></div>
</section>
<?php wh_footer(); ?>
