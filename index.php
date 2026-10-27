<?php
/**
 * HELMS - Henowic Enterprise Logistics Management System
 * Home page (index.php) - the first page for customers, staff and drivers.
 *
 * Expected routes (change to match your project):
 *   login.php?portal=customer|staff|driver   -> login form
 *   register.php?type=individual|corporate   -> customer registration (FR-010, FR-015)
 *   track.php?id=XXXX                        -> parcel tracking
 *   dashboard.php                            -> role-based dashboard after login
 */
session_start();

$isLoggedIn = isset($_SESSION['user_id']);
$userName   = $_SESSION['full_name'] ?? '';
$userRole   = $_SESSION['role'] ?? '';

$services = [
    ['Standard Delivery',   'Reliable island-wide domestic parcel delivery.',                      'M3 7h11v9H3zM14 10h4l3 3v3h-7z'],
    ['Express Delivery',    'Priority handling for parcels that cannot wait.',                      'M13 2L4 14h7l-1 8 9-12h-7z'],
    ['Scheduled Pickup',    'We collect from the location and time you choose.',                    'M12 21s7-6.2 7-11a7 7 0 10-14 0c0 4.8 7 11 7 11zM12 12a2 2 0 100-4 2 2 0 000 4z'],
    ['Customer Drop-off',   'Bring your parcel straight to our Colombo headquarters.',              'M3 10l9-6 9 6v10H3zM9 20v-6h6v6'],
    ['Corporate Logistics', 'Business accounts with agreements, invoices and billing views.',       'M4 21V7h9v14M13 11h7v10M7 10h3M7 14h3M7 18h3'],
    ['Cash on Delivery',    'Receiver pays on arrival, recorded by the driver on the spot.',       'M3 6h18v12H3zM12 15a3 3 0 100-6 3 3 0 000 6z'],
    ['Warehouse Storage',   'Temporary secure storage while parcels await dispatch.',              'M3 9l9-5 9 5v11H3zM8 20v-6h8v6'],
    ['Return Handling',     'Failed deliveries are returned to the warehouse and processed.',      'M9 14L4 9l5-5M4 9h11a5 5 0 010 10h-3'],
];

