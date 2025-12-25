<?php
declare(strict_types=1);

// Safely get page title
$pageTitle = $title ?? (function_exists('app_config') ? app_config('name', 'Shift Scheduler') : 'Shift Scheduler');
$tagline = function_exists('app_config') ? app_config('tagline', 'Unified coverage, breaks, and analytics') : 'Unified coverage, breaks, and analytics';
$brandYear = function_exists('app_config') ? app_config('brand_year', '2026') : '2026';
$user = function_exists('current_user') ? current_user() : null;
$role = ($user && isset($user['role'])) ? $user['role'] : null;
$bodyRoleClass = $role ? 'role-' . strtolower(str_replace(' ', '-', $role)) : '';
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
    <link rel="stylesheet" href="/assets/css/skin.css?v=<?= @filemtime(__DIR__ . '/../public/assets/css/skin.css') ?: time() ?>">

    </head>
<body class="page-shell <?= !$user ? 'login-page' : '' ?> <?= $bodyRoleClass ?>">
<?php if ($user): ?>
    <?php
    $companyLabel = $user['company_name'] ?? $sectionName ?? 'Company Workspace';
    $dashboardSubtitle = match ($role) {
        'Supervisor' => 'Supervisor Command Center',
        'Team Leader' => 'Team Leader Workspace',
        default => 'My Schedule',
    };
    $notificationAnchor = $role === 'Supervisor'
        ? '#director-activity'
        : ($role === 'Employee' ? '#employee-notifications' : null);
    ?>
    <header class="dashboard-topbar">
        <div class="dashboard-topbar-inner">
            <div class="dashboard-brand">
                <div class="dashboard-brand-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="3" y="3" width="18" height="18" rx="6" stroke="currentColor" stroke-width="2"/>
                        <path d="M8 12H16M12 8V16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </div>
                <div class="dashboard-brand-text">
                    <span class="dashboard-brand-title"><?= function_exists('e') ? e($pageTitle) : htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="dashboard-brand-subtitle"><?= function_exists('e') ? e($dashboardSubtitle) : htmlspecialchars($dashboardSubtitle, ENT_QUOTES, 'UTF-8') ?></span>
                </div>
                <span class="dashboard-brand-divider">|</span>
                <span class="dashboard-brand-company"><?= function_exists('e') ? e($companyLabel) : htmlspecialchars($companyLabel, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
            <div class="dashboard-topbar-actions">
                <div class="topbar-pills">
                    <span class="topbar-pill"><?= function_exists('e') ? e($role ?? '') : htmlspecialchars($role ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                    <?php if ($sectionName || $currentSectionId): ?>
                        <span class="topbar-pill">Section: <?= function_exists('e') ? e($sectionName ?? 'Selected') : htmlspecialchars($sectionName ?? 'Selected', ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endif; ?>
                </div>
                <?php if ($notificationAnchor): ?>
                    <a class="icon-button" href="<?= function_exists('e') ? e($notificationAnchor) : htmlspecialchars($notificationAnchor, ENT_QUOTES, 'UTF-8') ?>" aria-label="View updates">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M18 8C18 6.4087 17.3679 4.88258 16.2426 3.75736C15.1174 2.63214 13.5913 2 12 2C10.4087 2 8.88258 2.63214 7.75736 3.75736C6.63214 4.88258 6 6.4087 6 8C6 15 3 17 3 17H21C21 17 18 15 18 8Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M13.73 21C13.5542 21.3031 13.3019 21.5547 12.9982 21.7295C12.6946 21.9044 12.3504 21.9965 12 21.9965C11.6496 21.9965 11.3054 21.9044 11.0018 21.7295C10.6982 21.5547 10.4458 21.3031 10.27 21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span class="icon-dot"></span>
                    </a>
                <?php endif; ?>
                <form method="post" class="inline" action="/index.php">
                    <input type="hidden" name="action" value="logout">
                    <input type="hidden" name="csrf_token" value="<?= function_exists('csrf_token') ? (function_exists('e') ? e(csrf_token()) : htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8')) : '' ?>">
                    <button type="submit" class="btn ghost small">Logout</button>
                </form>
            </div>
        </div>
    </header>
<?php endif; ?>
<main>
    <?php if (!empty($message)): ?>
        <div class="notice"><?= function_exists('e') ? e($message) : htmlspecialchars($message ?? '', ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
