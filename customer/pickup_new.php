<?php
// CREATE: Request a pickup  (layout follows register.php: intro on the left, form card on the right)
require_once __DIR__ . '/_layout.php';
require_once __DIR__ . '/pickup_functions.php';
$uid = cust_id();
$errors = []; $values = $_POST;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cust_check_csrf();
    $errors = pickup_validate($_POST);
    if (!$errors) {
        $id = pickup_create($pdo, $uid, $_POST);
        cust_flash('success', "Pickup $id requested. We will confirm it once our team has reviewed it.");
        header('Location: pickups.php');
        exit;
    }
}
$action = 'pickup_new.php'; $submitLabel = 'Submit pickup request';
cust_header($pdo, 'Request a pickup', 'new', 'cu-page login-page');
?>
<main class="login-main">
  <section class="login-intro">
    <span class="pill">PICKUP MANAGEMENT &middot; HELMS</span>
    <h1>Book a pickup.<br><span>We'll handle the rest.</span></h1>
    <p>Tell us where and when to collect your parcel. Our customer service team reviews the request and arranges a driver.</p>
    <div class="login-points">
      <div class="login-point"><span class="point-icon">1</span><span>Request: choose the address, date and time</span></div>
      <div class="login-point"><span class="point-icon">2</span><span>Review: our team approves your request</span></div>
      <div class="login-point"><span class="point-icon">3</span><span>Collection: a driver picks up your parcel</span></div>
      <div class="login-point"><span class="point-icon">✓</span><span>Edit or cancel while it is still pending</span></div>
    </div>
  </section>

  <section class="login-card" aria-labelledby="formTitle">
    <div class="login-card-head">
      <div class="login-symbol">+</div>
      <h2 id="formTitle">Request a pickup</h2>
      <p>Enter the collection details below.</p>
    </div>
    <?php include __DIR__ . '/_pickup_form.php'; ?>
    <div class="login-divider">CHANGED YOUR MIND?</div>
    <p class="login-bottom"><a class="text-link" href="pickups.php">Back to my pickups</a></p>
  </section>
</main>
<?php cust_footer(); ?>
