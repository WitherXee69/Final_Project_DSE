<?php
// CREATE: Request a pickup
require_once __DIR__ . '/_layout.php';
require_once __DIR__ . '/pickup_functions.php';
$uid = cust_id();
$errors = []; $values = $_POST;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cust_check_csrf();
    $errors = pickup_validate($_POST);
    if (!$errors) {
        $id = pickup_create($pdo, $uid, $_POST);
        cust_flash('success', "Pickup #$id requested. We will confirm it once our team has reviewed it.");
        header('Location: pickups.php');
        exit;
    }
}
$action = 'pickup_new.php'; $submitLabel = 'Submit request';
cust_header($pdo, 'Request a pickup', 'new');
cust_page_head('PICKUP MANAGEMENT', 'Request a pickup');
?>
<div class="cu-two-col">
  <section class="cu-panel"><?php include __DIR__ . '/_pickup_form.php'; ?></section>
  <aside class="cu-panel cu-tips">
    <h2>How it works</h2>
    <ol>
      <li><b>Request</b> Tell us where and when to collect.</li>
      <li><b>Review</b> Our customer service team approves your request.</li>
      <li><b>Collection</b> A driver is assigned and collects your parcel.</li>
    </ol>
    <p class="cu-muted">You can edit a request while it is awaiting approval, and cancel it any time before collection.</p>
  </aside>
</div>
<?php cust_footer(); ?>
