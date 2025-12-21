<?php
declare(strict_types=1);

$weekDates = [];
$weekStartDate = new DateTimeImmutable($weekStart);
for ($i = 0; $i < 7; $i++) {
    $weekDates[] = $weekStartDate->modify('+' . $i . ' day')->format('Y-m-d');
}
?>
<div class="dashboard-container">
    <!-- Modern Navigation Cards -->
    <div class="dashboard-nav-cards">
        <button class="nav-card active" data-section="overview">
            <div class="nav-card-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M3 9L12 2L21 9V20C21 20.5304 20.7893 21.0391 20.4142 21.4142C20.0391 21.7893 19.5304 22 19 22H5C4.46957 22 3.96086 21.7893 3.58579 21.4142C3.21071 21.0391 3 20.5304 3 20V9Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M9 22V12H15V22" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div class="nav-card-content">
                <div class="nav-card-title">Overview</div>
                <div class="nav-card-subtitle">Control center</div>
            </div>
        </button>
        
        <button class="nav-card" data-section="create-employee">
            <div class="nav-card-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M16 21V19C16 17.9391 15.5786 16.9217 14.8284 16.1716C14.0783 15.4214 13.0609 15 12 15H5C3.93913 15 2.92172 15.4214 2.17157 16.1716C1.42143 16.9217 1 17.9391 1 19V21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M8.5 11C10.7091 11 12.5 9.20914 12.5 7C12.5 4.79086 10.7091 3 8.5 3C6.29086 3 4.5 4.79086 4.5 7C4.5 9.20914 6.29086 11 8.5 11Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M23 21V19C22.9993 18.1137 22.7044 17.2528 22.1614 16.5523C21.6184 15.8519 20.8581 15.3516 20 15.13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M16 3.13C16.8604 3.35031 17.623 3.85071 18.1676 4.55232C18.7122 5.25392 19.0078 6.11683 19.0078 7.005C19.0078 7.89318 18.7122 8.75608 18.1676 9.45769C17.623 10.1593 16.8604 10.6597 16 10.88" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div class="nav-card-content">
                <div class="nav-card-title">Create Employee</div>
                <div class="nav-card-subtitle">Add team members</div>
            </div>
        </button>
        
        <button class="nav-card" data-section="manage-employees">
            <div class="nav-card-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M17 21V19C17 17.9391 16.5786 16.9217 15.8284 16.1716C15.0783 15.4214 14.0609 15 13 15H5C3.93913 15 2.92172 15.4214 2.17157 16.1716C1.42143 16.9217 1 17.9391 1 19V21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M9 11C11.2091 11 13 9.20914 13 7C13 4.79086 11.2091 3 9 3C6.79086 3 5 4.79086 5 7C5 9.20914 6.79086 11 9 11Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M23 21V19C22.9993 18.1137 22.7044 17.2528 22.1614 16.5523C21.6184 15.8519 20.8581 15.3516 20 15.13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M16 3.13C16.8604 3.35031 17.623 3.85071 18.1676 4.55232C18.7122 5.25392 19.0078 6.11683 19.0078 7.005C19.0078 7.89318 18.7122 8.75608 18.1676 9.45769C17.623 10.1593 16.8604 10.6597 16 10.88" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div class="nav-card-content">
                <div class="nav-card-title">Manage Employees</div>
                <div class="nav-card-subtitle">View, update, delete</div>
            </div>
        </button>
        
        <button class="nav-card" data-section="shift-requirements">
            <div class="nav-card-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M9 5H7C5.89543 5 5 5.89543 5 7V19C5 20.1046 5.89543 21 7 21H17C18.1046 21 19 20.1046 19 19V7C19 5.89543 18.1046 5 17 5H15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M9 5C9 4.46957 9.21071 3.96086 9.58579 3.58579C9.96086 3.21071 10.4696 3 11 3H13C13.5304 3 14.0391 3.21071 14.4142 3.58579C14.7893 3.96086 15 4.46957 15 5C15 5.53043 14.7893 6.03914 14.4142 6.41421C14.0391 6.78929 13.5304 7 13 7H11C10.4696 7 9.96086 6.78929 9.58579 6.41421C9.21071 6.03914 9 5.53043 9 5Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M9 12H15M9 16H13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div class="nav-card-content">
                <div class="nav-card-title">Shift Requirements</div>
                <div class="nav-card-subtitle">Define coverage</div>
            </div>
        </button>
        
        <button class="nav-card" data-section="shift-requests">
            <div class="nav-card-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M14 2H6C5.46957 2 4.96086 2.21071 4.58579 2.58579C4.21071 2.96086 4 3.46957 4 4V20C4 20.5304 4.21071 21.0391 4.58579 21.4142C4.96086 21.7893 5.46957 22 6 22H18C18.5304 22 19.0391 21.7893 19.4142 21.4142C19.7893 21.0391 20 20.5304 20 20V8L14 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M14 2V8H20" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M16 13H8M16 17H8M10 9H8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div class="nav-card-content">
                <div class="nav-card-title">Shift Requests</div>
                <div class="nav-card-subtitle">Review & approve</div>
            </div>
        </button>
        
        <button class="nav-card" data-section="weekly-schedule">
            <div class="nav-card-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M8 2V6M16 2V6M3 10H21M5 4H19C20.1046 4 21 4.89543 21 6V19C21 20.1046 20.1046 21 19 21H5C3.89543 21 3 20.1046 3 19V6C3 4.89543 3.89543 4 5 4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div class="nav-card-content">
                <div class="nav-card-title">Weekly Schedule</div>
                <div class="nav-card-subtitle">Generate & manage</div>
            </div>
        </button>
        
        <button class="nav-card" data-section="break-monitoring">
            <div class="nav-card-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M12 6V12L16 14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div class="nav-card-content">
                <div class="nav-card-title">Break Monitoring</div>
                <div class="nav-card-subtitle">Track breaks</div>
            </div>
        </button>
        
        <button class="nav-card" data-section="performance">
            <div class="nav-card-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M3 3V21H21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M7 16L12 11L16 15L21 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M21 10V3H14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div class="nav-card-content">
                <div class="nav-card-title">Performance</div>
                <div class="nav-card-subtitle">Analytics & metrics</div>
            </div>
        </button>
    </div>

    <!-- Main Content Area -->
    <main class="dashboard-content">
        <!-- Overview Section -->
        <section class="dashboard-section active" data-section="overview">
            <div class="card">
                <div class="hero-row">
                    <div>
                        <h2>Team Leader Control Center</h2>
                        <p>Full CRUD permissions for <?= e($user['section_name'] ?? 'your section') ?>.</p>
                    </div>
                    <div class="meta-row">
                        <span class="pill">Week <?= e($weekStart) ?> → <?= e($weekEnd) ?></span>
                        <a class="btn secondary small" href="/index.php?download=schedule">Export CSV</a>
                    </div>
                </div>
            </div>
            <!-- Pending Shift Requests Widget -->
            <div class="widget widget-requests" data-widget="requests" onclick="window.dashboard?.navigateToSection('shift-requests')">
                <div class="widget-header">
                    <div class="widget-icon" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M10 2V18M2 10H18" stroke="white" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <div class="widget-title-group">
                        <h3>Pending Requests</h3>
                        <span class="widget-subtitle">Requires attention</span>
                    </div>
                </div>
                <div class="widget-content">
                    <div class="widget-metric">
                        <span class="metric-value"><?= e($commandCenter['pending_requests']['total'] ?? 0) ?></span>
                        <span class="metric-label">Total</span>
                    </div>
                    <?php if (!empty($commandCenter['pending_requests']['high_priority'])): ?>
                    <div class="widget-alert">
                        <span class="alert-badge high"><?= e($commandCenter['pending_requests']['high_priority']) ?> High Priority</span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($commandCenter['pending_requests']['requests'])): ?>
                    <div class="widget-list">
                        <?php foreach (array_slice($commandCenter['pending_requests']['requests'], 0, 3) as $req): ?>
                        <div class="widget-list-item">
                            <span class="item-name"><?= e($req['employee_name'] ?? 'Unknown') ?></span>
                            <span class="item-meta"><?= e($req['shift_name'] ?? '') ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="widget-footer">
                    <a href="#shift-requests" class="widget-link">View all requests →</a>
                </div>
            </div>

            <!-- Coverage Gaps Widget -->
            <div class="widget widget-coverage" data-widget="coverage" onclick="window.dashboard?.navigateToSection('weekly-schedule')">
                <div class="widget-header">
                    <div class="widget-icon" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M10 18C14.4183 18 18 14.4183 18 10C18 5.58172 14.4183 2 10 2C5.58172 2 2 5.58172 2 10C2 14.4183 5.58172 18 10 18Z" stroke="white" stroke-width="2"/>
                            <path d="M10 6V10L13 12" stroke="white" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <div class="widget-title-group">
                        <h3>Coverage Gaps</h3>
                        <span class="widget-subtitle">Understaffed shifts</span>
                    </div>
                </div>
                <div class="widget-content">
                    <div class="widget-metric">
                        <span class="metric-value"><?= e(count($commandCenter['coverage_gaps'] ?? [])) ?></span>
                        <span class="metric-label">Gaps this week</span>
                    </div>
                    <?php if (!empty($commandCenter['coverage_gaps'])): ?>
                    <div class="widget-list">
                        <?php foreach (array_slice($commandCenter['coverage_gaps'], 0, 3) as $gap): ?>
                        <div class="widget-list-item">
                            <span class="item-name"><?= e((new DateTimeImmutable($gap['date']))->format('D, M j')) ?></span>
                            <span class="item-meta"><?= e($gap['shift_name']) ?>: <?= e($gap['assigned']) ?>/<?= e($gap['required']) ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="widget-footer">
                    <a href="#weekly-schedule" class="widget-link">Fix coverage →</a>
                </div>
            </div>

            <!-- Employees on Break Widget -->
            <div class="widget widget-breaks" data-widget="breaks" onclick="window.dashboard?.navigateToSection('break-monitoring')">
                <div class="widget-header">
                    <div class="widget-icon" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <circle cx="10" cy="10" r="8" stroke="white" stroke-width="2"/>
                            <path d="M10 6V10L13 12" stroke="white" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <div class="widget-title-group">
                        <h3>On Break</h3>
                        <span class="widget-subtitle">Currently away</span>
                    </div>
                </div>
                <div class="widget-content">
                    <div class="widget-metric">
                        <span class="metric-value"><?= e($commandCenter['on_break']['count'] ?? 0) ?></span>
                        <span class="metric-label">Active breaks</span>
                    </div>
                    <?php if (!empty($commandCenter['on_break']['employees'])): ?>
                    <div class="widget-list">
                        <?php foreach (array_slice($commandCenter['on_break']['employees'], 0, 3) as $break): ?>
                        <div class="widget-list-item">
                            <span class="item-name"><?= e($break['employee_name'] ?? 'Unknown') ?></span>
                            <span class="item-meta">Started <?= e($break['break_start'] ?? '') ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="widget-footer">
                    <a href="#break-monitoring" class="widget-link">Monitor breaks →</a>
                </div>
            </div>

            <!-- Unassigned Employees Widget -->
            <div class="widget widget-unassigned" data-widget="unassigned" onclick="window.dashboard?.navigateToSection('weekly-schedule')">
                <div class="widget-header">
                    <div class="widget-icon" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M10 2V18M2 10H18" stroke="white" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <div class="widget-title-group">
                        <h3>Unassigned</h3>
                        <span class="widget-subtitle">No shifts this week</span>
                    </div>
                </div>
                <div class="widget-content">
                    <div class="widget-metric">
                        <span class="metric-value"><?= e($commandCenter['unassigned']['count'] ?? 0) ?></span>
                        <span class="metric-label">Employees</span>
                    </div>
                    <?php if (!empty($commandCenter['unassigned']['employees'])): ?>
                    <div class="widget-list">
                        <?php foreach (array_slice($commandCenter['unassigned']['employees'], 0, 3) as $emp): ?>
                        <div class="widget-list-item">
                            <span class="item-name"><?= e($emp['full_name'] ?? 'Unknown') ?></span>
                            <span class="item-meta"><?= e($emp['employee_code'] ?? '') ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="widget-footer">
                    <a href="#weekly-schedule" class="widget-link">Assign shifts →</a>
                </div>
            </div>

            <!-- SLA Alerts Widget -->
            <?php if (!empty($commandCenter['sla_alerts'])): ?>
            <div class="widget widget-alerts" data-widget="alerts">
                <div class="widget-header">
                    <div class="widget-icon" style="background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M10 2L2 18H18L10 2Z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M10 12V8M10 14H10.01" stroke="white" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <div class="widget-title-group">
                        <h3>SLA Alerts</h3>
                        <span class="widget-subtitle">Requires action</span>
                    </div>
                </div>
                <div class="widget-content">
                    <div class="widget-metric">
                        <span class="metric-value"><?= e(count($commandCenter['sla_alerts'])) ?></span>
                        <span class="metric-label">Active alerts</span>
                    </div>
                    <div class="widget-list">
                        <?php foreach (array_slice($commandCenter['sla_alerts'], 0, 3) as $alert): ?>
                        <div class="widget-list-item alert-item" data-severity="<?= e($alert['severity']) ?>">
                            <span class="item-name"><?= e($alert['message']) ?></span>
                            <span class="item-badge severity-<?= e($alert['severity']) ?>"><?= e(ucfirst($alert['severity'])) ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Quick Actions -->
            <div class="quick-actions-section">
                <h3>Quick Actions</h3>
                <div class="quick-actions-grid">
                    <button class="quick-action-card" onclick="window.dashboard?.navigateToSection('shift-requests')">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <path d="M9 12L11 14L15 10M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span>Review Requests</span>
                    </button>
                    <button class="quick-action-card" onclick="window.dashboard?.navigateToSection('weekly-schedule')">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <path d="M8 2V6M16 2V6M3 10H21M5 4H19C20.1046 4 21 4.89543 21 6V19C21 20.1046 20.1046 21 19 21H5C3.89543 21 3 20.1046 3 19V6C3 4.89543 3.89543 4 5 4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span>Manage Schedule</span>
                    </button>
                    <button class="quick-action-card" onclick="window.dashboard?.navigateToSection('manage-employees')">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <path d="M17 21V19C17 17.9391 16.5786 16.9217 15.8284 16.1716C15.0783 15.4214 14.0609 15 13 15H5C3.93913 15 2.92172 15.4214 2.17157 16.1716C1.42143 16.9217 1 17.9391 1 19V21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M9 11C11.2091 11 13 9.20914 13 7C13 4.79086 11.2091 3 9 3C6.79086 3 5 4.79086 5 7C5 9.20914 6.79086 11 9 11Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span>View Team</span>
                    </button>
                    <button class="quick-action-card" onclick="window.dashboard?.navigateToSection('performance')">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <path d="M3 3V21H21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M7 16L12 11L16 15L21 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span>View Analytics</span>
                    </button>
                </div>
            </div>
        </section>

        <!-- Create Employee Section -->
        <section class="dashboard-section" data-section="create-employee">
            <div class="card">
    <div class="section-title">
        <h3>Create Employee</h3>
        <span>Add employees to this section</span>
    </div>
    <form method="post" action="/index.php" class="grid">
        <input type="hidden" name="action" value="create_employee">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="section_id" value="<?= e((string) $user['section_id']) ?>">
        <label>
            Full Name
            <input type="text" name="full_name" required>
        </label>
        <label>
            Employee Code
            <input type="text" name="employee_code" required>
        </label>
        <label>
            Username
            <input type="text" name="username" required>
        </label>
        <label>
            Email
            <input type="email" name="email">
        </label>
        <label>
            Password
            <input type="password" name="password" required>
        </label>
        <label>
            Role
            <select name="role_id" required>
                <?php foreach ($roles as $role): ?>
                                <?php if (in_array($role['role_name'], ['Employee', 'Senior'], true)): ?>
                                    <option value="<?= e((string) $role['id']) ?>"><?= e($role['role_name']) ?></option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
        </label>
        <label>
            Seniority Level
            <input type="number" name="seniority_level" min="0" value="0">
            <small class="muted">Higher values place employees earlier in coverage sorting.</small>
        </label>
        <div class="form-actions">
            <button type="submit" class="btn">Create Employee</button>
        </div>
    </form>
            </div>
        </section>

        <!-- Manage Employees Section -->
        <section class="dashboard-section" data-section="manage-employees">
            <div class="card">
                <div class="section-title">
                    <h3>Manage Employees</h3>
                    <span><?= e(count($employees ?? [])) ?> employees in this section</span>
                </div>
                <?php if (!empty($employees)): ?>
                    <table>
                        <thead>
                        <tr>
                            <th>Name</th>
                            <th>Employee Code</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($employees as $employee): ?>
                            <tr>
                                <td><?= e($employee['full_name']) ?></td>
                                <td><?= e($employee['employee_code']) ?></td>
                                <td><?= e($employee['username']) ?></td>
                                <td><?= e($employee['email'] ?? '-') ?></td>
                                <td><span class="pill"><?= e($employee['role_name']) ?></span></td>
                                <td class="table-actions">
                                    <form method="post" action="/index.php" class="inline" style="display: inline-block;">
                                        <input type="hidden" name="action" value="update_employee">
                                        <input type="hidden" name="employee_id" value="<?= e((string) $employee['id']) ?>">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                        <button type="submit" class="btn secondary small">Update</button>
                                    </form>
                                    <form method="post" action="/index.php" class="inline" style="display: inline-block; margin-left: 0.5rem;" onsubmit="return confirm('Are you sure you want to delete this employee?');">
                                        <input type="hidden" name="action" value="delete_employee">
                                        <input type="hidden" name="employee_id" value="<?= e((string) $employee['id']) ?>">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                        <button type="submit" class="btn secondary small" style="background: var(--danger);">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-title">No employees found</div>
                        <p class="empty-state-text">Create your first employee using the "Create Employee" section above.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Shift Requirements Section -->
        <section class="dashboard-section" data-section="shift-requirements">
            <div class="card">
    <div class="section-title">
        <h3>Shift Requirements</h3>
        <span>Define required coverage per shift</span>
    </div>
    <form method="post" action="/index.php">
        <input type="hidden" name="action" value="save_requirements">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <div class="table-wrapper">
            <table>
                <thead>
                <tr>
                    <th>Date</th>
                    <?php foreach ($shiftTypes as $shiftType): ?>
                        <th><?= e($shiftType['shift_type_name']) ?></th>
                    <?php endforeach; ?>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($weekDates as $date): ?>
                    <tr>
                        <td><?= e($date) ?></td>
                        <?php foreach ($shiftTypes as $shiftType): ?>
                            <?php
                                $existing = 0;
                                foreach ($requirements as $requirement) {
                                    if ($requirement['shift_date'] === $date && (int) $requirement['shift_type_id'] === (int) $shiftType['shift_type_id']) {
                                        $existing = (int) $requirement['required_count'];
                                        break;
                                    }
                                }
                            ?>
                            <td>
                                <input type="number" name="requirements[<?= e((string) $shiftType['shift_type_id']) ?>][<?= e($date) ?>]" min="0" value="<?= e((string) $existing) ?>">
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn">Save Requirements</button>
        </div>
    </form>
            </div>
