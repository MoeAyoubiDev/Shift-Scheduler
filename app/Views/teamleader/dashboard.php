<?php
declare(strict_types=1);

$allowedPages = [
    'overview',
    'create-employee',
    'manage-employees',
    'shift-requirements',
    'shift-requests',
    'weekly-schedule',
    'break-monitoring',
    'performance',
];
$teamleaderPage = $teamleaderPage ?? 'overview';
if (!in_array($teamleaderPage, $allowedPages, true)) {
    $teamleaderPage = 'overview';
}
?>
<div class="dashboard-container teamleader-dashboard">
    <div class="dashboard-shell">
        <?php require __DIR__ . '/partials/topnav.php'; ?>

        <main class="dashboard-content dashboard-main">
            <?php require __DIR__ . '/pages/' . $teamleaderPage . '.php'; ?>
        </main>
    </div>
</div>
