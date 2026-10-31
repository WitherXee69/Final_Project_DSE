<?php

require_once __DIR__ . '/../config/database.php';
if (session_status() === PHP_SESSION_NONE) session_start();

const CUST_DEMO_ID       = 1;      
const CUST_REQUIRE_LOGIN = false;  

function cust_h($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }


function cust_id(): int {
    if (!empty($_SESSION['user_id'])) return (int)$_SESSION['user_id'];
    if (CUST_REQUIRE_LOGIN) { header('Location: ../login.php?portal=customer'); exit; }
    return CUST_DEMO_ID;
}
function cust_is_demo(): bool { return empty($_SESSION['user_id']); }


function cust_name(PDO $pdo, int $uid): string {
    if (!empty($_SESSION['full_name'])) return $_SESSION['full_name'];
    $st = $pdo->prepare("SELECT name FROM IndividualCustomer WHERE userId = ?");
    $st->execute([$uid]);
    if ($n = $st->fetchColumn()) return $n;
    $st = $pdo->prepare("SELECT companyName FROM CorporateCustomer WHERE userId = ?");
    $st->execute([$uid]);
    return $st->fetchColumn() ?: 'Customer';
}

function cust_csrf(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
}
function cust_check_csrf(): void {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(403);
        exit('Invalid request token. Go back, refresh the page and try again.');
    }
}
function cust_flash(string $type, string $msg): void { $_SESSION['flash'] = [$type, $msg]; }
function cust_show_flash(): void {
    if (!empty($_SESSION['flash'])) {
        [$t, $m] = $_SESSION['flash']; unset($_SESSION['flash']);
        echo '<div class="cu-alert cu-alert-' . cust_h($t) . '" role="status">' . cust_h($m) . '</div>';
    }
}


function cust_header(PDO $pdo, string $title, string $active = 'pickups'): void {
    $name = cust_name($pdo, cust_id()); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= cust_h($title) ?> | HELMS</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="customer.css">
</head>
<body class="cu-body">
<header>
  <div class="container nav">
    <a href="../index.php" class="brand" aria-label="HELMS home">
      <div class="logo">H</div>
      <div><b>HELMS</b><small>Customer Portal</small></div>
    </a>
    <div class="cu-user"><?= cust_h($name) ?><small>Customer account</small></div>
  </div>
</header>

<div class="cu-shell">
  <aside class="cu-sidebar" aria-label="Customer menu">
    <div class="cu-side-label">MY ACCOUNT</div>
    <a href="index.php" class="<?= $active === 'dashboard' ? 'active' : '' ?>"><span class="cu-ico">▦</span> Dashboard</a>
    <a href="pickups.php" class="<?= $active === 'pickups' ? 'active' : '' ?>"><span class="cu-ico">☰</span> My pickups</a>
    <a href="pickup_new.php" class="<?= $active === 'new' ? 'active' : '' ?>"><span class="cu-ico">＋</span> Request a pickup</a>
    <div class="cu-side-bottom"><a href="../index.php">← Back to HELMS home</a></div>
  </aside>

  <main class="cu-main">
    <?php if (cust_is_demo()): ?>
      <div class="cu-demo">Demo mode: sign-in is not connected yet, so you are viewing the sample customer account.</div>
    <?php endif; ?>
<?php }

function cust_footer(): void { ?>
  </main>
</div>
<dialog id="cuConfirm" class="cu-dialog">
  <form method="dialog">
    <h3 id="cuConfirmTitle">Are you sure?</h3>
    <p id="cuConfirmText" class="cu-muted"></p>
    <div class="cu-actions">
      <button class="cu-btn cu-btn-danger" value="ok">Yes, cancel pickup</button>
      <button class="cu-btn cu-btn-light" value="no">Keep it</button>
    </div>
  </form>
</dialog>
<script src="pickup.js"></script>
</body>
</html>
<?php }


function cust_page_head(string $eyebrow, string $title, string $actionHtml = ''): void { ?>
<div class="cu-page-head">
  <div><div class="cu-eyebrow"><?= cust_h($eyebrow) ?></div><h1><?= cust_h($title) ?></h1></div>
  <?php if ($actionHtml) echo '<div class="cu-actions">' . $actionHtml . '</div>'; ?>
</div>
<?php }

function cust_status_class(string $s): string {
    return ['Requested' => 'warning', 'Approved' => 'good', 'Successful' => 'good',
            'Rejected' => 'danger', 'Cancelled' => 'danger', 'Failed' => 'danger'][$s] ?? 'info';
}
