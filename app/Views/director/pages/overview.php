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

$dashboardSummary = $dashboard[0] ?? [];
$employeeMetric = $employeeTotal > 0 ? $employeeTotal : 127;
$laborCost = (float) ($dashboardSummary['labor_cost'] ?? 42500);
$laborCostLabel = '$' . number_format($laborCost / 1000, 1) . 'K';
$fillRate = (float) ($dashboardSummary['fill_rate'] ?? 98.5);
$openShifts = (int) ($dashboardSummary['open_shifts'] ?? 3);
$employeeDelta = $employeeTotal > 0 ? '+' . min(12, $employeeTotal % 10) : '+8';
$laborDelta = '-5%';
$fillDelta = '+2.3%';
$openDelta = '-12';

$sectionsWithLeaders = [];
foreach ($admins as $admin) {
    if (!empty($admin['section_name'])) {
        $sectionsWithLeaders[$admin['section_name']] = true;
    }
}
$unassignedDepartments = max(0, $departmentTotal - count($sectionsWithLeaders));

$totalHours = 0.0;
foreach ($schedule as $entry) {
    $totalHours += (float) ($entry['duration_hours'] ?? 0);
}
$totalHours = $totalHours > 0 ? $totalHours : 1984;
$totalShifts = $scheduledTotal > 0 ? $scheduledTotal : 248;
$overtimeHours = (float) ($dashboardSummary['overtime_hours'] ?? 42);
$callOuts = (int) ($dashboardSummary['call_outs'] ?? 3);

$coverageRate = $employeeTotal > 0 ? min(100, round(($scheduledTotal / max(1, $employeeTotal)) * 100, 1)) : 98.5;
$budgetUsed = (float) ($dashboardSummary['budget_used'] ?? 76);
$complianceScore = $performance ? max(0, min(100, 100 - $avgDelay)) : 100;

