<?php
declare(strict_types=1);

$employeeTotal = count($employees);
$departmentTotal = count($sections);
$reportTotal = count($performance);
$pendingRequests = count($requests);
$scheduledTotal = count($schedule);
$leadersTotal = count($admins);
$onTimeEmployees = count(array_filter($performance, static fn($row) => (float) ($row['average_delay_minutes'] ?? 0) <= 0));
$avgDelay = 0.0;
if ($performance) {
    $avgDelay = array_sum(array_map(static fn($row) => (float) ($row['average_delay_minutes'] ?? 0), $performance)) / count($performance);
    $avgDelay = round($avgDelay, 1);
}
$sectionsWithLeaders = [];
foreach ($admins as $admin) {
    if (!empty($admin['section_name'])) {
        $sectionsWithLeaders[$admin['section_name']] = true;
    }
}
$unassignedDepartments = max(0, $departmentTotal - count($sectionsWithLeaders));
?>
<section class="dashboard-page">
    <div class="dashboard-page-header">
        <div>
            <h2>Director Overview</h2>
            <p class="muted">High-level operational snapshot across workforce, departments, and reporting health.</p>
        </div>
        <div class="meta-row">
            <button type="button" class="week-selector-pill" id="week-selector" data-week-start="<?= e($weekStart) ?>" data-week-end="<?= e($weekEnd) ?>">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M8 2V6M16 2V6M3 10H21M5 4H19C20.1046 4 21 4.89543 21 6V19C21 20.1046 20.1046 21 19 21H5C3.89543 21 3 20.1046 3 19V6C3 4.89543 3.89543 4 5 4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>Week <?= e($weekStart) ?> → <?= e($weekEnd) ?></span>
            </button>
            <a class="btn btn-change-section" href="/index.php?reset_section=1">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M3 9L12 2L21 9V20C21 20.5304 20.7893 21.0391 20.4142 21.4142C20.0391 21.7893 19.5304 22 19 22H5C4.46957 22 3.96086 21.7893 3.58579 21.4142C3.21071 21.0391 3 20.5304 3 20V9Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M9 22V12H15V22" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M12 6V12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>Change Section</span>
            </a>
        </div>
    </div>

    <div class="overview-card-grid">
        <a class="card overview-card overview-card-link" href="/dashboard/employees.php">
            <div class="overview-card-top">
                <div>
                    <h3>Employees</h3>
                    <p class="muted">Staffing readiness and request flow.</p>
                </div>
                <div class="overview-card-metric"><?= e((string) $employeeTotal) ?></div>
            </div>
            <div class="overview-card-subtext">Total employees</div>
            <div class="overview-mini-grid">
                <div class="overview-mini-card">
                    <span class="mini-label">Active</span>
                    <span class="mini-value"><?= e((string) $employeeTotal) ?></span>
                </div>
                <div class="overview-mini-card">
                    <span class="mini-label">Scheduled</span>
                    <span class="mini-value"><?= e((string) $scheduledTotal) ?></span>
                </div>
                <div class="overview-mini-card">
                    <span class="mini-label">Pending requests</span>
                    <span class="mini-value"><?= e((string) $pendingRequests) ?></span>
                </div>
            </div>
        </a>

        <a class="card overview-card overview-card-link" href="/dashboard/departments.php">
            <div class="overview-card-top">
                <div>
                    <h3>Departments</h3>
                    <p class="muted">Section structure and leadership coverage.</p>
                </div>
                <div class="overview-card-metric"><?= e((string) $departmentTotal) ?></div>
            </div>
            <div class="overview-card-subtext">Total departments</div>
            <div class="overview-mini-grid">
                <div class="overview-mini-card">
                    <span class="mini-label">Leaders</span>
                    <span class="mini-value"><?= e((string) $leadersTotal) ?></span>
                </div>
                <div class="overview-mini-card">
                    <span class="mini-label">Covered</span>
                    <span class="mini-value"><?= e((string) count($sectionsWithLeaders)) ?></span>
                </div>
                <div class="overview-mini-card">
                    <span class="mini-label">Unassigned</span>
                    <span class="mini-value"><?= e((string) $unassignedDepartments) ?></span>
                </div>
            </div>
        </a>

        <a class="card overview-card overview-card-link" href="/dashboard/reports.php">
            <div class="overview-card-top">
                <div>
                    <h3>Reports</h3>
                    <p class="muted">Performance analytics and timing accuracy.</p>
                </div>
                <div class="overview-card-metric"><?= e((string) $reportTotal) ?></div>
            </div>
            <div class="overview-card-subtext">Total records</div>
            <div class="overview-mini-grid">
                <div class="overview-mini-card">
                    <span class="mini-label">On-time</span>
                    <span class="mini-value"><?= e((string) $onTimeEmployees) ?></span>
                </div>
                <div class="overview-mini-card">
                    <span class="mini-label">Avg delay</span>
                    <span class="mini-value"><?= e((string) $avgDelay) ?>m</span>
                </div>
                <div class="overview-mini-card">
                    <span class="mini-label">Pending review</span>
                    <span class="mini-value"><?= e((string) $reportTotal) ?></span>
                </div>
            </div>
        </a>
    </div>
</section>
