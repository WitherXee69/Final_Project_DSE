<?php
// CREATE: request a pickup with parcel + receiver details
require_once __DIR__ . '/_layout.php';
$uid = cust_id();
$services = pickup_services($pdo);
$zones    = pickup_zones($pdo);
$errors   = [];
$values   = pickup_clean([]);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cust_check_csrf();
    $values = pickup_clean($_POST);
    $errors = pickup_validate($pdo, $values);
    if (!$errors) {
        $pickupId = requestPickup($pdo, $uid, $values);
        cust_flash('success', "Pickup $pickupId requested. We will confirm it once our team has reviewed it.");
        header('Location: pickup_view.php?id=' . urlencode($pickupId));
        exit;
    }
}
$action = 'pickup_new.php'; $submitLabel = 'Submit pickup request'; $backUrl = 'pickups.php';
cust_header($pdo, 'Request a pickup', 'new');
cust_hero('PICKUP MANAGEMENT', 'Request a <span>pickup</span>',
    'Tell us what to collect, where to collect it and who it is going to.');
?>
<section class="cu-over">
  <div class="container cu-formlayout">
    <div class="cu-card"><?php include __DIR__ . '/_pickup_form.php'; ?></div>
    <div class="cu-side">
      <aside class="cu-card cu-tips">
        <div class="cu-card-head"><h2>How it works</h2></div>
        <ol>
          <li><b>Request</b> Fill in the three sections.</li>
          <li><b>Review</b> Our team approves your request.</li>
          <li><b>Collection</b> A driver collects the parcel.</li>
        </ol>
        <p class="cu-muted">You can edit a request while it is awaiting approval, and cancel it before a driver is assigned.</p>
      </aside>
    </div>
  </div>
</section>
<?php cust_footer(); ?>
