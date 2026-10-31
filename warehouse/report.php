<?php
require_once __DIR__.'/_layout.php';
$role=$_SESSION['role']??'Warehouse Officer';
if(stripos($role,'manager')===false){http_response_code(403);wh_header('Access restricted');?><section class="wh-panel"><p class="wh-empty">This report is available to Warehouse Managers only.</p></section><?php wh_footer();exit;}
$w=$_SESSION['wh_warehouse'];$parcels=wh_parcels();$byStatus=[];
foreach($parcels as $p){$s=$p['status'];$byStatus[$s]=($byStatus[$s]??0)+1;}
$used=(int)$w['currentLoad'];$cap=(int)$w['capacity'];$pct=$cap?round($used/$cap*100):0;
wh_header('Storage report','report');
?>
<section class="wh-panel"><div class="wh-panel-head"><div><h2><?= wh_h($w['warehouseName']) ?></h2><p class="wh-muted">Warehouse ID: <?= wh_h($w['assignedWarehouseId']) ?> · Zone: <?= wh_h($w['zoneCode']) ?></p></div><span class="wh-role-pill">Manager report</span></div>
<div class="wh-grid" style="grid-template-columns:repeat(3,minmax(0,1fr))"><div class="wh-card"><div class="wh-stat-label">Total capacity</div><div class="wh-stat-value"><?= $cap ?></div></div><div class="wh-card"><div class="wh-stat-label">Current load</div><div class="wh-stat-value"><?= $used ?></div></div><div class="wh-card"><div class="wh-stat-label">Capacity used</div><div class="wh-stat-value"><?= $pct ?>%</div></div></div>
<h2 style="font-size:1rem;color:#0b1f3a;margin:12px 0">Parcel status summary</h2>
<div class="wh-table-wrap"><table class="wh-table"><thead><tr><th>Status</th><th>Parcel count</th></tr></thead><tbody><?php foreach($byStatus as $s=>$n): ?><tr><td><?= wh_h($s) ?></td><td><strong><?= $n ?></strong></td></tr><?php endforeach; ?></tbody></table></div>
<p class="wh-muted" style="margin-top:14px">This is a demonstration report calculated from the temporary session records. Capacity and current-load figures are sample values from the diagram fields and must be connected to the actual Warehouse table.</p>
</section>
<?php wh_footer(); ?>
