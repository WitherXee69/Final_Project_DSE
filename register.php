<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';

if (!empty($_SESSION['user_id'])) {
    redirect_to_dashboard();
}

$error = '';
$fullName = '';
$email = '';
$phone = '';
$address = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (!verify_csrf()) {
        $error = 'Your session has expired. Please refresh the page and try again.';
    } elseif (
        $fullName === '' ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        $phone === '' ||
        $address === ''
    ) {
        $error = 'Please provide valid details in all required fields.';
    } elseif (strlen($password) < 8) {
        $error = 'Your password must contain at least 8 characters.';
    } elseif ($password !== $confirmPassword) {
        $error = 'The passwords do not match.';
    } elseif (empty($_POST['terms'])) {
        $error = 'Please accept the terms before creating your account.';
    } else {
        try {
            $pdo->beginTransaction();

            // Prevent duplicate usernames and customer email addresses.
            $check = $pdo->prepare("
                SELECT u.userId
                FROM User u
                LEFT JOIN Customer c ON c.userId = u.userId
                WHERE u.username = ?
                   OR c.email = ?
                LIMIT 1
            ");
            $check->execute([$email, $email]);

            if ($check->fetch()) {
                throw new RuntimeException(
                    'An account already exists with this email address.'
                );
            }

            // Generate the next USR identifier.
            $last = $pdo->query("
                SELECT userId
                FROM User
                WHERE userId LIKE 'USR-%'
                ORDER BY userId DESC
                LIMIT 1
                FOR UPDATE
            ")->fetchColumn();

            $lastNumber = $last
                ? (int)substr($last, 4)
                : 0;

            $newUserId = 'USR-' . str_pad(
                $lastNumber + 1,
                6,
                '0',
                STR_PAD_LEFT
            );

            $passwordHash = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            // 1. Create the base User record.
            $stmt = $pdo->prepare("
                INSERT INTO User
                    (userId, username, passwordHash, role, adminId)
                VALUES
                    (?, ?, ?, 'IndividualCustomer', NULL)
            ");
            $stmt->execute([
                $newUserId,
                $email,
                $passwordHash
            ]);

            // 2. Create the Customer record.
            $stmt = $pdo->prepare("
                INSERT INTO Customer
                    (userId, contactNumber, email, address)
                VALUES
                    (?, ?, ?, ?)
            ");
            $stmt->execute([
                $newUserId,
                $phone,
                $email,
                $address
            ]);

            // 3. Create the IndividualCustomer record.
            $stmt = $pdo->prepare("
                INSERT INTO IndividualCustomer
                    (userId, name, nic, csMgrId)
                VALUES
                    (?, ?, NULL, NULL)
            ");
            $stmt->execute([
                $newUserId,
                $fullName
            ]);

            $pdo->commit();

            session_regenerate_id(true);

            $_SESSION['user_id'] = $newUserId;
            $_SESSION['full_name'] = $fullName;
            $_SESSION['role'] = 'IndividualCustomer';

            redirect_to_dashboard();

        } catch (RuntimeException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = $e->getMessage();

        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = 'We could not create your account. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create an individual customer account | HELMS</title>
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
            <a class="btn btn-ghost" href="index.php">Home</a>
        </nav>
    </div>
</header>

<main class="login-main register-main">
    <section class="login-intro">
        <span class="pill">INDIVIDUAL CUSTOMER REGISTRATION &middot; HELMS</span>
        <h1>Your deliveries,<br><span>all in one place.</span></h1>
        <p>Create your personal HELMS account to request pickups, track parcels and manage delivery details.</p>

        <div class="login-points">
            <div class="login-point"><span class="point-icon">✓</span><span>Track your parcel progress</span></div>
            <div class="login-point"><span class="point-icon">✓</span><span>Keep sender and recipient details handy</span></div>
            <div class="login-point"><span class="point-icon">✓</span><span>Manage your delivery requests</span></div>
            <div class="login-point"><span class="point-icon">✓</span><span>One account for your customer activity</span></div>
        </div>

        <div class="staff-note">
            This form is for individual customers only.
            Business, staff and driver accounts are created through the company's internal process.
        </div>
    </section>

    <section class="login-card">
        <div class="login-card-head">
            <div class="login-symbol">+</div>
            <h2>Create your account</h2>
            <p>Enter your personal details to get started.</p>
        </div>

        <form class="login-form" method="POST">
            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">

            <div class="form-row">
                <label for="fullName">Full name</label>
                <input id="fullName" name="full_name" type="text"
                       value="<?= h($fullName) ?>" autocomplete="name" required>
            </div>

            <div class="form-row">
                <label for="email">Email address</label>
                <input id="email" name="email" type="email"
                       value="<?= h($email) ?>" autocomplete="email" required>
            </div>

            <div class="form-row">
                <label for="phone">Mobile number</label>
                <input id="phone" name="phone" type="tel"
                       value="<?= h($phone) ?>" autocomplete="tel" required>
            </div>

            <div class="form-row">
                <label for="address">Main pickup address</label>
                <textarea id="address" name="address" rows="3"
                          autocomplete="street-address" required><?= h($address) ?></textarea>
            </div>

            <div class="form-row">
                <label for="password">Create password</label>
                <div class="password-wrap">
                    <input id="password" name="password" type="password"
                           minlength="8" autocomplete="new-password" required>
                    <button class="password-toggle" type="button"
                            onclick="togglePassword('password', this)">Show</button>
                </div>
            </div>

            <div class="form-row">
                <label for="confirmPassword">Confirm password</label>
                <div class="password-wrap">
                    <input id="confirmPassword" name="confirm_password"
                           type="password" minlength="8"
                           autocomplete="new-password" required>
                    <button class="password-toggle" type="button"
                            onclick="togglePassword('confirmPassword', this)">Show</button>
                </div>
            </div>

            <label class="remember register-consent">
                <input type="checkbox" name="terms" value="1" required>
                <span>I confirm that the details provided are accurate and agree to the applicable terms and privacy policy.</span>
            </label>

            <?php if ($error !== ''): ?>
                <div class="login-notice show" role="alert">
                    <?= h($error) ?>
                </div>
            <?php endif; ?>

            <button class="btn btn-primary login-submit" type="submit">
                Create individual account <span aria-hidden="true">→</span>
            </button>
        </form>

        <div class="login-divider">ALREADY REGISTERED?</div>
        <p class="login-bottom">
            Have a HELMS account?
            <a class="text-link" href="login.php?portal=customer">Sign in instead</a>
        </p>
    </section>
</main>

<footer class="login-footer">
    <span>&copy; <?= date('Y') ?> Henowic Inventors (Pvt) Ltd.</span>
    <span><a href="index.php">Return to HELMS home</a> &middot; NIBM DSE Final Project</span>
</footer>

<script>
function togglePassword(id, button) {
    const input = document.getElementById(id);
    const visible = input.type === 'password';

    input.type = visible ? 'text' : 'password';
    button.textContent = visible ? 'Hide' : 'Show';
}
</script>

</body>
</html>