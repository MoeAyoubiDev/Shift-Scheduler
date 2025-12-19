<?php
require_once __DIR__ . '/../layouts/header.php';
?>

<h1>Director Dashboard</h1>

<div class="dashboard-grid">
    <?php foreach ($dashboardData as $section): ?>
        <div class="card">
            <h2><?= htmlspecialchars($section['section_name']) ?></h2>
            <div class="stats">
                <div class="stat-item">
                    <span class="stat-label">Total Employees:</span>
                    <span class="stat-value"><?= $section['total_employees'] ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Active Employees:</span>
                    <span class="stat-value"><?= $section['active_employees'] ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Pending Requests:</span>
                    <span class="stat-value"><?= $section['pending_requests'] ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Schedules:</span>
                    <span class="stat-value"><?= $section['schedules_count'] ?></span>
                </div>
            </div>
            <a href="/director/section/<?= $section['section_id'] ?>" class="btn btn-primary">View Section</a>
        </div>
    <?php endforeach; ?>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

