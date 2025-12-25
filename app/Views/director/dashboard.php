<?php
declare(strict_types=1);

// Supervisor dashboard uses shared top navigation and page includes.
$allowedPages = ['overview', 'employees', 'departments', 'attendance', 'reports', 'settings'];
$directorPage = $directorPage ?? 'overview';
if (!in_array($directorPage, $allowedPages, true)) {
    $directorPage = 'overview';
}
?>
<div class="dashboard-container director-dashboard">
    <div class="dashboard-shell">
        <?php require __DIR__ . '/partials/topnav.php'; ?>

        <main class="dashboard-content dashboard-main">
            <?php require __DIR__ . '/pages/' . $directorPage . '.php'; ?>
        </main>
    </div>
</div>
