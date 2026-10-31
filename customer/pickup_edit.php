<?php
// UPDATE: edit a pickup that is still awaiting approval
require_once __DIR__ . '/_layout.php';
require_once __DIR__ . '/pickup_functions.php';
$uid = cust_id();
$id  = (int)($_GET['id'] ?? 0);
$p   = pickup_get($pdo, $id, $uid);
if (!$p)                  { cust_flash('error', 'Pickup not found.'); header('Location: pickups.php'); exit; }
if (!pickup_can_edit($p)) { cust_flash('error', 'Only pickups awaiting approval can be edited.'); header('Location: pickups.php'); exit; }

$errors = []; $values = $p;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cust_check_csrf();
    $values = array_merge($p, $_POST);
    $errors = pickup_validate($_POST);
    if (!$errors) {
        if (pickup_update($pdo, $id, $uid, $_POST)) cust_flash('success', "Pickup #$id updated.");
        else cust_flash('error', 'Pickup could not be updated (its status may have changed).');
        header('Location: pickups.php');
        exit;
    }
}
$action = 'pickup_edit.php?id=' . $id; $submitLabel = 'Save changes';
cust_header($pdo, 'Edit pickup #' . $id, 'pickups');
cust_page_head('PICKUP MANAGEMENT', 'Edit pickup #' . $id);
?>
<div class="cu-two-col">
  <section class="cu-panel"><?php include __DIR__ . '/_pickup_form.php'; ?></section>
  <aside class="cu-panel cu-tips">
    <h2>Good to know</h2>
    <p class="cu-muted">You can change the address, date and time window while the request is <b>awaiting approval</b>. Once our team approves it, changes are locked, but you can still cancel.</p>
  </aside>
</div>
<?php cust_footer(); ?>
