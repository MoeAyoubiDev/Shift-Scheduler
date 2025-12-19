<?php
require_once __DIR__ . '/../layouts/header.php';
?>

<h1>Supervisor Dashboard - <?= htmlspecialchars($user['section_name']) ?></h1>

<div class="dashboard-menu">
    <a href="/supervisor/schedule.php" class="btn">View Schedule</a>
    <a href="/supervisor/performance.php" class="btn">Performance Analytics</a>
    <a href="/supervisor/breaks.php" class="btn">Break Reports</a>
</div>

<div class="dashboard-summary">
    <div class="card">
        <h3>Current Week Schedule</h3>
        <p class="large-number"><?= count($schedules) ?></p>
        <p>Assignments</p>
        <a href="/supervisor/schedule.php" class="btn btn-sm">View Schedule</a>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

