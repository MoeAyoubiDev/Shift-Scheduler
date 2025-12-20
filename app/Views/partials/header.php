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
    <link rel="stylesheet" href="/assets/css/app.css?v=<?= filemtime(__DIR__ . '/../../../public/assets/css/app.css') ?>">

</head>
<body class="page-shell <?= !$user ? 'login-page' : '' ?>">
<?php if ($user): ?>
<header>
    <div class="header-content">
        <div class="brand">
            <span class="brand-badge"><?= e($brandYear) ?></span>
            <div>
                <div><?= e(app_config('name', 'Shift Management System')) ?></div>
                <small class="muted"><?= e($tagline) ?></small>
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
