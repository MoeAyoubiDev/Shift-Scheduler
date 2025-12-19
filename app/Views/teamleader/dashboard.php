<?php
require_once __DIR__ . '/../layouts/header.php';
?>

<h1>Team Leader Dashboard - <?= htmlspecialchars($user['section_name']) ?></h1>

<div class="dashboard-menu">
    <a href="/employees.php" class="btn">Manage Employees</a>
    <a href="/shift-requests.php" class="btn">Shift Requests (<?= count($pendingRequests) ?>)</a>
    <a href="/schedule.php" class="btn">Weekly Schedule</a>
    <a href="/performance.php" class="btn">Performance Analytics</a>
    <a href="/breaks.php" class="btn">Break Management</a>
</div>

<div class="dashboard-summary">
    <div class="card">
        <h3>Pending Shift Requests</h3>
        <p class="large-number"><?= count($pendingRequests) ?></p>
        <a href="/shift-requests.php" class="btn btn-sm">View All</a>
    </div>
    
    <div class="card">
        <h3>Current Week Schedule</h3>
        <p class="large-number"><?= count($schedules) ?></p>
        <p>Assignments</p>
        <a href="/schedule.php" class="btn btn-sm">View Schedule</a>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

