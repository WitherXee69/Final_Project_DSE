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
cust_header($pdo, 'Edit pickup #' . $id, 'pickups', 'cu-page login-page');
?>
<main class="login-main">
  <section class="login-intro">
    <span class="pill">PICKUP #<?= $id ?> &middot; HELMS</span>
    <h1>Update your<br><span>pickup details.</span></h1>
    <p>You can change the address, date and time window while the request is awaiting approval.</p>
    <div class="staff-note">Once our team approves the pickup, it can no longer be edited, but you can still cancel it from your pickups list.</div>
  </section>

  <section class="login-card" aria-labelledby="formTitle">
    <div class="login-card-head">
      <div class="login-symbol">✎</div>
      <h2 id="formTitle">Edit pickup #<?= $id ?></h2>
      <p>Change the details below and save.</p>
    </div>
    <?php include __DIR__ . '/_pickup_form.php'; ?>
    <div class="login-divider">DON'T WANT TO CHANGE ANYTHING?</div>
    <p class="login-bottom"><a class="text-link" href="pickup_view.php?id=<?= $id ?>">Back to pickup details</a></p>
  </section>
</main>
<?php cust_footer(); ?>