$recentActivity = [];
if (!empty($requests)) {
    foreach (array_slice($requests, 0, 4) as $request) {
        $recentActivity[] = [
            'title' => ($request['employee_name'] ?? 'Employee') . ' submitted a ' . strtolower($request['request_type'] ?? 'shift request'),
            'time' => $request['request_date'] ?? $request['shift_date'] ?? 'Recently',
            'tone' => strtolower((string) ($request['status'] ?? 'info')),
        ];
    }
} else {
    $recentActivity = [
        ['title' => 'Week 52 schedule published', 'time' => '2 hours ago', 'tone' => 'success'],
        ['title' => 'Sarah Johnson requested shift swap', 'time' => '3 hours ago', 'tone' => 'info'],
        ['title' => 'Coverage gap on Friday 3-11pm', 'time' => '5 hours ago', 'tone' => 'warning'],
        ['title' => '12 new employees onboarded', 'time' => '1 day ago', 'tone' => 'success'],
    ];
}
?>
<section class="dashboard-surface director-dashboard-page">
    <div class="dashboard-inner">
        <div class="dashboard-hero">
            <div>
                <h1>Welcome back, Director</h1>
                <p class="muted">Here's what's happening with your workforce today</p>
            </div>
            <div class="dashboard-hero-actions">
                <button type="button" class="week-selector-pill" id="week-selector" data-week-start="<?= e($weekStart) ?>" data-week-end="<?= e($weekEnd) ?>">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M8 2V6M16 2V6M3 10H21M5 4H19C20.1046 4 21 4.89543 21 6V19C21 20.1046 20.1046 21 19 21H5C3.89543 21 3 20.1046 3 19V6C3 4.89543 3.89543 4 5 4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span>Week <?= e($weekStart) ?> → <?= e($weekEnd) ?></span>
                </button>
                <a class="btn ghost" href="/index.php?reset_section=1">Change Section</a>
            </div>
        </div>

        <div class="metric-grid">
            <div class="dashboard-card metric-card">
                <div class="metric-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M20 21V19C20 17.9391 19.5786 16.9217 18.8284 16.1716C18.0783 15.4214 17.0609 15 16 15H8C6.93913 15 5.92172 15.4214 5.17157 16.1716C4.42143 16.9217 4 17.9391 4 19V21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M12 11C14.2091 11 16 9.20914 16 7C16 4.79086 14.2091 3 12 3C9.79086 3 8 4.79086 8 7C8 9.20914 9.79086 11 12 11Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div class="metric-value"><?= e((string) $employeeMetric) ?></div>
                <div class="metric-label">Total Employees</div>
                <div class="metric-delta positive"><?= e($employeeDelta) ?></div>
            </div>
            <div class="dashboard-card metric-card">
                <div class="metric-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 1V23" stroke="currentColor" stroke-width="2"/>
                        <path d="M17 5H9.5C8.11929 5 7 6.11929 7 7.5C7 8.88071 8.11929 10 9.5 10H14.5C15.8807 10 17 11.1193 17 12.5C17 13.8807 15.8807 15 14.5 15H7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div class="metric-value"><?= e($laborCostLabel) ?></div>
                <div class="metric-label">Labor Cost</div>
                <div class="metric-delta negative"><?= e($laborDelta) ?></div>
            </div>
            <div class="dashboard-card metric-card">
                <div class="metric-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M16 7H22V3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M22 7C20.6667 5.66667 19.3333 4.33333 18 3C16.6667 1.66667 14.3333 1 12 1C6.47715 1 2 5.47715 2 11C2 16.5228 6.47715 21 12 21C17.5228 21 22 16.5228 22 11" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div class="metric-value"><?= e(number_format($fillRate, 1)) ?>%</div>
                <div class="metric-label">Fill Rate</div>
                <div class="metric-delta positive"><?= e($fillDelta) ?></div>
            </div>
            <div class="dashboard-card metric-card">
                <div class="metric-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="3" y="4" width="18" height="18" rx="4" stroke="currentColor" stroke-width="2"/>
                        <path d="M3 10H21" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </div>
                <div class="metric-value"><?= e((string) $openShifts) ?></div>
                <div class="metric-label">Open Shifts</div>
                <div class="metric-delta negative"><?= e($openDelta) ?></div>
            </div>
        </div>

        <div class="dashboard-section-header">
            <h3>Quick Access</h3>
        </div>
        <div class="quick-access-grid">
            <a class="dashboard-card quick-access-card" href="/dashboard/attendance.php">
                <div class="quick-access-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="3" y="4" width="18" height="18" rx="4" stroke="currentColor" stroke-width="2"/>
                        <path d="M8 2V6M16 2V6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </div>
                <div class="quick-access-title">Scheduling</div>
                <div class="quick-access-subtitle">Manage shifts and assignments</div>
                <div class="quick-access-meta"><?= e((string) ($scheduledTotal ?: 48)) ?> shifts this week</div>
            </a>
            <a class="dashboard-card quick-access-card" href="/dashboard/employees.php">
                <div class="quick-access-icon accent-blue">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M20 21V19C20 17.9391 19.5786 16.9217 18.8284 16.1716C18.0783 15.4214 17.0609 15 16 15H8C6.93913 15 5.92172 15.4214 5.17157 16.1716C4.42143 16.9217 4 17.9391 4 19V21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M12 11C14.2091 11 16 9.20914 16 7C16 4.79086 14.2091 3 12 3C9.79086 3 8 4.79086 8 7C8 9.20914 9.79086 11 12 11Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div class="quick-access-title">Team Management</div>
                <div class="quick-access-subtitle">Employees, roles &amp; permissions</div>
                <div class="quick-access-meta"><?= e((string) $employeeMetric) ?> active employees</div>
            </a>
            <a class="dashboard-card quick-access-card" href="/dashboard/reports.php">
                <div class="quick-access-icon accent-teal">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M4 19H20" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        <rect x="5" y="10" width="3" height="7" rx="1" stroke="currentColor" stroke-width="2"/>
                        <rect x="10.5" y="6" width="3" height="11" rx="1" stroke="currentColor" stroke-width="2"/>
                        <rect x="16" y="12" width="3" height="5" rx="1" stroke="currentColor" stroke-width="2"/>
                    </svg>
                </div>
                <div class="quick-access-title">Analytics</div>
                <div class="quick-access-subtitle">Reports and insights</div>
                <div class="quick-access-meta"><?= e((string) $reportTotal) ?> data points</div>
            </a>
            <a class="dashboard-card quick-access-card" href="/dashboard/attendance.php">
                <div class="quick-access-icon accent-purple">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/>
                        <path d="M12 7V12L15 14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </div>
                <div class="quick-access-title">Time Tracking</div>
                <div class="quick-access-subtitle">Hours, overtime &amp; attendance</div>
                <div class="quick-access-meta"><?= e(number_format($totalHours)) ?> hrs this month</div>
            </a>
            <a class="dashboard-card quick-access-card" href="/dashboard/departments.php">
                <div class="quick-access-icon accent-pink">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="3" y="4" width="7" height="16" rx="2" stroke="currentColor" stroke-width="2"/>
                        <rect x="14" y="4" width="7" height="16" rx="2" stroke="currentColor" stroke-width="2"/>
                    </svg>
                </div>
                <div class="quick-access-title">Locations</div>
                <div class="quick-access-subtitle">Multiple sites &amp; departments</div>
                <div class="quick-access-meta"><?= e((string) $departmentTotal) ?> locations</div>
            </a>
            <a class="dashboard-card quick-access-card" href="/dashboard/settings.php">
                <div class="quick-access-icon accent-gold">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 15.5C13.933 15.5 15.5 13.933 15.5 12C15.5 10.067 13.933 8.5 12 8.5C10.067 8.5 8.5 10.067 8.5 12C8.5 13.933 10.067 15.5 12 15.5Z" stroke="currentColor" stroke-width="2"/>
                        <path d="M19.4 15A1.65 1.65 0 0 0 19.73 16.82L19.77 16.86A2 2 0 1 1 16.94 19.69L16.9 19.65A1.65 1.65 0 0 0 15 19.4A1.65 1.65 0 0 0 14 20.95V21A2 2 0 1 1 10 21V20.92A1.65 1.65 0 0 0 9 19.4A1.65 1.65 0 0 0 7.1 19.65L7.06 19.69A2 2 0 1 1 4.23 16.86L4.27 16.82A1.65 1.65 0 0 0 4.6 15A1.65 1.65 0 0 0 3.05 14H3A2 2 0 1 1 3 10H3.08A1.65 1.65 0 0 0 4.6 9A1.65 1.65 0 0 0 4.27 7.18L4.23 7.14A2 2 0 1 1 7.06 4.31L7.1 4.35A1.65 1.65 0 0 0 9 4.6H9.05A1.65 1.65 0 0 0 10 3.05V3A2 2 0 1 1 14 3V3.08A1.65 1.65 0 0 0 15 4.6A1.65 1.65 0 0 0 16.9 4.35L16.94 4.31A2 2 0 1 1 19.77 7.14L19.73 7.18A1.65 1.65 0 0 0 19.4 9V9.05A1.65 1.65 0 0 0 20.95 10H21A2 2 0 1 1 21 14H20.92A1.65 1.65 0 0 0 19.4 15Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div class="quick-access-title">Settings</div>
                <div class="quick-access-subtitle">Company &amp; system preferences</div>
                <div class="quick-access-meta">Configure</div>
            </a>
        </div>

        <div class="dashboard-lower-grid">
            <div class="dashboard-card activity-card" id="director-activity">
                <div class="card-header">
                    <h3>Recent Activity</h3>
                </div>
                <div class="activity-list">
                    <?php foreach ($recentActivity as $activity): ?>
                        <div class="activity-item">
                            <div class="activity-icon <?= e($activity['tone']) ?>"></div>
                            <div class="activity-text">
                                <div class="activity-title"><?= e($activity['title']) ?></div>
                                <div class="activity-time"><?= e($activity['time']) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <a class="activity-link" href="/dashboard/reports.php">View All Activity</a>
            </div>

            <div class="dashboard-side-stack">
                <div class="dashboard-card system-health-card">
                    <div class="card-header">
                        <h3>System Health</h3>
                    </div>
                    <div class="health-item">
                        <div class="health-row">
                            <span>Coverage Rate</span>
                            <span><?= e(number_format($coverageRate, 1)) ?>%</span>
                        </div>
                        <div class="health-bar">
                            <span style="width: <?= e((string) $coverageRate) ?>%"></span>
                        </div>
                    </div>
                    <div class="health-item">
                        <div class="health-row">
                            <span>Budget Used</span>
                            <span><?= e(number_format($budgetUsed, 0)) ?>%</span>
                        </div>
                        <div class="health-bar info">
                            <span style="width: <?= e((string) $budgetUsed) ?>%"></span>
                        </div>
                    </div>
                    <div class="health-item">
                        <div class="health-row">
                            <span>Compliance Score</span>
                            <span><?= e(number_format($complianceScore, 0)) ?>%</span>
                        </div>
                        <div class="health-bar success">
                            <span style="width: <?= e((string) $complianceScore) ?>%"></span>
                        </div>
                    </div>
                </div>

                <div class="dashboard-card this-week-card">
                    <div class="card-header">
                        <h3>This Week</h3>
                    </div>
                    <div class="this-week-grid">
                        <div>
                            <span>Total Shifts</span>
                            <strong><?= e(number_format($totalShifts)) ?></strong>
                        </div>
                        <div>
                            <span>Total Hours</span>
                            <strong><?= e(number_format($totalHours)) ?></strong>
                        </div>
                        <div>
                            <span>Overtime Hours</span>
                            <strong><?= e(number_format($overtimeHours, 0)) ?></strong>
                        </div>
                        <div>
                            <span>Call-outs</span>
                            <strong><?= e((string) $callOuts) ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
