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
<style>
.login-page{min-height:100vh;display:flex;flex-direction:column;background:radial-gradient(900px 500px at 0% 0%,rgba(29,74,133,.18),transparent 65%),var(--bg)}
.login-main{flex:1;display:grid;grid-template-columns:1fr 1fr;max-width:1160px;width:92%;margin:42px auto;gap:54px;align-items:center}
.login-intro{padding:24px 0}
.login-intro .pill{margin-bottom:22px}
.login-intro h1{font-size:clamp(2.2rem,4.2vw,3.5rem);line-height:1.12;letter-spacing:-1.3px;color:var(--navy);font-weight:800}
.login-intro h1 span{color:var(--amber-d)}
.login-intro>p{font-size:1.03rem;color:var(--muted);max-width:490px;margin:20px 0 28px}
.login-points{display:grid;gap:14px;margin-top:30px}
.login-point{display:flex;align-items:center;gap:12px;color:var(--navy);font-size:.92rem;font-weight:700}
.login-point .point-icon{width:34px;height:34px;border-radius:10px;background:#fff3df;color:var(--amber-d);display:grid;place-items:center;flex:none}
.login-card{background:#fff;border:1px solid var(--line);border-radius:24px;padding:clamp(25px,4vw,42px);box-shadow:0 22px 60px rgba(11,31,58,.12)}
.login-card-head{margin-bottom:26px}
.login-symbol{width:54px;height:54px;border-radius:16px;background:var(--navy);color:var(--amber);display:grid;place-items:center;font-size:1.35rem;font-weight:800;margin-bottom:20px}
.login-card h2{font-size:1.65rem;line-height:1.25;color:var(--navy);letter-spacing:-.5px}
.login-card-head p{color:var(--muted);font-size:.9rem;margin-top:8px}
.login-form{display:grid;gap:18px}
.login-form .form-row{display:grid;gap:7px}
.login-form label{font-size:.86rem;font-weight:700;color:var(--navy)}
.login-form input,.login-form select{width:100%;min-width:0;border:1.5px solid var(--line);border-radius:12px;padding:13px 14px;font:inherit;font-size:.93rem;color:var(--ink);background:#fff;outline:none;transition:border-color .2s,box-shadow .2s}
.login-form input:focus,.login-form select:focus{border-color:var(--amber-d);box-shadow:0 0 0 3px rgba(255,159,28,.15)}
.password-wrap{position:relative}
.password-wrap input{padding-right:82px}
.password-toggle{position:absolute;right:10px;top:50%;transform:translateY(-50%);border:0;background:transparent;color:var(--navy-2);font:inherit;font-size:.8rem;font-weight:800;cursor:pointer;padding:6px}
.form-options{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;font-size:.83rem}
.remember{display:flex;align-items:center;gap:8px;color:var(--muted);cursor:pointer}
.remember input{width:16px;height:16px;accent-color:var(--navy);margin:0}
.text-link{color:var(--navy-2);font-weight:800}
.text-link:hover{color:var(--amber-d);text-decoration:underline}
.login-submit{width:100%;margin-top:2px;min-height:48px}
.login-notice{display:none;padding:12px 14px;border-radius:10px;background:#fff3df;color:#704700;font-size:.84rem;border:1px solid #ffe0ad}
.login-notice.show{display:block}
.login-divider{display:flex;align-items:center;gap:12px;color:#8a94a3;font-size:.76rem;margin:20px 0}
.login-divider:before,.login-divider:after{content:"";height:1px;background:var(--line);flex:1}
.login-bottom{text-align:center;color:var(--muted);font-size:.86rem}
.login-footer{padding:18px 4%;display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;color:#7b8797;font-size:.78rem;border-top:1px solid var(--line);background:rgba(255,255,255,.7)}
.login-footer a{color:var(--navy-2);font-weight:700}
.staff-note{padding:12px 14px;border-radius:12px;background:#f0f4fa;color:var(--muted);font-size:.8rem}
@media(max-width:850px){.login-main{grid-template-columns:1fr;max-width:540px;gap:20px;margin:26px auto 36px}.login-intro{padding:10px 0}.login-intro h1{font-size:2.4rem}.login-points{grid-template-columns:1fr 1fr}.login-point{font-size:.8rem;align-items:flex-start}}
@media(max-width:520px){.login-points{grid-template-columns:1fr}.login-card{padding:24px 20px}.login-intro h1{font-size:2.1rem}.login-footer{justify-content:center;text-align:center}}
</style>
</head>
<body class="login-page">
<header>
  <div class="container nav">
    <a href="index.php" class="brand" aria-label="HELMS home">
      <div class="logo">H</div>
      <div><b>HELMS</b><small>Henowic Inventors (Pvt) Ltd.</small></div>
    </a>
    <nav class="menu" style="display:flex">
      <a class="link" href="index.php#services">Services</a>
      <a class="link" href="index.php#how">How it works</a>
      <a class="btn btn-ghost" href="index.php">Home</a>
    </nav>
  </div>
</header>

<main class="login-main">
  <section class="login-intro">
    <span class="pill"><?= h($page['eyebrow']) ?> &middot; HELMS</span>
    <h1>Welcome back.<br><span>Let's get moving.</span></h1>
    <p><?= h($page['description']) ?> HELMS brings parcel delivery and logistics operations together in one secure workspace.</p>
    <div class="login-points">
      <div class="login-point"><span class="point-icon">✓</span><span>One connected logistics platform</span></div>
      <div class="login-point"><span class="point-icon">✓</span><span>Parcel status and delivery updates</span></div>
      <div class="login-point"><span class="point-icon">✓</span><span>Role-based workspace access</span></div>
      <div class="login-point"><span class="point-icon">✓</span><span>Designed for Sri Lankan delivery teams</span></div>
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
        <input id="identity" name="identity" type="text" placeholder="<?= h($page['emailPlaceholder']) ?>" autocomplete="username" required>
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
          <input id="password" name="password" type="password" placeholder="Enter your password" autocomplete="current-password" required>
          <button class="password-toggle" id="togglePassword" type="button" aria-controls="password" aria-pressed="false">Show</button>
        </div>
      </div>

      <div class="form-options">
        <label class="remember"><input type="checkbox" name="remember" value="1"> Remember me</label>
        <a class="text-link" href="forgot_password.php">Forgot password?</a>
      </div>

      <div class="login-notice" id="loginNotice" role="status" aria-live="polite">This is the UI-only login form. Authentication will be connected when the backend is implemented.</div>
      <button class="btn btn-primary login-submit" type="submit">Sign in to HELMS <span aria-hidden="true">→</span></button>
    </form>

    <?php if ($portal === 'customer'): ?>
    <div class="login-divider">NEW TO HELMS?</div>
    <p class="login-bottom">Need a customer account? <a class="text-link" href="register.php?type=individual">Create an account</a></p>
    <?php elseif ($portal === 'staff'): ?>
    <div class="staff-note" style="margin-top:22px">Staff accounts are created by an authorized system administrator. If you need access, contact your supervisor or system administrator.</div>
    <?php else: ?>
    <div class="staff-note" style="margin-top:22px">Driver accounts are provisioned by the company. If you cannot sign in, contact your transport supervisor.</div>
    <?php endif; ?>
  </section>
</main>

<footer class="login-footer">
  <span>&copy; <?= date('Y') ?> Henowic Inventors (Pvt) Ltd.</span>
  <span><a href="index.php">Return to HELMS home</a> &middot; NIBM DSE Final Project</span>
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
