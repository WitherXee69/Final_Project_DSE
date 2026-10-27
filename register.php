<?php
/**
 * HELMS - Individual customer registration UI
 * UI only: connect validation, account creation and persistence to the backend later.
 */
function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Create an individual customer account | HELMS</title>
<meta name="description" content="Create an individual customer account for the Henowic Enterprise Logistics Management System.">
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
      <div><b>HELMS</b><small>Henowic Inventors (Pvt) Ltd.</small></div>
    </a>
    <nav class="menu login-menu">
      <a class="link" href="index.php#services">Services</a>
      <a class="link" href="index.php#how">How it works</a>
      <a class="btn btn-ghost" href="index.php">Home</a>
    </nav>
  </div>
</header>

<main class="login-main register-main">
  <section class="login-intro">
    <span class="pill">INDIVIDUAL CUSTOMER REGISTRATION &middot; HELMS</span>
    <h1>Your deliveries,<br><span>all in one place.</span></h1>
    <p>Create your personal HELMS customer account to request pickups, track parcels, manage delivery details and keep your logistics activity organised.</p>
    <div class="login-points">
      <div class="login-point"><span class="point-icon">✓</span><span>Track your parcel progress</span></div>
      <div class="login-point"><span class="point-icon">✓</span><span>Keep sender and recipient details handy</span></div>
      <div class="login-point"><span class="point-icon">✓</span><span>Manage your delivery requests</span></div>
      <div class="login-point"><span class="point-icon">✓</span><span>One account for your customer activity</span></div>
    </div>
    <div class="staff-note">This form is for individual customers. Business customers are registered by the Customer Manager, while staff and driver accounts are created through the company’s internal process.</div>
  </section>

  <section class="login-card" aria-labelledby="registerTitle">
    <div class="login-card-head">
      <div class="login-symbol">+</div>
      <h2 id="registerTitle">Create your account</h2>
      <p>Enter your personal details to get started with HELMS.</p>
    </div>

    <!-- UI only: registration does not create or save an account until the backend is connected. -->
    <form class="login-form" id="registerForm" method="post" action="">
      <div class="form-row">
        <label for="fullName">Full name</label>
        <input id="fullName" name="full_name" type="text" placeholder="Enter your full name" autocomplete="name" required>
      </div>

      <div class="form-row">
        <label for="email">Email address</label>
        <input id="email" name="email" type="email" placeholder="you@example.com" autocomplete="email" required>
      </div>

      <div class="form-row">
        <label for="phone">Mobile number</label>
        <input id="phone" name="phone" type="tel" placeholder="+94 7X XXX XXXX" autocomplete="tel" inputmode="tel" required>
      </div>

      <div class="form-row">
        <label for="address">Main pickup address</label>
        <textarea id="address" name="address" rows="3" placeholder="Street, city or town" autocomplete="street-address" required></textarea>
      </div>

      <div class="form-row">
        <label for="password">Create password</label>
        <div class="password-wrap">
          <input id="password" name="password" type="password" placeholder="At least 8 characters" autocomplete="new-password" minlength="8" required>
          <button class="password-toggle" id="togglePassword" type="button" aria-controls="password" aria-pressed="false">Show</button>
        </div>
      </div>

      <div class="form-row">
        <label for="confirmPassword">Confirm password</label>
        <div class="password-wrap">
          <input id="confirmPassword" name="confirm_password" type="password" placeholder="Re-enter your password" autocomplete="new-password" minlength="8" required>
          <button class="password-toggle" id="toggleConfirmPassword" type="button" aria-controls="confirmPassword" aria-pressed="false">Show</button>
        </div>
      </div>

      <label class="remember register-consent">
        <input type="checkbox" name="terms" value="1" required>
        <span>I confirm that the details provided are accurate and agree to the applicable terms and privacy policy.</span>
      </label>

      <div class="login-notice" id="registerNotice" role="status" aria-live="polite">This is a UI-only registration form. No account has been created or saved. Registration will work once the backend is implemented.</div>
      <button class="btn btn-primary login-submit" type="submit">Create individual account <span aria-hidden="true">→</span></button>
    </form>

    <div class="login-divider">ALREADY REGISTERED?</div>
    <p class="login-bottom">Have a HELMS account? <a class="text-link" href="login.php?portal=customer">Sign in instead</a></p>
  </section>
</main>

<footer class="login-footer">
  <span>&copy; <?= date('Y') ?> Henowic Inventors (Pvt) Ltd.</span>
  <span><a href="index.php">Return to HELMS home</a> &middot; NIBM DSE Final Project</span>
</footer>
<script>
(function () {
  const form = document.getElementById('registerForm');
  const notice = document.getElementById('registerNotice');

  function wirePasswordToggle(inputId, buttonId) {
    const input = document.getElementById(inputId);
    const button = document.getElementById(buttonId);
    button.addEventListener('click', function () {
      const visible = input.type === 'password';
      input.type = visible ? 'text' : 'password';
      button.textContent = visible ? 'Hide' : 'Show';
      button.setAttribute('aria-pressed', String(visible));
    });
  }
  wirePasswordToggle('password', 'togglePassword');
  wirePasswordToggle('confirmPassword', 'toggleConfirmPassword');

  form.addEventListener('submit', function (event) {
    event.preventDefault();
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirmPassword');
    if (password.value !== confirmPassword.value) {
      confirmPassword.setCustomValidity('The passwords do not match.');
      confirmPassword.reportValidity();
      confirmPassword.setCustomValidity('');
      return;
    }
    notice.classList.add('show');
  });
})();
</script>
</body>
</html>
