<?php
// UPDATE: edit a pickup (and its parcel / receiver) that is still awaiting approval
require_once __DIR__ . '/_layout.php';
$uid = cust_id();
$id  = (string)($_GET['id'] ?? '');
$p   = pickup_details($pdo, $id, $uid);
if (!$p)                  { cust_flash('error', 'Pickup not found.'); header('Location: pickups.php'); exit; }
if (!pickup_can_edit($p)) { cust_flash('error', 'Only pickups awaiting approval can be edited.'); header('Location: pickup_view.php?id=' . urlencode($id)); exit; }

$services = pickup_services($pdo);
$zones    = pickup_zones($pdo);
$errors   = [];
$values   = pickup_form_values($p);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cust_check_csrf();
    $values = pickup_clean($_POST);
    $errors = pickup_validate($pdo, $values);
    if (!$errors) {
        if (updatePickup($pdo, $id, $uid, $values)) cust_flash('success', "Pickup $id updated.");
        else cust_flash('error', 'Pickup could not be updated (its status may have changed).');
        header('Location: pickup_view.php?id=' . urlencode($id));
        exit;
    }
}
$action = 'pickup_edit.php?id=' . urlencode($id); $submitLabel = 'Save changes'; $backUrl = 'pickup_view.php?id=' . urlencode($id);
cust_header($pdo, 'Edit pickup ' . $id, 'pickups');
cust_hero('PICKUP MANAGEMENT', 'Edit pickup <span>' . cust_h($id) . '</span>',
    'Changes are allowed while the request is awaiting approval.');
?>
<section class="cu-over">
  <div class="container cu-formlayout">
    <div class="cu-card"><?php include __DIR__ . '/_pickup_form.php'; ?></div>
    <div class="cu-side">
      <aside class="cu-card cu-tips">
        <div class="cu-card-head"><h2>Good to know</h2></div>
        <p class="cu-muted">Once our team approves the pickup it can no longer be edited, but you can still cancel it until a driver is assigned.</p>
      </aside>
    </div>
  </div>
</section>
<?php cust_footer(); ?>
