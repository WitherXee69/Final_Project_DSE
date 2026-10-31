<?php
/**
 * HELMS - Login UI
 * UI-only starter page. Connect the form to your authentication backend later.
 */
$portal = strtolower($_GET['portal'] ?? 'customer');
$portalConfig = [
    'customer' => [
        'title' => 'Customer login',
        'eyebrow' => 'CUSTOMER PORTAL',
        'description' => 'Manage your pickups, follow parcel progress and keep your delivery details in one place.',
        'emailLabel' => 'Email address',
        'emailPlaceholder' => 'you@example.com',
        'backLabel' => 'Back to customer portal',
        'icon' => 'C',
    ],
    'staff' => [
        'title' => 'Staff & management login',
        'eyebrow' => 'TEAM ACCESS',
        'description' => 'Sign in to access the tools and information for your department.',
        'emailLabel' => 'Work email or username',
        'emailPlaceholder' => 'Enter your work email or username',
        'backLabel' => 'Back to staff portal',
        'icon' => 'S',
    ],
    'driver' => [
        'title' => 'Driver login',
        'eyebrow' => 'DRIVER PORTAL',
        'description' => 'View your assigned work, manage delivery stops and record delivery outcomes.',
        'emailLabel' => 'Driver email or username',
        'emailPlaceholder' => 'Enter your driver email or username',
        'backLabel' => 'Back to driver portal',
        'icon' => 'D',
    ],
];
if (!isset($portalConfig[$portal])) {
    $portal = 'customer';
}
$page = $portalConfig[$portal];
$staffRoles = [
    'Managing Director', 'Customer Service', 'Warehouse',
    'Transport', 'Finance', 'System Admin',
];
function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= h($page['title']) ?> | HELMS</title>
  <meta name="description" content="Sign in to the Henowic Enterprise Logistics Management System.">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-page">
<header>
  <div class="container nav">
    <a href="index.php" class="brand" aria-label="HELMS home">
      <div class="logo">H</div>
      <div>
        <b>HELMS</b>
        <small>Henowic Inventors (Pvt) Ltd.</small>
      </div>
    </a>
    <nav class="menu login-menu">
      <a class="link" href="index.php#services">Services</a>
      <a class="link" href="index.php#how">How it works</a>
      <a class="btn btn-ghost" href="index.php">Home</a>
    </nav>
  </div>
</header>

<main class="login-main">
  <section class="login-intro">
    <span class="pill"><?= h($page['eyebrow']) ?> &middot; HELMS</span>
    <h1>
      Welcome back.<br>
      <span>Let's get moving.</span>
    </h1>
    <p><?= h($page['description']) ?> HELMS brings parcel delivery and logistics operations together in one secure workspace.</p>
    <div class="login-points">
      <div class="login-point">
        <span class="point-icon">✓</span>
        <span>One connected logistics platform</span>
      </div>
      <div class="login-point">
        <span class="point-icon">✓</span>
        <span>Parcel status and delivery updates</span>
      </div>
      <div class="login-point">
        <span class="point-icon">✓</span>
        <span>Role-based workspace access</span>
      </div>
      <div class="login-point">
        <span class="point-icon">✓</span>
        <span>Designed for Sri Lankan delivery teams</span>
      </div>
    </div>
  </section>

  <section class="login-card" aria-labelledby="loginTitle">
    <div class="login-card-head">
      <div class="login-symbol"><?= h($page['icon']) ?></div>
      <h2 id="loginTitle"><?= h($page['title']) ?></h2>
      <p>Enter your account details to continue.</p>
    </div>

    <!-- UI only: connect this form to your authentication backend later. -->
    <form class="login-form" id="loginForm" method="post" action="">
      <input type="hidden" name="portal" value="<?= h($portal) ?>">
      <div class="form-row">
        <label for="identity"><?= h($page['emailLabel']) ?></label>
        <input
          id="identity"
          name="identity"
          type="text"
          placeholder="<?= h($page['emailPlaceholder']) ?>"
          autocomplete="username"
          required
        >
      </div>

      <?php if ($portal === 'staff'): ?>
      <div class="form-row">
        <label for="staffRole">Department / staff role</label>
        <select id="staffRole" name="staff_role" required>
          <option value="">Select your role</option>
          <?php foreach ($staffRoles as $role): ?>
          <option value="<?= h($role) ?>"><?= h($role) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <?php endif; ?>

      <div class="form-row">
        <label for="password">Password</label>
        <div class="password-wrap">
          <input
            id="password"
            name="password"
            type="password"
            placeholder="Enter your password"
            autocomplete="current-password"
            required
          >
          <button
            class="password-toggle"
            id="togglePassword"
            type="button"
            aria-controls="password"
            aria-pressed="false"
          >Show</button>
        </div>
      </div>

      <div class="form-options">
        <label class="remember">
          <input type="checkbox" name="remember" value="1">
          Remember me
        </label>
        <a class="text-link" href="forgot_password.php">Forgot password?</a>
      </div>

      <div class="login-notice" id="loginNotice" role="status" aria-live="polite">This is the UI-only login form. Authentication will be connected when the backend is implemented.</div>
      <button class="btn btn-primary login-submit" type="submit">Sign in to HELMS <span aria-hidden="true">→</span></button>
    </form>

    <?php if ($portal === 'customer'): ?>
    <div class="login-divider">NEW TO HELMS?</div>
    <p class="login-bottom">
      Need a customer account?
      <a class="text-link" href="register.php?type=individual">Create an account</a>
    </p>
    <?php elseif ($portal === 'staff'): ?>
    <div class="staff-note">Staff accounts are created by an authorized system administrator. If you need access, contact your supervisor or system administrator.</div>
    <?php else: ?>
    <div class="staff-note" style="margin-top:22px">
      Driver accounts are provisioned by the company. If you cannot sign in, contact your transport supervisor.
    </div>
    <?php endif; ?>
  </section>
</main>

<footer class="login-footer">
  <span>&copy; <?= date('Y') ?> Henowic Inventors (Pvt) Ltd.</span>
  <span>
    <a href="index.php">Return to HELMS home</a>
    &middot; NIBM DSE Final Project
  </span>
</footer>
<script>
(function () {
  const password = document.getElementById('password');
  const toggle = document.getElementById('togglePassword');
  const form = document.getElementById('loginForm');
  const notice = document.getElementById('loginNotice');

  toggle.addEventListener('click', function () {
    const visible = password.type === 'password';
    password.type = visible ? 'text' : 'password';
    toggle.textContent = visible ? 'Hide' : 'Show';
    toggle.setAttribute('aria-pressed', String(visible));
  });

  // Prevent fake authentication until the backend is connected.
  form.addEventListener('submit', function (event) {
    event.preventDefault();
    notice.classList.add('show');
  });
})();
</script>
</body>
</html>
