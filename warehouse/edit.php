<?php
require_once __DIR__ . '/_layout.php';

$id = $_GET['id'] ?? '';
$p = wh_find($id);
$error = '';

if (!$p) {
    wh_header('Edit parcel', 'parcels');
    ?><section class="wh-panel"><p class="wh-empty">Parcel not found.</p></section><?php
    wh_footer();
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedStatus = $_POST['status'] ?? '';
    $p['description'] = trim($_POST['description'] ?? '');
    $p['parcelCategory'] = $_POST['parcelCategory'] ?? $p['parcelCategory'];
    $p['isFragile'] = isset($_POST['isFragile']);
    $p['serviceType'] = $_POST['serviceType'] ?? $p['serviceType'];
    $p['intakeMethod'] = $_POST['intakeMethod'] ?? $p['intakeMethod'];
    $p['declaredWeight'] = trim($_POST['declaredWeight'] ?? '');
    $p['confirmedWeight'] = trim($_POST['confirmedWeight'] ?? '');
    $p['dimensions'] = trim($_POST['dimensions'] ?? '');

    if ($p['description'] === '') {
        $error = 'Description cannot be empty.';
    } elseif (!isset(wh_status_options()[$selectedStatus])) {
        $error = 'Select a valid parcel status.';
    } else {
        $p['status'] = $selectedStatus;
        wh_save($p);
        header('Location: view.php?id=' . urlencode($id) . '&updated=1');
        exit;
    }
}

$currentStatus = wh_normalize_status($p['status']);
wh_header('Edit parcel details', 'parcels');
?>
<section class="wh-panel">
    <div class="wh-panel-head"><div><h2>Update <?= wh_h($id) ?></h2><p class="wh-muted">Parcel ID is treated as the record identifier and cannot be changed here.</p></div></div>
    <?php if ($error): ?><p class="wh-error"><?= wh_h($error) ?></p><?php endif; ?>
    <form class="wh-form" method="post">
        <div class="wh-field full"><label>Description *</label><input name="description" required value="<?= wh_h($p['description']) ?>"></div>
        <div class="wh-field"><label>Category</label><select name="parcelCategory"><?php foreach (['General', 'Electronics', 'Fragile', 'Documents', 'Clothing', 'Other'] as $v): ?><option value="<?= wh_h($v) ?>" <?= $p['parcelCategory'] === $v ? 'selected' : '' ?>><?= wh_h($v) ?></option><?php endforeach; ?></select></div>
        <div class="wh-field"><label>Service type</label><select name="serviceType"><?php foreach (['Standard', 'Express', 'Scheduled'] as $v): ?><option value="<?= wh_h($v) ?>" <?= $p['serviceType'] === $v ? 'selected' : '' ?>><?= wh_h($v) ?></option><?php endforeach; ?></select></div>
        <div class="wh-field"><label>Intake method</label><select name="intakeMethod"><?php foreach (['Customer Drop-off', 'Driver Pickup'] as $v): ?><option value="<?= wh_h($v) ?>" <?= $p['intakeMethod'] === $v ? 'selected' : '' ?>><?= wh_h($v) ?></option><?php endforeach; ?></select></div>
        <div class="wh-field"><label>Declared weight (kg)</label><input type="number" min="0" step=".01" name="declaredWeight" value="<?= wh_h($p['declaredWeight']) ?>"></div>
        <div class="wh-field"><label>Confirmed weight (kg)</label><input type="number" min="0" step=".01" name="confirmedWeight" value="<?= wh_h($p['confirmedWeight']) ?>"></div>
        <div class="wh-field"><label>Dimensions</label><input name="dimensions" value="<?= wh_h($p['dimensions']) ?>"></div>
        <div class="wh-field"><label>Parcel status *</label><select name="status" required><?php foreach (wh_status_options() as $value => $label): ?><option value="<?= wh_h($value) ?>" <?= $currentStatus === $value ? 'selected' : '' ?>><?= wh_h($label) ?></option><?php endforeach; ?></select><small>Available statuses follow the HELMS parcel lifecycle.</small></div>
        <div class="wh-field"><label class="wh-check"><input type="checkbox" name="isFragile" <?= $p['isFragile'] ? 'checked' : '' ?>>Fragile parcel</label></div>
        <div class="wh-form-actions"><button class="wh-btn" type="submit">Save changes</button><a class="wh-btn secondary" href="view.php?id=<?= urlencode($id) ?>">Cancel</a></div>
    </form>
</section>
<?php wh_footer(); ?>