$staffRoles = [
    'Managing Director', 'Customer Service', 'Warehouse', 'Transport',
    'Finance', 'System Admin',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>HELMS | Henowic Inventors Logistics</title>
<meta name="description" content="Henowic Enterprise Logistics Management System - request pickups, track parcels and manage logistics operations.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<?php if ($isLoggedIn): ?>
<div class="welcome">
  Welcome back, <?= htmlspecialchars($userName) ?> (<?= htmlspecialchars($userRole) ?>) -
  <a href="dashboard.php">Go to your dashboard</a>
</div>
<?php endif; ?>

<header>
  <div class="container nav">
    <a href="index.php" class="brand">
      <div class="logo">H</div>
      <div><b>HELMS</b><small>Henowic Inventors (Pvt) Ltd.</small></div>
    </a>
    <button class="burger" id="burger" aria-label="Menu">
      <svg class="i" viewBox="0 0 24 24" style="width:28px;height:28px"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
    </button>
    <nav class="menu" id="menu">
      <a class="link" href="#portals">Portals</a>
      <a class="link" href="#services">Services</a>
      <a class="link" href="#how">How it works</a>
      <a class="link" href="#contact">Contact</a>
      <?php if ($isLoggedIn): ?>
        <a class="btn btn-primary" href="dashboard.php">Dashboard</a>
        <a class="btn btn-ghost" href="logout.php">Logout</a>
      <?php else: ?>
        <a class="btn btn-ghost" href="login.php">Login</a>
        <a class="btn btn-primary" href="register.php">Register</a>
      <?php endif; ?>
    </nav>
  </div>
</header>

<!-- HERO -->
<section class="hero">
  <div class="container hero-grid">
    <div>
      <span class="pill">Island-wide domestic logistics &middot; Sri Lanka</span>
      <h1>Send it. Track it. <span>Delivered.</span></h1>
      <p class="lead">One connected platform for pickups, parcels, warehouse, drivers and payments. Customers book and track online, and our teams run every step from a single system.</p>
      <div class="hero-cta">
        <a href="<?= $isLoggedIn ? 'pickup_new.php' : 'register.php' ?>" class="btn btn-primary">
          <svg class="i" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg> Request a Pickup
        </a>
        <a href="#portals" class="btn btn-ghost">Choose your portal</a>
      </div>
    </div>

    <!-- Public tracking -->
    <div class="track">
      <h3>
        <svg class="i" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="M20 20l-4-4"/></svg>
        Track your parcel
      </h3>
      <p>Enter your tracking number to see the latest status.</p>
      <form action="track.php" method="get" class="field" id="trackForm">
        <input type="text" name="id" id="trackId" placeholder="e.g. HEN-2026-000123" required maxlength="30" autocomplete="off">
        <button class="btn btn-primary" type="submit">Track</button>
      </form>
      <div class="steps">
        <div class="step done"><div class="dot"><svg class="i" viewBox="0 0 24 24" style="width:16px;height:16px"><path d="M5 12l5 5 9-10"/></svg></div>Picked up</div>
        <div class="step done"><div class="dot"><svg class="i" viewBox="0 0 24 24" style="width:16px;height:16px"><path d="M5 12l5 5 9-10"/></svg></div>In warehouse</div>
        <div class="step now"><div class="dot"><svg class="i" viewBox="0 0 24 24" style="width:16px;height:16px"><path d="M3 7h11v9H3zM14 10h4l3 3v3h-7z"/></svg></div>Out for delivery</div>
        <div class="step"><div class="dot"><svg class="i" viewBox="0 0 24 24" style="width:16px;height:16px"><path d="M12 21s7-6.2 7-11a7 7 0 10-14 0c0 4.8 7 11 7 11z"/></svg></div>Delivered</div>
      </div>
    </div>
  </div>
</section>

<!-- PORTALS -->
<section class="portals" id="portals">
  <div class="container portal-grid">

    <!-- Customers -->
    <article class="portal">
      <div class="ico c1"><svg class="i" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.6-7 8-7s8 3 8 7"/></svg></div>
      <h3>Customers</h3>
      <p>Individual and corporate customers.</p>
      <ul>
        <li><svg class="i" viewBox="0 0 24 24"><path d="M5 12l5 5 9-10"/></svg>Request and manage pickups &amp; deliveries</li>
        <li><svg class="i" viewBox="0 0 24 24"><path d="M5 12l5 5 9-10"/></svg>Track delivery status in real time</li>
        <li><svg class="i" viewBox="0 0 24 24"><path d="M5 12l5 5 9-10"/></svg>Corporate users can view agreements</li>
      </ul>
      <div class="actions">
        <a class="btn btn-primary" href="login.php?portal=customer">Login</a>
        <a class="btn btn-outline" href="register.php?type=individual">Register</a>
      </div>
    </article>

    <!-- Staff -->
    <article class="portal">
      <div class="ico c2"><svg class="i" viewBox="0 0 24 24"><rect x="3" y="7" width="18" height="13" rx="2"/><path d="M9 7V5a2 2 0 012-2h2a2 2 0 012 2v2M3 13h18"/></svg></div>
      <h3>Staff &amp; Management</h3>
      <p>Secure access for Henowic employees.</p>
      <div class="chips">
        <?php foreach ($staffRoles as $r): ?><span><?= htmlspecialchars($r) ?></span><?php endforeach; ?>
      </div>
      <div class="actions">
        <a class="btn btn-primary" href="login.php?portal=staff">Staff Login</a>
      </div>
    </article>

    <!-- Drivers -->
    <article class="portal">
      <div class="ico c3"><svg class="i" viewBox="0 0 24 24"><path d="M3 7h11v9H3zM14 10h4l3 3v3h-7z"/><circle cx="7" cy="18" r="1.6"/><circle cx="17" cy="18" r="1.6"/></svg></div>
      <h3>Drivers</h3>
      <p>Your daily work, simplified.</p>
      <ul>
        <li><svg class="i" viewBox="0 0 24 24"><path d="M5 12l5 5 9-10"/></svg>View assigned manifests &amp; parcels</li>
        <li><svg class="i" viewBox="0 0 24 24"><path d="M5 12l5 5 9-10"/></svg>Update delivery &amp; pickup status</li>
        <li><svg class="i" viewBox="0 0 24 24"><path d="M5 12l5 5 9-10"/></svg>Record COD payments &amp; availability</li>
      </ul>
      <div class="actions">
        <a class="btn btn-primary" href="login.php?portal=driver">Driver Login</a>
      </div>
    </article>

  </div>
</section>

<!-- SERVICES -->
<section class="block" id="services">
  <div class="container">
    <div class="head">
      <small>OUR SERVICES</small>
      <h2>Everything your parcel needs, end to end</h2>
      <p>From the moment you book to the moment it arrives.</p>
    </div>
    <div class="svc-grid">
      <?php foreach ($services as [$title, $desc, $path]): ?>
      <div class="svc">
        <svg class="i" viewBox="0 0 24 24"><path d="<?= $path ?>"/></svg>
        <h4><?= htmlspecialchars($title) ?></h4>
        <p><?= htmlspecialchars($desc) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- HOW IT WORKS -->
<section class="block how" id="how">
  <div class="container">
    <div class="head">
      <small>HOW IT WORKS</small>
      <h2>Five simple steps from booking to doorstep</h2>
    </div>
    <div class="flow">
      <div class="f"><h4>Book</h4><p>Register and create a pickup or delivery request.</p></div>
      <div class="f"><h4>Collect</h4><p>A driver and vehicle are assigned to pick up your parcel.</p></div>
      <div class="f"><h4>Store</h4><p>Parcel is received and located in our Colombo warehouse.</p></div>
      <div class="f"><h4>Deliver</h4><p>Dispatched via a delivery manifest to the receiver.</p></div>
      <div class="f"><h4>Pay &amp; Receipt</h4><p>Invoices, COD and receipts are recorded automatically.</p></div>
    </div>

    <div class="band" style="margin-top:64px">
      <div>
        <h2>One system. Every department connected.</h2>
        <p>HELMS replaces scattered spreadsheets, paper forms and chat messages with a single, role-based platform so everyone sees the same, up-to-date information.</p>
      </div>
      <div class="bullets">
        <div><svg class="i" viewBox="0 0 24 24"><path d="M5 12l5 5 9-10"/></svg>Role-based access</div>
        <div><svg class="i" viewBox="0 0 24 24"><path d="M5 12l5 5 9-10"/></svg>Live parcel status</div>
        <div><svg class="i" viewBox="0 0 24 24"><path d="M5 12l5 5 9-10"/></svg>Warehouse locations</div>
        <div><svg class="i" viewBox="0 0 24 24"><path d="M5 12l5 5 9-10"/></svg>Invoices &amp; receipts</div>
        <div><svg class="i" viewBox="0 0 24 24"><path d="M5 12l5 5 9-10"/></svg>Driver manifests</div>
        <div><svg class="i" viewBox="0 0 24 24"><path d="M5 12l5 5 9-10"/></svg>Management reports</div>
      </div>
    </div>
  </div>
</section>

<!-- FOOTER -->
<footer id="contact">
  <div class="container">
    <div class="f-grid">
      <div>
        <div class="brand" style="margin-bottom:14px"><div class="logo">H</div><div><b>HELMS</b><small>Henowic Inventors (Pvt) Ltd.</small></div></div>
        <p style="font-size:.88rem">Reliable, organized domestic parcel delivery across Sri Lanka.</p>
      </div>
      <div>
        <h5>Portals</h5>
        <a href="login.php?portal=customer">Customer Login</a>
        <a href="register.php">Register</a>
        <a href="login.php?portal=staff">Staff Login</a>
        <a href="login.php?portal=driver">Driver Login</a>
      </div>
      <div>
        <h5>Services</h5>
        <a href="#services">Standard &amp; Express</a>
        <a href="#services">Pickup &amp; Drop-off</a>
        <a href="#services">Corporate Logistics</a>
        <a href="track.php">Track a Parcel</a>
      </div>
      <div>
        <h5>Head Office</h5>
        <li>Colombo, Sri Lanka</li>
        <li>+94 XX XXX XXXX</li>
        <li>info@henowic.lk</li>
      </div>
    </div>
    <div class="copy">
      <span>&copy; <?= date('Y') ?> Henowic Inventors (Pvt) Ltd. All rights reserved.</span>
      <span>HELMS &middot; NIBM DSE Final Project</span>
    </div>
  </div>
</footer>

<script src="assets/js/main.js"></script>
</body>
</html>
