
<?php
session_start();

if (empty($_SESSION['user_id'])) {
    header('Location: ../login.php?portal=staff');
    exit;
}

require_once __DIR__ . '/../config/database.php';

$userId = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare(
        "SELECT u.userId, u.username, u.role,
                sa.accessLevel, sa.lastLoginDate
         FROM User u
         INNER JOIN SystemAdmin sa ON sa.userId = u.userId
         WHERE u.userId = :userId
         LIMIT 1"
    );
    $stmt->execute(['userId' => $userId]);
    $admin = $stmt->fetch();

    if (!$admin) {
        http_response_code(403);
        exit('Access denied. System Administrator access is required.');
    }

    $totalUsers = (int) $pdo->query(
        "SELECT COUNT(*) FROM User"
    )->fetchColumn();

    $roleCounts = $pdo->query(
        "SELECT role, COUNT(*) AS total
         FROM User
         GROUP BY role
         ORDER BY total DESC"
    )->fetchAll();

} catch (PDOException $e) {
    error_log('System Admin dashboard error: ' . $e->getMessage());
    http_response_code(500);
    exit('Unable to load the System Admin dashboard.');
}

function h($value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

$adminName = $admin['username'];
$accessLevel = $admin['accessLevel'];

$formattedLastLogin = $admin['lastLoginDate']
    ? date('d M Y, h:i A', strtotime($admin['lastLoginDate']))
    : 'Not available';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>System Admin Dashboard | HELMS</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/system-admin.css">
</head>

<body class="system-admin-page">

<header class="system-admin-header">
    <div class="container system-admin-nav">

        <a href="../index.php" class="brand">
            <div class="logo">H</div>
            <div>
                <b>HELMS</b>
                <small>Henowic Inventors (Pvt) Ltd.</small>
            </div>
        </a>

        <nav class="system-admin-menu">
            <a href="index.php" class="active">Dashboard</a>
            <a href="user_management.php">User Management</a>
        </nav>

        <div class="system-admin-account">
            <span class="system-admin-avatar">
                <?= h(strtoupper(substr($adminName, 0, 1))) ?>
            </span>

            <div class="system-admin-account-info">
                <strong><?= h($adminName) ?></strong>
                <small>System Admin</small>
            </div>

            <a class="system-admin-logout" href="../logout.php">Logout</a>
        </div>

    </div>
</header>

<main class="container system-admin-main">

    <section class="system-admin-welcome">
        <div>
            <span class="system-admin-eyebrow">SYSTEM ADMINISTRATION</span>
            <h1>Good day, <?= h($adminName) ?>.</h1>
            <p>
                Manage HELMS user accounts and maintain
                system-level access.
            </p>
        </div>

        <div class="system-admin-access-card">
            <span>ACCESS LEVEL</span>
            <strong><?= h($accessLevel) ?></strong>
            <small>System Administrator</small>
        </div>
    </section>

    <section class="system-admin-profile">

        <div class="profile-avatar">
            <?= h(strtoupper(substr($adminName, 0, 1))) ?>
        </div>

        <div class="profile-main">
            <span class="profile-label">System Administrator</span>
            <h2><?= h($adminName) ?></h2>
            <span class="profile-user-id">
                User ID: <?= h($admin['userId']) ?>
            </span>
        </div>

        <div class="profile-detail">
            <span>Access Level</span>
            <strong><?= h($accessLevel) ?></strong>
        </div>

        <div class="profile-detail">
            <span>Last Login</span>
            <strong><?= h($formattedLastLogin) ?></strong>
        </div>

    </section>

    <section class="system-admin-stats">

        <article class="system-admin-stat-card">
            <div class="stat-icon navy-icon">#</div>
            <div>
                <span>Total Users</span>
                <strong><?= $totalUsers ?></strong>
                <small>Registered in HELMS</small>
            </div>
        </article>

        <article class="system-admin-stat-card">
            <div class="stat-icon amber-icon">R</div>
            <div>
                <span>User Roles</span>
                <strong><?= count($roleCounts) ?></strong>
                <small>Roles currently assigned</small>
            </div>
        </article>

        <article class="system-admin-stat-card">
            <div class="stat-icon green-icon">✓</div>
            <div>
                <span>System Access</span>
                <strong><?= h($accessLevel) ?></strong>
                <small>Current administrator level</small>
            </div>
        </article>

    </section>

    <section class="system-admin-section">

        <div class="system-admin-section-heading">
            <div>
                <span class="section-kicker">USER ADMINISTRATION</span>
                <h2>User Management</h2>
            </div>
        </div>

        <div class="admin-action-grid">
            <a href="user_management.php" class="admin-action-card">

                <div class="admin-action-icon">U</div>

                <div>
                    <h3>Manage Users</h3>
                    <p>
                        View registered users, search accounts
                        and manage user records.
                    </p>
                </div>

                <span class="action-arrow">→</span>

            </a>
        </div>

    </section>

    <section class="system-admin-section">

        <div class="system-admin-section-heading">
            <div>
                <span class="section-kicker">ACCOUNT OVERVIEW</span>
                <h2>Users by Role</h2>
            </div>
        </div>

        <div class="role-summary-grid">
            <?php foreach ($roleCounts as $role): ?>
                <div class="role-summary-card">
                    <span><?= h($role['role']) ?></span>
                    <strong><?= (int)$role['total'] ?></strong>
                </div>
            <?php endforeach; ?>
        </div>

    </section>

</main>

</body>
</html>
