<?php
declare(strict_types=1);

$pageTitle = $title ?? app_config('name', 'Shift Scheduler');
$tagline = app_config('tagline', 'Unified coverage, breaks, and analytics');
$brandYear = app_config('brand_year', '2026');
$user = current_user();
$role = $user['role'] ?? null;
$sectionName = $user['section_name'] ?? null;
$currentSectionId = current_section_id();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <meta name="description" content="Manage shift coverage, employee requests, and weekly schedules in one secure workspace.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/app.css?v=<?= filemtime(__DIR__ . '/../public/assets/css/app.css') ?>">

</head>
<body class="page-shell <?= !$user ? 'login-page' : '' ?>">
<?php if ($user): ?>
<header>
    <div class="header-content">
        <div class="brand">
            <?php
            // Get user's display name
            $displayName = '';
            if (isset($user['full_name']) && !empty($user['full_name'])) {
                $displayName = $user['full_name'];
            } elseif (isset($user['username']) && !empty($user['username'])) {
                $displayName = $user['username'];
            } else {
                $displayName = 'User';
            }
            ?>
            <div class="user-name">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M20 21V19C20 17.9391 19.5786 16.9217 18.8284 16.1716C18.0783 15.4214 17.0609 15 16 15H8C6.93913 15 5.92172 15.4214 5.17157 16.1716C4.42143 16.9217 4 17.9391 4 19V21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M12 11C14.2091 11 16 9.20914 16 7C16 4.79086 14.2091 3 12 3C9.79086 3 8 4.79086 8 7C8 9.20914 9.79086 11 12 11Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span class="user-name-text"><?= e($displayName) ?></span>
            </div>
        </div>
        <div class="header-actions">
            <div class="pill"><?= e($role) ?></div>
            <?php if ($sectionName || $currentSectionId): ?>
                <div class="pill">Section: <?= e($sectionName ?? 'Selected') ?></div>
            <?php endif; ?>
            <form method="post" class="inline" action="/index.php">
                <input type="hidden" name="action" value="logout">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <button type="submit" class="btn secondary small">Logout</button>
            </form>
        </div>
    </div>
</header>
<?php endif; ?>
<main>
    <?php if (!empty($message)): ?>
        <div class="notice"><?= e($message) ?></div>
    <?php endif; ?>
