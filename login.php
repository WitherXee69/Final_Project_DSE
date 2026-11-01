<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';

if (!empty($_SESSION['user_id'])) {
    redirect_to_dashboard();
}

$portal = strtolower($_GET['portal'] ?? $_POST['portal'] ?? 'customer');

$portals = [
    'customer' => [
        'title' => 'Customer login',
        'eyebrow' => 'CUSTOMER PORTAL',
        'description' => 'Manage your pickups, follow parcel progress and keep your delivery details in one place.',
        'emailLabel' => 'Email address',
        'emailPlaceholder' => 'you@example.com',
        'icon' => 'C'
    ],
    'staff' => [
        'title' => 'Staff & management login',
        'eyebrow' => 'TEAM ACCESS',
        'description' => 'Sign in to access the tools and information for your department.',
        'emailLabel' => 'Work username',
        'emailPlaceholder' => 'Enter your work username',
        'icon' => 'S'
    ],
    'driver' => [
        'title' => 'Driver login',
        'eyebrow' => 'DRIVER PORTAL',
        'description' => 'View your assigned work and manage delivery stops.',
        'emailLabel' => 'Driver username',
        'emailPlaceholder' => 'Enter your driver username',
        'icon' => 'D'
    ]
];

if (!isset($portals[$portal])) {
    $portal = 'customer';
}

$page = $portals[$portal];
$error = '';
$identity = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identity = trim($_POST['identity'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!verify_csrf()) {
        $error = 'Your session has expired. Please refresh the page and try again.';
    } elseif ($identity === '' || $password === '') {
        $error = 'Please enter your username and password.';
    } else {
        $sql = "
            SELECT
                u.userId,
                u.username,
                u.passwordHash,
                u.role,
                COALESCE(
                    NULLIF(ic.name, ''),
                    NULLIF(cc.companyName, ''),
                    u.username
                ) AS fullName,
                c.email
            FROM User u
            LEFT JOIN Customer c ON c.userId = u.userId
            LEFT JOIN IndividualCustomer ic ON ic.userId = u.userId
            LEFT JOIN CorporateCustomer cc ON cc.userId = u.userId
            WHERE u.username = :identity
               OR c.email = :email
            LIMIT 1
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'identity' => $identity,
            'email' => $identity
        ]);

        $user = $stmt->fetch();

        $validPortal = false;

        if ($user) {
            if ($portal === 'customer') {
                $validPortal = in_array(
                    $user['role'],
                    ['IndividualCustomer', 'CorporateCustomer'],
                    true
                );
            } elseif ($portal === 'driver') {
                $validPortal = $user['role'] === 'Driver';
            } else {
                $validPortal = !in_array(
                    $user['role'],
                    ['IndividualCustomer', 'CorporateCustomer', 'Driver'],
                    true
                );
            }
        }

        if (
            $user &&
            $validPortal &&
            password_verify($password, $user['passwordHash'])
        ) {
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['userId'];
            $_SESSION['full_name'] = $user['fullName'];
            $_SESSION['role'] = $user['role'];

            // Record the latest login for system administrators.
            if ($user['role'] === 'SystemAdmin') {
                $update = $pdo->prepare(
                    "UPDATE SystemAdmin
                     SET lastLoginDate = NOW()
                     WHERE userId = ?"
                );
                $update->execute([$user['userId']]);
            }

            redirect_to_dashboard();
        } else {
            $error = 'Invalid login details or this account cannot access the selected portal.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($page['title']) ?> | HELMS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-page">

<header>
    <div class="container nav">
        <a href="index.php" class="brand">
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
        <h1>Welcome back.<br><span>Let's get moving.</span></h1>
        <p><?= h($page['description']) ?></p>

        <div class="login-points">
            <div class="login-point"><span class="point-icon">✓</span><span>One connected logistics platform</span></div>
            <div class="login-point"><span class="point-icon">✓</span><span>Parcel status and delivery updates</span></div>
            <div class="login-point"><span class="point-icon">✓</span><span>Role-based workspace access</span></div>
            <div class="login-point"><span class="point-icon">✓</span><span>Designed for Sri Lankan delivery teams</span></div>
        </div>
    </section>

    <section class="login-card">
        <div class="login-card-head">
            <div class="login-symbol"><?= h($page['icon']) ?></div>
            <h2><?= h($page['title']) ?></h2>
            <p>Enter your account details to continue.</p>
        </div>

        <form class="login-form" method="POST">
            <input type="hidden" name="portal" value="<?= h($portal) ?>">
            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">

            <div class="form-row">
                <label for="identity"><?= h($page['emailLabel']) ?></label>
                <input
                    id="identity"
                    name="identity"
                    type="text"
                    value="<?= h($identity) ?>"
                    placeholder="<?= h($page['emailPlaceholder']) ?>"
                    autocomplete="username"
                    required
                >
            </div>

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
                    <button class="password-toggle" id="togglePassword" type="button">Show</button>
                </div>
            </div>

            <?php if ($error !== ''): ?>
                <div class="login-notice show" role="alert">
                    <?= h($error) ?>
                </div>
            <?php endif; ?>

            <button class="btn btn-primary login-submit" type="submit">
                Sign in to HELMS <span aria-hidden="true">→</span>
            </button>
        </form>

        <?php if ($portal === 'customer'): ?>
            <div class="login-divider">NEW TO HELMS?</div>
            <p class="login-bottom">
                Need a customer account?
                <a class="text-link" href="register.php">Create an account</a>
            </p>
        <?php else: ?>
            <div class="staff-note">
                Staff and driver accounts are created by the company.
                Contact your supervisor if you need access.
            </div>
        <?php endif; ?>
    </section>
</main>

<footer class="login-footer">
    <span>&copy; <?= date('Y') ?> Henowic Inventors (Pvt) Ltd.</span>
    <span><a href="index.php">Return to HELMS home</a> &middot; NIBM DSE Final Project</span>
</footer>

<script>
const password = document.getElementById('password');
const toggle = document.getElementById('togglePassword');

toggle.addEventListener('click', function () {
    const visible = password.type === 'password';
    password.type = visible ? 'text' : 'password';
    toggle.textContent = visible ? 'Hide' : 'Show';
});
</script>

</body>
</html>