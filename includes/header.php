<?php
declare(strict_types=1);

// Safely get page title
$pageTitle = $title ?? (function_exists('app_config') ? app_config('name', 'Shift Scheduler') : 'Shift Scheduler');
$tagline = function_exists('app_config') ? app_config('tagline', 'Unified coverage, breaks, and analytics') : 'Unified coverage, breaks, and analytics';
$brandYear = function_exists('app_config') ? app_config('brand_year', '2026') : '2026';
$user = function_exists('current_user') ? current_user() : null;
$role = ($user && isset($user['role'])) ? $user['role'] : null;
$sectionName = ($user && isset($user['section_name'])) ? $user['section_name'] : null;
$currentSectionId = function_exists('current_section_id') ? current_section_id() : null;
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="format-detection" content="telephone=no">
    <title><?= function_exists('e') ? e($pageTitle) : htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <meta name="description" content="Manage shift coverage, employee requests, and weekly schedules in one secure workspace.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/app.css?v=<?= @filemtime(__DIR__ . '/../public/assets/css/app.css') ?: time() ?>">

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
                <span class="user-name-text"><?= function_exists('e') ? e($displayName) : htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        </div>
        <div class="header-actions">
            <div class="pill"><?= function_exists('e') ? e($role) : htmlspecialchars($role ?? '', ENT_QUOTES, 'UTF-8') ?></div>
            <?php if ($sectionName || $currentSectionId): ?>
                <div class="pill">Section: <?= function_exists('e') ? e($sectionName ?? 'Selected') : htmlspecialchars($sectionName ?? 'Selected', ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
            <form method="post" class="inline" action="/index.php">
                <input type="hidden" name="action" value="logout">
                <input type="hidden" name="csrf_token" value="<?= function_exists('csrf_token') ? (function_exists('e') ? e(csrf_token()) : htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8')) : '' ?>">
                <button type="submit" class="btn secondary small">Logout</button>
            </form>
        </div>
    </div>
</header>
<?php endif; ?>
<main>
    <?php if (!empty($message)): ?>
        <div class="notice"><?= function_exists('e') ? e($message) : htmlspecialchars($message ?? '', ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
