<?php
require_once __DIR__.'/_layout.php';
$error = '';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $id = strtoupper(trim($_POST['parcelId'] ?? ''));
    if ($id==='' || isset($_SESSION['wh_parcels'][$id])) $error = 'Enter a unique parcel ID.';
    elseif (trim($_POST['description'] ?? '')==='') $error = 'Enter a parcel description.';
    else {
        $p = [
            'parcelId'=>$id,'description'=>trim($_POST['description']),'parcelCategory'=>$_POST['parcelCategory'] ?? 'General',
            'isFragile'=>isset($_POST['isFragile']),'serviceType'=>$_POST['serviceType'] ?? 'Standard',
            'intakeMethod'=>$_POST['intakeMethod'] ?? 'Customer Drop-off','declaredWeight'=>trim($_POST['declaredWeight'] ?? ''),
            'confirmedWeight'=>'','dimensions'=>trim($_POST['dimensions'] ?? ''),'detailsConfirmed'=>false,
            'parcelReceivedDate'=>$_POST['parcelReceivedDate'] ?? date('Y-m-d'),'storageLocationCode'=>'',
            'status'=>'Pending Confirmation','holdExpiresAt'=>'','failureReason'=>''
        ];
        wh_save($p); header('Location: view.php?id='.urlencode($id).'&created=1'); exit;
    }
}
wh_header('Receive parcel','receive');
?>
<section class="wh-panel"><div class="wh-panel-head"><div><h2>Record a parcel arriving at the warehouse</h2><p class="wh-muted">Enter the parcel details supplied at intake. Confirmed weight and storage location can be recorded during processing.</p></div></div>
<?php if ($error): ?><p style="color:#b91c1c;margin-bottom:12px;font-size:.85rem"><?= wh_h($error) ?></p><?php endif; ?>
<form class="wh-form" method="post">
 <div class="wh-field"><label for="parcelId">Parcel ID *</label><input id="parcelId" name="parcelId" required maxlength="30" placeholder="HEN-2026-000126"></div>
 <div class="wh-field"><label for="parcelReceivedDate">Received date *</label><input id="parcelReceivedDate" name="parcelReceivedDate" type="date" value="<?= date('Y-m-d') ?>" required></div>
 <div class="wh-field full"><label for="description">Parcel description *</label><input id="description" name="description" required maxlength="255" placeholder="Brief description of contents"></div>
 <div class="wh-field"><label for="parcelCategory">Parcel category</label><select id="parcelCategory" name="parcelCategory"><option>General</option><option>Electronics</option><option>Fragile</option><option>Documents</option><option>Clothing</option><option>Other</option></select></div>
 <div class="wh-field"><label for="serviceType">Delivery service</label><select id="serviceType" name="serviceType"><option>Standard</option><option>Express</option><option>Scheduled</option></select></div>
 <div class="wh-field"><label for="intakeMethod">Intake method</label><select id="intakeMethod" name="intakeMethod"><option>Customer Drop-off</option><option>Driver Pickup</option></select></div>
 <div class="wh-field"><label for="declaredWeight">Declared weight (kg)</label><input id="declaredWeight" name="declaredWeight" type="number" min="0" step="0.01" placeholder="0.00"></div>
 <div class="wh-field"><label for="dimensions">Dimensions</label><input id="dimensions" name="dimensions" placeholder="L x W x H cm"></div>
 <div class="wh-field"><label class="wh-check"><input type="checkbox" name="isFragile" value="1"> Fragile parcel</label><small>Handle with additional care where required.</small></div>
 <div class="wh-form-actions"><button class="wh-btn" type="submit">Save received parcel</button><a class="wh-btn secondary" href="parcels.php">Cancel</a></div>
</form></section>
<?php wh_footer(); ?>
