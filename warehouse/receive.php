<?php
require_once __DIR__ . '/_layout.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = strtoupper(trim($_POST['parcelId'] ?? ''));
    $selectedStatus = $_POST['status'] ?? 'RECEIVED_AT_WAREHOUSE';

    if ($id === '' || isset($_SESSION['wh_parcels'][$id])) {
        $error = 'Enter a unique parcel ID.';
    } elseif (trim($_POST['description'] ?? '') === '') {
        $error = 'Enter a parcel description.';
    } elseif (!isset(wh_status_options()[$selectedStatus])) {
        $error = 'Select a valid parcel status.';
    } else {
        $p = [
            'parcelId' => $id,
            'description' => trim($_POST['description']),
            'parcelCategory' => $_POST['parcelCategory'] ?? 'General',
            'isFragile' => isset($_POST['isFragile']),
            'serviceType' => $_POST['serviceType'] ?? 'Standard',
            'intakeMethod' => $_POST['intakeMethod'] ?? 'Customer Drop-off',
            'declaredWeight' => trim($_POST['declaredWeight'] ?? ''),
            'confirmedWeight' => '',
            'dimensions' => trim($_POST['dimensions'] ?? ''),
            'detailsConfirmed' => false,
            'parcelReceivedDate' => $_POST['parcelReceivedDate'] ?? date('Y-m-d'),
            'storageLocationCode' => '',
            'status' => $selectedStatus,
            'holdExpiresAt' => '',
            'failureReason' => '',
        ];
        wh_save($p);
        header('Location: view.php?id=' . urlencode($id) . '&created=1');
        exit;
    }
}

wh_header('Receive parcel', 'receive');
?>
<section class="wh-panel">
    <div class="wh-panel-head"><div><h2>Record a parcel arriving at the warehouse</h2><p class="wh-muted">Enter the parcel details supplied at intake. Confirmed weight and storage location can be recorded during processing.</p></div></div>
    <?php if ($error): ?><p class="wh-error"><?= wh_h($error) ?></p><?php endif; ?>
    <form class="wh-form" method="post">
        <div class="wh-field"><label for="parcelId">Parcel ID *</label><input id="parcelId" name="parcelId" required maxlength="30" placeholder="HEN-2026-000126"></div>
        <div class="wh-field"><label for="parcelReceivedDate">Received date *</label><input id="parcelReceivedDate" name="parcelReceivedDate" type="date" value="<?= date('Y-m-d') ?>" required></div>
        <div class="wh-field full"><label for="description">Parcel description *</label><input id="description" name="description" required maxlength="255" placeholder="Brief description of contents"></div>
        <div class="wh-field"><label for="parcelCategory">Parcel category</label><select id="parcelCategory" name="parcelCategory"><option>General</option><option>Electronics</option><option>Fragile</option><option>Documents</option><option>Clothing</option><option>Other</option></select></div>
        <div class="wh-field"><label for="serviceType">Delivery service</label><select id="serviceType" name="serviceType"><option>Standard</option><option>Express</option><option>Scheduled</option></select></div>
        <div class="wh-field"><label for="intakeMethod">Intake method</label><select id="intakeMethod" name="intakeMethod"><option>Customer Drop-off</option><option>Driver Pickup</option></select></div>
        <div class="wh-field"><label for="declaredWeight">Declared weight (kg)</label><input id="declaredWeight" name="declaredWeight" type="number" min="0" step="0.01" placeholder="0.00"></div>
        <div class="wh-field"><label for="dimensions">Dimensions</label><input id="dimensions" name="dimensions" placeholder="L x W x H cm"></div>
        <div class="wh-field"><label for="status">Parcel status *</label><select id="status" name="status" required><?php foreach (wh_status_options() as $value => $label): ?><option value="<?= wh_h($value) ?>" <?= $value === ($_POST['status'] ?? 'RECEIVED_AT_WAREHOUSE') ? 'selected' : '' ?>><?= wh_h($label) ?></option><?php endforeach; ?></select><small>Choose the appropriate status for this parcel record.</small></div>
        <div class="wh-field"><label class="wh-check"><input type="checkbox" name="isFragile" value="1">Fragile parcel</label><small>Handle with additional care where required.</small></div>
        <div class="wh-form-actions"><button class="wh-btn" type="submit">Save received parcel</button><a class="wh-btn secondary" href="parcels.php">Cancel</a></div>
    </form>
</section>
<?php wh_footer(); ?>
