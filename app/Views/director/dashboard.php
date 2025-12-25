<?php
declare(strict_types=1);

// Structural update: director dashboard now uses a shared sidebar and page includes.
$allowedPages = ['overview', 'employees', 'departments', 'attendance', 'reports', 'settings'];
$directorPage = $directorPage ?? 'overview';
if (!in_array($directorPage, $allowedPages, true)) {
    $directorPage = 'overview';
}
?>
<div class="dashboard-container director-dashboard">
    <!-- Structural update: left sidebar + dedicated page content area -->
    <div class="dashboard-shell <?= $directorPage === 'overview' ? 'dashboard-shell--full' : '' ?>">
        <?php require __DIR__ . '/partials/sidebar.php'; ?>

        <main class="dashboard-content dashboard-main">
            <?php require __DIR__ . '/pages/' . $directorPage . '.php'; ?>
        </main>
    </div>
</div>
