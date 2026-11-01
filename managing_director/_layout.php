<?php
/**
 * HELMS - Managing Director module: shared layout + access control.
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

/** Only a logged-in Managing Director may use this module. */
function md_require_director(): void
{
    if (empty($_SESSION['user_id'])) {
        header('Location: ../login.php?portal=staff');
        exit;
    }

    if (($_SESSION['role'] ?? '') !== 'ManagingDirector') {
        http_response_code(403);
        echo <<<'HTML'
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Access restricted</title>
    <link rel="stylesheet" href="../assets/css/md.css">
</head>
<body class="md-access-denied-page">
    <h2>Access restricted</h2>
    <p>This area is for the Managing Director only.</p>
    <p><a href="../dashboard.php">Back to my dashboard</a></p>
</body>
</html>
HTML;
        exit;
    }
}

function md_header(string $title): void
{
    $name = $_SESSION['full_name'] ?? 'Managing Director';
    ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($title) ?> | HELMS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/md.css">
</head>
<body class="md-body">

<header>
    <div class="container nav">
        <a href="../index.php" class="brand">
            <div class="logo">H</div>
            <div>
                <b>HELMS</b>
                <small>Henowic Inventors (Pvt) Ltd.</small>
            </div>
        </a>
        <div class="md-user">
            <div>
                <span><?= h($name) ?></span>
                <small>Managing Director</small>
            </div>
            <a class="btn btn-ghost" href="../logout.php">Logout</a>
        </div>
    </div>
</header>

<main class="md-main">
    <?php
}

function md_footer(): void
{
    ?>
</main>

</body>
</html>
    <?php
}