</section>

        <!-- Shift Requests Section -->
        <section class="dashboard-section" data-section="shift-requests">
            <div class="card">
                <div class="section-title">
                    <h3>Shift Requests</h3>
                    <span><?= e(count($requests)) ?> requests awaiting review</span>
                </div>
                <table>
                    <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Date</th>
                        <th>Shift</th>
                        <th>Pattern</th>
                        <th>Importance</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($requests as $request): ?>
                        <tr>
                            <td><?= e($request['employee_name']) ?></td>
                            <td><?= e($request['submit_date']) ?></td>
                            <td><?= e($request['shift_name']) ?></td>
                            <td><?= e($request['pattern_name']) ?></td>
                            <td><?= e($request['importance_level']) ?></td>
                            <td><span class="status <?= strtolower($request['status']) ?>"><?= e($request['status']) ?></span></td>
                            <td>
                                <form method="post" action="/index.php" class="inline">
                                    <input type="hidden" name="action" value="update_request_status">
                                    <input type="hidden" name="request_id" value="<?= e((string) $request['id']) ?>">
                                    <input type="hidden" name="status" value="APPROVED">
                                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                    <button type="submit" class="btn small">Approve</button>
                                </form>
                                <form method="post" action="/index.php" class="inline">
                                    <input type="hidden" name="action" value="update_request_status">
                                    <input type="hidden" name="request_id" value="<?= e((string) $request['id']) ?>">
                                    <input type="hidden" name="status" value="DECLINED">
                                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                    <button type="submit" class="btn danger small">Decline</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Weekly Schedule Section -->
        <section class="dashboard-section" data-section="weekly-schedule">
            <!-- Shift Requests Panel -->
            <div class="card requests-panel">
                <div class="section-title">
                    <h3>Shift Requests for Next Week</h3>
                    <span><?= e(count($requests)) ?> pending requests</span>
                </div>
                <?php if (!empty($requests)): ?>
                    <div class="requests-list">
                        <?php 
                        // Group requests by employee and date
                        $requestsByEmployee = [];
                        foreach ($requests as $request) {
                            $empId = (int) ($request['employee_id'] ?? 0);
                            $date = $request['request_date'] ?? '';
                            if (!isset($requestsByEmployee[$empId])) {
                                $requestsByEmployee[$empId] = [];
                            }
                            if (!isset($requestsByEmployee[$empId][$date])) {
                                $requestsByEmployee[$empId][$date] = [];
                            }
                            $requestsByEmployee[$empId][$date][] = $request;
                        }
                        ?>
                        <?php foreach ($requestsByEmployee as $empId => $employeeRequests): ?>
                            <?php 
                            $firstRequest = reset($employeeRequests);
                            $firstRequest = reset($firstRequest);
                            $employeeName = $firstRequest['employee_name'] ?? 'Unknown';
                            ?>
                            <div class="request-group" data-employee-id="<?= e((string) $empId) ?>">
                                <div class="request-employee-header">
                                    <strong><?= e($employeeName) ?></strong>
                                    <span class="request-count"><?= e(count(array_merge(...array_values($employeeRequests)))) ?> request(s)</span>
                                </div>
                                <div class="request-items">
                                    <?php foreach ($employeeRequests as $date => $dateRequests): ?>
                                        <?php foreach ($dateRequests as $request): ?>
                                            <div class="request-item" data-request-id="<?= e((string) $request['id']) ?>" data-employee-id="<?= e((string) $empId) ?>" data-date="<?= e($date) ?>" data-shift-id="<?= e((string) ($request['shift_definition_id'] ?? 0)) ?>">
                                                <div class="request-info">
                                                    <div class="request-date-shift">
                                                        <span class="request-date"><?= e((new DateTimeImmutable($date))->format('D, M j')) ?></span>
                                                        <span class="request-shift"><?= e($request['shift_name'] ?? 'Day Off') ?></span>
                                                        <?php if (!empty($request['importance_level']) && $request['importance_level'] !== 'MEDIUM'): ?>
                                                            <span class="pill importance-<?= strtolower($request['importance_level']) ?>"><?= e($request['importance_level']) ?></span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <?php if (!empty($request['reason'])): ?>
                                                        <div class="request-reason"><?= e($request['reason']) ?></div>
                                                    <?php endif; ?>
                                                    <div class="request-status">
                                                        <span class="status <?= strtolower($request['status']) ?>"><?= e($request['status']) ?></span>
                                                    </div>
        </div>
                                                <div class="request-actions">
                                                    <button type="button" class="btn-assign-request btn-small" 
                                                            data-request-id="<?= e((string) $request['id']) ?>"
                                                            data-employee-id="<?= e((string) $empId) ?>"
                                                            data-date="<?= e($date) ?>"
                                                            data-shift-id="<?= e((string) ($request['shift_definition_id'] ?? 0)) ?>"
                                                            title="Assign to schedule">
                                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                            <path d="M8 2V14M2 8H14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                        </svg>
                                                        Assign
                                                    </button>
                                                    <form method="post" action="/index.php" class="inline request-status-form">
                                                        <input type="hidden" name="action" value="update_request_status">
                                                        <input type="hidden" name="request_id" value="<?= e((string) $request['id']) ?>">
                                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                                        <input type="hidden" name="status" value="APPROVED">
                                                        <button type="submit" class="btn-small btn-approve" title="Approve request">
                                                            ✓ Approve
                                                        </button>
                                                    </form>
                                                    <form method="post" action="/index.php" class="inline request-status-form">
                                                        <input type="hidden" name="action" value="update_request_status">
                                                        <input type="hidden" name="request_id" value="<?= e((string) $request['id']) ?>">
                                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                                        <input type="hidden" name="status" value="PENDING">
                                                        <button type="submit" class="btn-small btn-pending" title="Keep pending">
                                                            ⏱ Pending
                                                        </button>
                                                    </form>
                                                    <form method="post" action="/index.php" class="inline request-status-form">
                                                        <input type="hidden" name="action" value="update_request_status">
                                                        <input type="hidden" name="request_id" value="<?= e((string) $request['id']) ?>">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                                        <input type="hidden" name="status" value="DECLINED">
                                                        <button type="submit" class="btn-small btn-decline" title="Decline request">
                                                            ✗ Decline
                                                        </button>
        </form>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-title">No shift requests</div>
                        <p class="empty-state-text">All requests have been processed or no requests submitted for this week.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Schedule Analytics -->
            <?php
            // Calculate analytics
            $totalShifts = count($schedule);
            $totalEmployees = count($uniqueEmployees);
            $totalHours = array_sum($employeeHours);
            $avgHoursPerEmployee = $totalEmployees > 0 ? $totalHours / $totalEmployees : 0;
            $conflicts = []; // Will be populated by conflict detection
            
            // Detect conflicts (overlapping shifts for same employee)
            $employeeShiftsByDate = [];
            foreach ($schedule as $entry) {
                if (empty($entry['employee_id']) || empty($entry['shift_date'])) continue;
                $empId = (int) $entry['employee_id'];
                $date = $entry['shift_date'];
                if (!isset($employeeShiftsByDate[$empId])) {
                    $employeeShiftsByDate[$empId] = [];
                }
                if (!isset($employeeShiftsByDate[$empId][$date])) {
                    $employeeShiftsByDate[$empId][$date] = [];
                }
                $employeeShiftsByDate[$empId][$date][] = $entry;
            }
            
            // Check for overlapping shifts
            foreach ($employeeShiftsByDate as $empId => $dates) {
                foreach ($dates as $date => $shifts) {
                    if (count($shifts) > 1) {
                        // Check if shifts overlap
                        for ($i = 0; $i < count($shifts); $i++) {
                            for ($j = $i + 1; $j < count($shifts); $j++) {
                                $shift1 = $shifts[$i];
                                $shift2 = $shifts[$j];
                                $start1 = $shift1['start_time'] ?? '';
                                $end1 = $shift1['end_time'] ?? '';
                                $start2 = $shift2['start_time'] ?? '';
                                $end2 = $shift2['end_time'] ?? '';
                                
                                if ($start1 && $end1 && $start2 && $end2) {
                                    $start1Time = strtotime($start1);
                                    $end1Time = strtotime($end1);
                                    $start2Time = strtotime($start2);
                                    $end2Time = strtotime($end2);
                                    
                                    // Check for overlap
                                    if (($start1Time < $end2Time && $end1Time > $start2Time)) {
                                        $conflicts[] = [
                                            'employee_id' => $empId,
                                            'employee_name' => $shift1['employee_name'] ?? 'Unknown',
                                            'date' => $date,
                                            'shift1' => $shift1['shift_name'] ?? '',
                                            'shift2' => $shift2['shift_name'] ?? '',
                                        ];
                                    }
                                }
                            }
                        }
                    }
                }
            }
            ?>
            <div class="card">
                <div class="analytics-grid">
                    <div class="analytics-card">
                        <div class="analytics-card-title">Total Shifts</div>
                        <div class="analytics-card-value"><?= e($totalShifts) ?></div>
                        <div class="analytics-card-change">This week</div>
                    </div>
                    <div class="analytics-card">
                        <div class="analytics-card-title">Employees Scheduled</div>
                        <div class="analytics-card-value"><?= e($totalEmployees) ?></div>
                        <div class="analytics-card-change">of <?= e(count($employees)) ?> total</div>
                    </div>
                    <div class="analytics-card">
                        <div class="analytics-card-title">Total Hours</div>
                        <div class="analytics-card-value"><?= e(number_format($totalHours, 1)) ?></div>
                        <div class="analytics-card-change">This week</div>
                    </div>
                    <div class="analytics-card">
                        <div class="analytics-card-title">Avg Hours/Employee</div>
                        <div class="analytics-card-value"><?= e(number_format($avgHoursPerEmployee, 1)) ?></div>
                        <div class="analytics-card-change">Per employee</div>
                    </div>
                    <?php if (!empty($conflicts)): ?>
                    <div class="analytics-card" style="border-color: #ef4444; background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);">
                        <div class="analytics-card-title">Conflicts Detected</div>
                        <div class="analytics-card-value" style="color: #dc2626;"><?= e(count($conflicts)) ?></div>
                        <div class="analytics-card-change" style="color: #b91c1c;">Needs attention</div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <?php if (!empty($conflicts)): ?>
                <div class="card" style="margin-top: 1rem; border-color: #ef4444;">
                    <div class="section-title">
                        <h3 style="color: #dc2626;">⚠️ Schedule Conflicts</h3>
                        <span>Overlapping shifts detected</span>
    </div>
    <table>
        <thead>
        <tr>
                                <th>Employee</th>
            <th>Date</th>
                                <th>Conflicting Shifts</th>
        </tr>
        </thead>
        <tbody>
                            <?php foreach ($conflicts as $conflict): ?>
                            <tr>
                                <td><?= e($conflict['employee_name']) ?></td>
                                <td><?= e((new DateTimeImmutable($conflict['date']))->format('D, M j')) ?></td>
                                <td>
                                    <span class="pill" style="background: #fee2e2; color: #dc2626;"><?= e($conflict['shift1']) ?></span>
                                    <span class="pill" style="background: #fee2e2; color: #dc2626;"><?= e($conflict['shift2']) ?></span>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
                </div>
                <?php endif; ?>
            </div>

            <div class="card">
                <!-- Schedule Header with Controls -->
                <div class="schedule-header">
                    <div class="schedule-controls">
                        <div class="date-range-selector">
                            <input type="date" id="schedule-start-date" value="<?= e($weekStart) ?>" class="date-input">
                            <span class="date-separator">–</span>
                            <input type="date" id="schedule-end-date" value="<?= e($weekEnd) ?>" class="date-input">
                        </div>
                        <div class="filter-input-wrapper">
                            <input type="text" id="schedule-filter" placeholder="Filter..." class="filter-input">
                        </div>
                    </div>
                    <div class="quick-actions-toolbar">
                        <button type="button" class="quick-action-btn" id="bulk-select-btn" title="Select multiple shifts">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" style="display: inline-block; margin-right: 0.25rem; vertical-align: middle;">
                                <path d="M2 4L6 8L14 0" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Bulk Select
                        </button>
                        <button type="button" class="quick-action-btn" id="copy-week-btn" title="Copy this week's schedule">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" style="display: inline-block; margin-right: 0.25rem; vertical-align: middle;">
                                <path d="M4 2H12C12.5304 2 13.0391 2.21071 13.4142 2.58579C13.7893 2.96086 14 3.46957 14 4V12C14 12.5304 13.7893 13.0391 13.4142 13.4142C13.0391 13.7893 12.5304 14 12 14H4C3.46957 14 2.96086 13.7893 2.58579 13.4142C2.21071 13.0391 2 12.5304 2 12V4C2 3.46957 2.21071 2.96086 2.58579 2.58579C2.96086 2.21071 3.46957 2 4 2Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Copy Week
                        </button>
                        <button type="button" class="quick-action-btn" id="clear-conflicts-btn" title="Highlight conflicts" style="<?= !empty($conflicts) ? 'background: #fee2e2; color: #dc2626;' : '' ?>">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" style="display: inline-block; margin-right: 0.25rem; vertical-align: middle;">
                                <path d="M8 1V15M1 8H15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <?= !empty($conflicts) ? count($conflicts) . ' Conflicts' : 'Check Conflicts' ?>
                        </button>
                    </div>
                    <div class="schedule-actions">
                        <form method="post" action="/index.php" class="inline">
                            <input type="hidden" name="action" value="generate_schedule">
                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                            <button type="submit" class="btn secondary">Generate Schedule</button>
                        </form>
                        <button type="button" class="btn btn-primary" id="assign-shifts-btn">
                            <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M9 3V15M3 9H15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            Assign Shifts
                        </button>
                        <a href="/index.php?download=schedule&week_start=<?= e($weekStart) ?>&week_end=<?= e($weekEnd) ?>" class="btn secondary">
                            <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M15 11.25V15C15 15.3978 14.842 15.7794 14.5607 16.0607C14.2794 16.342 13.8978 16.5 13.5 16.5H4.5C4.10218 16.5 3.72064 16.342 3.43934 16.0607C3.15804 15.7794 3 15.3978 3 15V11.25M12 7.5L9 4.5M9 4.5L6 7.5M9 4.5V11.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Export CSV
                        </a>
                    </div>
                </div>

                <!-- Schedule Table -->
                <?php
                // Transform schedule data: group by employee and day
                $employeeSchedule = [];
                $employeeHours = [];
                
                // Get unique employees from schedule
                $uniqueEmployees = [];
                foreach ($schedule as $entry) {
                    if (!empty($entry['employee_id']) && !empty($entry['employee_name'])) {
                        $empId = (int) $entry['employee_id'];
                        if (!isset($uniqueEmployees[$empId])) {
                            $uniqueEmployees[$empId] = [
                                'id' => $empId,
                                'name' => $entry['employee_name'],
                                'code' => $entry['employee_code'] ?? '',
                            ];
                        }
                    }
                }
                
                // Also include all employees from the section
                foreach ($employees as $emp) {
                    $empId = (int) $emp['id'];
                    if (!isset($uniqueEmployees[$empId])) {
                        $uniqueEmployees[$empId] = [
                            'id' => $empId,
                            'name' => $emp['full_name'],
                            'code' => $emp['employee_code'],
                        ];
                    }
                }
                
                // Group schedule entries by employee and date
                foreach ($schedule as $entry) {
                    if (empty($entry['employee_id']) || empty($entry['shift_date'])) {
                        continue;
                    }
                    $empId = (int) $entry['employee_id'];
                    $date = $entry['shift_date'];
                    
                    if (!isset($employeeSchedule[$empId])) {
                        $employeeSchedule[$empId] = [];
                    }
                    if (!isset($employeeSchedule[$empId][$date])) {
                        $employeeSchedule[$empId][$date] = [];
                    }
                    
                    // Get shift definition details for times
                    $shiftDef = null;
                    foreach ($shiftDefinitions as $def) {
                        if ((int) $def['definition_id'] === (int) ($entry['shift_definition_id'] ?? 0)) {
                            $shiftDef = $def;
                            break;
                        }
                    }
                    
                    $employeeSchedule[$empId][$date][] = [
                        'shift_name' => $entry['shift_name'] ?? '',
                        'start_time' => $shiftDef['start_time'] ?? '',
                        'end_time' => $shiftDef['end_time'] ?? '',
                        'category' => $entry['shift_category'] ?? '',
                        'assignment_id' => $entry['assignment_id'] ?? null,
                        'notes' => $entry['notes'] ?? '',
                        'shift_definition_id' => $entry['shift_definition_id'] ?? null,
                    ];
                    
                    // Calculate hours (use shift definition duration or default to 8)
                    if (!isset($employeeHours[$empId])) {
                        $employeeHours[$empId] = 0;
                    }
                    $duration = $shiftDef['duration_hours'] ?? 8.0;
                    $employeeHours[$empId] += (float) $duration;
                }
                
                // Generate week dates
                $weekDates = [];
                $startDate = new DateTimeImmutable($weekStart);
                for ($i = 0; $i < 7; $i++) {
                    $date = $startDate->modify('+' . $i . ' day');
                    $weekDates[] = [
                        'date' => $date->format('Y-m-d'),
                        'day_name' => $date->format('l'),
                        'day_number' => $date->format('j'),
                    ];
                }
                ?>
                
                <div class="schedule-table-wrapper">
                    <table class="schedule-table">
                        <thead>
                            <tr>
                                <th class="employee-col">Employee</th>
                                <?php foreach ($weekDates as $dayInfo): ?>
                                    <th class="day-col">
                                        <div class="day-header">
                                            <span class="day-name"><?= e($dayInfo['day_name']) ?></span>
                                            <span class="day-number"><?= e($dayInfo['day_number']) ?></span>
                                        </div>
                                    </th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($uniqueEmployees)): ?>
                                <tr>
                                    <td colspan="8" class="empty-schedule">
                                        <div class="empty-state">
                                            <div class="empty-state-title">No schedule data</div>
                                            <p class="empty-state-text">Generate a schedule or assign shifts to employees to see the weekly view.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($uniqueEmployees as $empId => $employee): ?>
                                    <tr class="employee-row" data-employee-id="<?= e((string) $empId) ?>" data-employee-name="<?= e(strtolower($employee['name'])) ?>">
                                        <td class="employee-cell">
                                            <div class="employee-info">
                                                <div class="employee-avatar">
                                                    <?= strtoupper(substr($employee['name'], 0, 1)) ?>
                                                </div>
                                                <div class="employee-details">
                                                    <div class="employee-name"><?= e($employee['name']) ?></div>
                                                    <div class="employee-hours">
                                                        <?= e(number_format($employeeHours[$empId] ?? 0, 1)) ?> hours this week
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <?php foreach ($weekDates as $dayInfo): ?>
                                            <?php
                                            $dayShifts = $employeeSchedule[$empId][$dayInfo['date']] ?? [];
                                            // Check if this cell has conflicts
                                            $hasConflict = false;
                                            foreach ($conflicts as $conflict) {
                                                if ($conflict['employee_id'] === $empId && $conflict['date'] === $dayInfo['date']) {
                                                    $hasConflict = true;
                                                    break;
                                                }
                                            }
                                            ?>
                                            <td class="shift-cell <?= $hasConflict ? 'shift-conflict' : '' ?>" data-date="<?= e($dayInfo['date']) ?>" data-employee-id="<?= e((string) $empId) ?>">
                                                <?php if (empty($dayShifts)): ?>
                                                    <div class="shift-empty">
                                                        <button type="button" class="btn-assign-shift" 
                                                                data-date="<?= e($dayInfo['date']) ?>"
                                                                data-employee-id="<?= e((string) $empId) ?>"
                                                                title="Click to assign shift">
                                                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                                                                <path d="M7 1V13M1 7H13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="shift-pills">
                                                        <?php foreach ($dayShifts as $shift): ?>
                                                            <?php
                                                            // Determine shift color based on category or notes
                                                            $shiftClass = 'shift-pill';
                                                            $shiftLabel = $shift['shift_name'];
                                                            
                                                            // Check for special statuses in notes
                                                            if (!empty($shift['notes'])) {
                                                                $notes = strtolower($shift['notes']);
                                                                if (strpos($notes, 'vacation') !== false) {
                                                                    $shiftClass .= ' shift-vacation';
                                                                    $shiftLabel = 'Vacation';
                                                                } elseif (strpos($notes, 'medical') !== false || strpos($notes, 'leave') !== false) {
                                                                    $shiftClass .= ' shift-medical';
                                                                    $shiftLabel = 'Medical Leave';
                                                                } elseif (strpos($notes, 'moving') !== false) {
                                                                    $shiftClass .= ' shift-moving';
                                                                    $shiftLabel = 'Moving';
                                                                }
                                                            }
                                                            
                                                            // Color based on category
                                                            if (empty($shift['notes']) || strpos(strtolower($shift['notes'] ?? ''), 'vacation') === false) {
                                                                switch (strtoupper($shift['category'] ?? '')) {
                                                                    case 'AM':
                                                                        $shiftClass .= ' shift-am';
                                                                        break;
                                                                    case 'PM':
                                                                        $shiftClass .= ' shift-pm';
                                                                        break;
                                                                    case 'MID':
                                                                        $shiftClass .= ' shift-mid';
                                                                        break;
                                                                    default:
                                                                        $shiftClass .= ' shift-default';
                                                                }
                                                            }
                                                            
                                                            // Format time display
                                                            $timeDisplay = '';
                                                            if (!empty($shift['start_time']) && !empty($shift['end_time'])) {
                                                                $startTime = date('H:i', strtotime($shift['start_time']));
                                                                $endTime = date('H:i', strtotime($shift['end_time']));
                                                                $timeDisplay = $startTime . ' - ' . $endTime;
                                                            } elseif (!empty($shift['start_time'])) {
                                                                $timeDisplay = date('H:i', strtotime($shift['start_time']));
                                                            }
                                                            ?>
                                                            <div class="<?= e($shiftClass) ?> shift-editable" 
                                                                 data-assignment-id="<?= e((string) ($shift['assignment_id'] ?? '')) ?>"
                                                                 data-shift-def-id="<?= e((string) ($shift['shift_definition_id'] ?? '')) ?>"
                                                                 data-date="<?= e($dayInfo['date']) ?>"
                                                                 data-employee-id="<?= e((string) $empId) ?>"
                                                                 title="<?= e($shift['notes'] ?? 'Click to edit') ?>">
                                                                <?php if (!empty($timeDisplay) && strpos($shiftLabel, 'Vacation') === false && strpos($shiftLabel, 'Medical') === false && strpos($shiftLabel, 'Moving') === false): ?>
                                                                    <span class="shift-time-start"><?= e(explode(' - ', $timeDisplay)[0]) ?></span>
                                                                    <span class="shift-time-end"><?= e(explode(' - ', $timeDisplay)[1] ?? '') ?></span>
                                                                <?php else: ?>
                                                                    <span class="shift-label"><?= e($shiftLabel) ?></span>
                                                                <?php endif; ?>
                                                                <button type="button" class="shift-edit-btn" title="Edit shift">
                                                                    <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
                                                                        <path d="M8.5 1.5L10.5 3.5M9 1L2 8V10H4L11 3L9 1Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                                    </svg>
                                                                </button>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Assignment Modal -->
                <div id="assign-modal" class="modal" style="display: none;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3>Assign Shift</h3>
                            <button type="button" class="modal-close">&times;</button>
                        </div>
                        <form id="assign-shift-form" method="post" action="/index.php">
                            <input type="hidden" name="action" value="assign_shift">
                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                            <input type="hidden" name="date" id="assign-date">
                            <input type="hidden" name="request_id" id="assign-request-id">
                            
                            <div class="form-group">
                                <label>Employee</label>
                                <select name="employee_id" id="assign-employee-select" required>
                                    <option value="">Select Employee</option>
                                    <?php foreach ($employees as $emp): ?>
                                        <option value="<?= e((string) $emp['id']) ?>"><?= e($emp['full_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Shift Type</label>
                                <select name="shift_definition_id" id="assign-shift-def" required>
                                    <option value="">Select Shift</option>
                                    <?php foreach ($shiftDefinitions as $def): ?>
                                        <option value="<?= e((string) $def['definition_id']) ?>" 
                                                data-start="<?= e($def['start_time'] ?? '') ?>"
                                                data-end="<?= e($def['end_time'] ?? '') ?>">
                                            <?= e($def['definition_name']) ?> (<?= e($def['shift_type_name'] ?? '') ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Custom Start Time (optional)</label>
                                <input type="time" name="custom_start_time" id="assign-start-time" placeholder="HH:MM">
                            </div>
                            
                            <div class="form-group">
                                <label>Custom End Time (optional)</label>
                                <input type="time" name="custom_end_time" id="assign-end-time" placeholder="HH:MM">
                            </div>
                            
                            <div class="form-group">
                                <label>Notes (optional)</label>
                                <textarea name="notes" id="assign-notes" rows="2" placeholder="Add notes..."></textarea>
                            </div>
                            
                            <div class="form-actions">
                                <button type="button" class="btn secondary modal-cancel">Cancel</button>
                                <button type="submit" class="btn btn-primary">Assign Shift</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Shift Swap Modal -->
            <div id="swap-modal" class="modal" style="display: none;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>Swap Shifts</h3>
                        <button type="button" class="modal-close">&times;</button>
                    </div>
                    <form id="swap-shift-form" method="post" action="/index.php">
                        <input type="hidden" name="action" value="swap_shifts">
                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                        <input type="hidden" name="assignment1_id" id="swap-assignment1-id">
                        <input type="hidden" name="assignment2_id" id="swap-assignment2-id">
                        
                        <div class="form-group">
                            <label>Employee 1</label>
                            <select name="employee1_id" id="swap-employee1-select" required>
                                <option value="">Select Employee</option>
                                <?php foreach ($employees as $emp): ?>
                                    <option value="<?= e((string) $emp['id']) ?>"><?= e($emp['full_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Employee 2</label>
                            <select name="employee2_id" id="swap-employee2-select" required>
                                <option value="">Select Employee</option>
                                <?php foreach ($employees as $emp): ?>
                                    <option value="<?= e((string) $emp['id']) ?>"><?= e($emp['full_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Date</label>
                            <input type="date" name="swap_date" id="swap-date" required>
                        </div>
                        
                        <div class="form-actions">
                            <button type="button" class="btn secondary modal-cancel">Cancel</button>
                            <button type="submit" class="btn btn-primary">Swap Shifts</button>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        <!-- Break Monitoring Section -->
        <section class="dashboard-section" data-section="break-monitoring">
            <div class="card">
    <div class="section-title">
        <h3>Break Monitoring</h3>
        <span>Track active breaks and delays</span>
    </div>
    <table>
        <thead>
        <tr>
            <th>Employee</th>
            <th>Shift</th>
            <th>Break Start</th>
            <th>Break End</th>
            <th>Delay (min)</th>
            <th>Status</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($breaks as $break): ?>
            <tr>
                <td><?= e($break['employee_name']) ?></td>
                <td><?= e($break['shift_name']) ?></td>
                <td><?= e($break['break_start']) ?></td>
                <td><?= e($break['break_end']) ?></td>
                <td><?= e((string) $break['delay_minutes']) ?></td>
                <td><?= e($break['status']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
            </div>
</section>

        <!-- Performance Analytics Section -->
        <section class="dashboard-section" data-section="performance">
            <div class="card">
    <div class="section-title">
        <h3>Performance Analytics</h3>
        <span>Month-to-date delay summary</span>
    </div>
    <table>
        <thead>
        <tr>
            <th>Employee</th>
            <th>Days Worked</th>
            <th>Total Delay (min)</th>
            <th>Average Delay</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($performance as $row): ?>
            <tr>
                <td><?= e($row['employee_name']) ?></td>
                <td><?= e((string) $row['days_worked']) ?></td>
                <td><?= e((string) $row['total_delay_minutes']) ?></td>
                <td><?= e((string) $row['average_delay_minutes']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
            </div>
</section>
    </main>
</div>
