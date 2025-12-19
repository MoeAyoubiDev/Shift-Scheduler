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
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
<header>
    <div class="header-content">
        <div class="brand">
            <span class="brand-badge"><?= e($brandYear) ?></span>
            <div>
                <div><?= e(app_config('name', 'Shift Scheduler')) ?></div>
                <small class="muted"><?= e($tagline) ?></small>
            </div>
        </div>
        <?php if (current_user()): ?>
            <div class="pill">Secure Session Active</div>
        <?php endif; ?>
    </div>
</header>
<main>
    <?php if (!empty($message)): ?>
        <div class="notice"><?= e($message) ?></div>
    <?php endif; ?>
