<?php
declare(strict_types=1);

$pageTitle = $title ?? app_config('name', 'Shift Scheduler');
$tagline = app_config('tagline', 'Smart weekly planning & coverage');
$brandYear = app_config('brand_year', '2026');
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
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="page-shell">
<header>
    <div class="header-content">
        <div class="brand">
            <span class="brand-badge"><?= e($brandYear) ?></span>
            <div>
                <div><?= e(app_config('name', 'Shift Scheduler')) ?></div>
                <small class="muted"><?= e($tagline) ?></small>
            </div>
        </div>
        <div class="header-actions">
            <?php if (current_user()): ?>
                <div class="pill">Secure Session Active</div>
            <?php endif; ?>
            <div class="status-badge">Server-ready</div>
        </div>
    </div>
</header>
<main>
    <?php if (!empty($message)): ?>
        <div class="notice"><?= e($message) ?></div>
    <?php endif; ?>
