<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';   // starts the session, provides csrf_token() / verify_csrf()


require_once __DIR__ . '/pickup_functions.php';

const CUST_CHECK = '<svg class="i" viewBox="0 0 24 24"><path d="M5 12l5 5 9-10"/></svg>';
const CUST_X     = '<svg class="i" viewBox="0 0 24 24"><path d="M6 6l12 12M18 6L6 18"/></svg>';
const CUST_TRUCK = '<svg class="i" viewBox="0 0 24 24"><path d="M3 7h11v9H3zM14 10h4l3 3v3h-7z"/></svg>';
const CUST_CLOCK = '<svg class="i" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>';
const CUST_PIN   = '<svg class="i" viewBox="0 0 24 24"><path d="M12 21s7-6.2 7-11a7 7 0 10-14 0c0 4.8 7 11 7 11zM12 12a2 2 0 100-4 2 2 0 000 4z"/></svg>';
const CUST_PLUS  = '<svg class="i" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>';

function cust_h($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

/** Roles allowed in the customer portal (login.php stores the role name in the session). */
function cust_is_customer_role(string $role): bool {
    return in_array($role, ['IndividualCustomer', 'CorporateCustomer', 'CUSTOMER', 'Customer'], true);
}

/** Returns the signed-in customer's userId; sends visitors to the right place otherwise. */
function cust_id(): string {
    if (empty($_SESSION['user_id'])) {
        header('Location: ../login.php?portal=customer');
        exit;
    }
    if (!cust_is_customer_role((string)($_SESSION['role'] ?? ''))) {
        header('Location: ../dashboard.php');      // staff / drivers have their own dashboards
        exit;
    }
    return (string)$_SESSION['user_id'];
}

function cust_name(PDO $pdo, string $uid): string {
    if (!empty($_SESSION['full_name'])) return $_SESSION['full_name'];
    $st = $pdo->prepare("SELECT name FROM IndividualCustomer WHERE userId = ?");
    $st->execute([$uid]);
    if ($n = $st->fetchColumn()) return $n;
    $st = $pdo->prepare("SELECT companyName FROM CorporateCustomer WHERE userId = ?");
    $st->execute([$uid]);
    return $st->fetchColumn() ?: 'Customer';
}

function cust_csrf(): string { return csrf_token(); }
function cust_check_csrf(): void {
    if (!verify_csrf()) {
        http_response_code(403);
        exit('Invalid request token. Go back, refresh the page and try again.');
    }
}
function cust_flash(string $type, string $msg): void { $_SESSION['flash'] = [$type, $msg]; }
function cust_show_flash(): void {
    if (!empty($_SESSION['flash'])) {
        [$t, $m] = $_SESSION['flash']; unset($_SESSION['flash']);
        echo '<div class="cu-notice cu-notice-' . cust_h($t) . '" role="status">' . cust_h($m) . '</div>';
    }
}

function cust_status_class(string $s): string {
    return [PK_REQUESTED => 'warning', PK_APPROVED => 'good', PK_SCHEDULED => 'good', PK_COMPLETED => 'good',
            PK_REJECTED => 'danger', PK_CANCELLED => 'danger', PK_FAILED => 'danger'][pickup_status($s)] ?? 'info';
}


function cust_header(PDO $pdo, string $title, string $active = 'pickups', string $bodyClass = 'cu-page'): void {
    $name = cust_name($pdo, cust_id());
    $nav  = fn($key) => $active === $key ? ' style="color:#fff"' : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= cust_h($title) ?> | HELMS</title>
<meta name="description" content="Manage your HELMS pickups - request, view, edit and cancel.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="customer.css?v=<?= filemtime(__DIR__ . '/customer.css') ?>">
</head>
<body class="<?= cust_h($bodyClass) ?>">
<header>
  <div class="container nav">
    <a href="../index.php" class="brand" aria-label="HELMS home">
      <div class="logo">H</div>
      <div><b>HELMS</b><small>Customer Portal</small></div>
    </a>
    <button class="burger" id="burger" aria-label="Menu">
      <svg class="i" viewBox="0 0 24 24" style="width:28px;height:28px"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
    </button>
    <nav class="menu" id="menu">
      <a class="link" href="index.php"<?= $nav('dashboard') ?>>Dashboard</a>
      <a class="link" href="pickups.php"<?= $nav('pickups') ?>>My Pickups</a>
      <a class="link" href="pickup_new.php"<?= $nav('new') ?>>Request a Pickup</a>
      <span class="link cu-who"><?= cust_h($name) ?></span>
      <a class="btn btn-ghost" href="../index.php">Home</a>
      <a class="btn btn-ghost" href="../logout.php">Logout</a>
    </nav>
  </div>
</header>
<?php }


function cust_hero(string $pill, string $titleHtml, string $lead = '', string $ctaHtml = ''): void { ?>
<section class="hero cu-hero">
  <div class="container">
    <span class="pill"><?= cust_h($pill) ?></span>
    <h1><?= $titleHtml ?></h1>
    <?php if ($lead): ?><p class="lead"><?= cust_h($lead) ?></p><?php endif; ?>
    <?php if ($ctaHtml): ?><div class="hero-cta"><?= $ctaHtml ?></div><?php endif; ?>
  </div>
</section>
<?php }

function cust_footer(): void { ?>
<footer class="login-footer cu-footer">
  <span>&copy; <?= date('Y') ?> Henowic Inventors (Pvt) Ltd.</span>
  <span><a href="../index.php">Return to HELMS home</a> &middot; NIBM DSE Final Project</span>
</footer>

<dialog id="cuConfirm" class="cu-dialog">
  <form method="dialog">
    <div class="login-symbol" style="margin-bottom:14px">!</div>
    <h3>Cancel this pickup?</h3>
    <p id="cuConfirmText" class="cu-muted"></p>
    <div class="cu-actions">
      <button class="btn btn-primary" value="ok">Yes, cancel it</button>
      <button class="btn btn-outline" value="no">Keep pickup</button>
    </div>
  </form>
</dialog>
<script src="pickup.js"></script>
</body>
</html>
<?php }
