<?php
declare(strict_types=1);
?>
<div class="dashboard-container">
    <!-- Modern Navigation Cards -->
    <div class="dashboard-nav-cards">
        <button type="button" class="nav-card active" data-section="overview">
            <div class="nav-card-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M3 9L12 2L21 9V20C21 20.5304 20.7893 21.0391 20.4142 21.4142C20.0391 21.7893 19.5304 22 19 22H5C4.46957 22 3.96086 21.7893 3.58579 21.4142C3.21071 21.0391 3 20.5304 3 20V9Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M9 22V12H15V22" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div class="nav-card-content">
                <div class="nav-card-title">Overview</div>
                <div class="nav-card-subtitle">Section tracking</div>
            </div>
        </button>
        
        <button type="button" class="nav-card" data-section="create-employee">
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
                <div class="nav-card-subtitle">Add seniors or staff</div>
            </div>
        </button>

        <button type="button" class="nav-card" data-section="weekly-schedule">
            <div class="nav-card-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M8 2V6M16 2V6M3 10H21M5 4H19C20.1046 4 21 4.89543 21 6V19C21 20.1046 20.1046 21 19 21H5C3.89543 21 3 20.1046 3 19V6C3 4.89543 3.89543 4 5 4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div class="nav-card-content">
                <div class="nav-card-title">Weekly Schedule</div>
                <div class="nav-card-subtitle">View schedules</div>
            </div>
        </button>
        
        <button type="button" class="nav-card" data-section="shift-requests">
            <div class="nav-card-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M14 2H6C5.46957 2 4.96086 2.21071 4.58579 2.58579C4.21071 2.96086 4 3.46957 4 4V20C4 20.5304 4.21071 21.0391 4.58579 21.4142C4.96086 21.7893 5.46957 22 6 22H18C18.5304 22 19.0391 21.7893 19.4142 21.4142C19.7893 21.0391 20 20.5304 20 20V8L14 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M14 2V8H20" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M16 13H8M16 17H8M10 9H8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div class="nav-card-content">
                <div class="nav-card-title">Shift Requests</div>
                <div class="nav-card-subtitle">Track requests</div>
            </div>
        </button>
        
        <button type="button" class="nav-card" data-section="employee-roster">
            <div class="nav-card-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M17 21V19C17 17.9391 16.5786 16.9217 15.8284 16.1716C15.0783 15.4214 14.0609 15 13 15H5C3.93913 15 2.92172 15.4214 2.17157 16.1716C1.42143 16.9217 1 17.9391 1 19V21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M9 11C11.2091 11 13 9.20914 13 7C13 4.79086 11.2091 3 9 3C6.79086 3 5 4.79086 5 7C5 9.20914 6.79086 11 9 11Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M23 21V19C22.9993 18.1137 22.7044 17.2528 22.1614 16.5523C21.6184 15.8519 20.8581 15.3516 20 15.13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M16 3.13C16.8604 3.35031 17.623 3.85071 18.1676 4.55232C18.7122 5.25392 19.0078 6.11683 19.0078 7.005C19.0078 7.89318 18.7122 8.75608 18.1676 9.45769C17.623 10.1593 16.8604 10.6597 16 10.88" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div class="nav-card-content">
                <div class="nav-card-title">Employee Roster</div>
                <div class="nav-card-subtitle">Team members</div>
            </div>
        </button>
        
        <button type="button" class="nav-card" data-section="break-monitoring">
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
        
        <button type="button" class="nav-card" data-section="performance">
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
                        <h2>Supervisor Tracking Dashboard</h2>
                    </div>
                    <div class="meta-row">
                        <button type="button" class="week-selector-pill" id="week-selector" data-week-start="<?= e($weekStart) ?>" data-week-end="<?= e($weekEnd) ?>">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M8 2V6M16 2V6M3 10H21M5 4H19C20.1046 4 21 4.89543 21 6V19C21 20.1046 20.1046 21 19 21H5C3.89543 21 3 20.1046 3 19V6C3 4.89543 3.89543 4 5 4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span>Week <?= e($weekStart) ?> → <?= e($weekEnd) ?></span>
                        </button>
                    </div>
                </div>
                
                <!-- Tracking Metrics Grid -->
                <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); margin-top: var(--space-xl);">
                    <div class="metric">
                        <div class="metric-icon" style="background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M17 21V19C17 17.9391 16.5786 16.9217 15.8284 16.1716C15.0783 15.4214 14.0609 15 13 15H5C3.93913 15 2.92172 15.4214 2.17157 16.1716C1.42143 16.9217 1 17.9391 1 19V21" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M9 11C11.2091 11 13 9.20914 13 7C13 4.79086 11.2091 3 9 3C6.79086 3 5 4.79086 5 7C5 9.20914 6.79086 11 9 11Z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <h3>Total Employees</h3>
                        <div class="metric-value"><?= e((string) $metrics['total_employees']) ?></div>
                        <span class="muted">Active team members</span>
                    </div>
                    
                    <div class="metric">
                        <div class="metric-icon" style="background: linear-gradient(135deg, #10b981, #059669);">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M8 2V6M16 2V6M3 10H21M5 4H19C20.1046 4 21 4.89543 21 6V19C21 20.1046 20.1046 21 19 21H5C3.89543 21 3 20.1046 3 19V6C3 4.89543 3.89543 4 5 4Z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <h3>Total Shifts</h3>
                        <div class="metric-value"><?= e((string) $metrics['total_shifts']) ?></div>
                        <span class="muted">This week's assignments</span>
                    </div>
                    
                    <div class="metric">
                        <div class="metric-icon" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M14 2H6C5.46957 2 4.96086 2.21071 4.58579 2.58579C4.21071 2.96086 4 3.46957 4 4V20C4 20.5304 4.21071 21.0391 4.58579 21.4142C4.96086 21.7893 5.46957 22 6 22H18C18.5304 22 19.0391 21.7893 19.4142 21.4142C19.7893 21.0391 20 20.5304 20 20V8L14 2Z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M14 2V8H20" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <h3>Pending Requests</h3>
                        <div class="metric-value"><?= e((string) $metrics['pending_requests']) ?></div>
                        <span class="muted">Awaiting approval</span>
                    </div>
                    
                    <div class="metric">
                        <div class="metric-icon" style="background: linear-gradient(135deg, #3b82f6, #2563eb);">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="12" cy="12" r="10" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M12 6V12L16 14" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <h3>Active Breaks</h3>
                        <div class="metric-value"><?= e((string) $metrics['active_breaks']) ?></div>
                        <span class="muted">Currently on break</span>
                    </div>
                    
                    <div class="metric">
                        <div class="metric-icon" style="background: linear-gradient(135deg, #ef4444, #dc2626);">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 8V12M12 16H12.01M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <h3>Avg Break Delay</h3>
                        <div class="metric-value"><?= e((string) $metrics['avg_delay']) ?> min</div>
                        <span class="muted">Average delay time</span>
                    </div>
                    
                    <div class="metric">
                        <div class="metric-icon" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M9 11L12 14L22 4M21 12V19C21 19.5304 20.7893 20.0391 20.4142 20.4142C20.0391 20.7893 19.5304 21 19 21H5C4.46957 21 3.96086 20.7893 3.58579 20.4142C3.21071 20.0391 3 19.5304 3 19V5C3 4.46957 3.21071 3.96086 3.58579 3.58579C3.96086 3.21071 4.46957 3 5 3H16" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <h3>Coverage Gaps</h3>
                        <div class="metric-value"><?= e((string) $metrics['coverage_gaps']) ?></div>
                        <span class="muted">Understaffed positions</span>
                    </div>
                </div>
            </div>
        </section>

        <section class="dashboard-section" data-section="create-employee">
            <div class="card">
                <div class="section-title">
                    <h3>Create Employee or Senior</h3>
                    <span>Add staff credentials for this section</span>
                </div>
                <form method="post" action="/index.php" class="grid">
                    <input type="hidden" name="action" value="create_employee">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
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

        <!-- Weekly Schedule Section (Read-Only) -->
        <section class="dashboard-section" data-section="weekly-schedule">
            <div class="card">
                <div class="section-title">
                    <h3>Weekly Schedule</h3>
                    <span><?= e(count($schedule)) ?> assignments (Read-Only)</span>
                </div>
                <?php if (empty($schedule)): ?>
                    <div class="empty-state">
                        <div class="empty-state-title">No Schedule Data</div>
                        <div class="empty-state-text">No schedule assignments found for this week.</div>
                    </div>
                <?php else: ?>
                    <table>
                        <thead>
                        <tr>
                            <th>Date</th>
                            <th>Shift</th>
                            <th>Employee</th>
                            <th>Source</th>
                            <th>Notes</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($schedule as $entry): ?>
                            <tr>
                                <td><?= e($entry['shift_date']) ?></td>
                                <td><?= e($entry['shift_name']) ?></td>
                                <td><?= e($entry['employee_name']) ?></td>
                                <td><?= e($entry['assignment_source']) ?></td>
                                <td><?= e($entry['notes']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </section>

        <!-- Shift Requests Section (Read-Only) -->
        <section class="dashboard-section" data-section="shift-requests">
            <div class="card">
                <div class="section-title">
                    <h3>Shift Requests</h3>
                    <span><?= e(count($requests)) ?> total requests (Read-Only)</span>
                </div>
                <?php if (empty($requests)): ?>
                    <div class="empty-state">
                        <div class="empty-state-title">No Requests</div>
                        <div class="empty-state-text">No shift requests found for this week.</div>
                    </div>
                <?php else: ?>
                    <table>
                        <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Date</th>
                            <th>Shift</th>
                            <th>Status</th>
                            <th>Importance</th>
                            <th>Reason</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($requests as $request): ?>
                            <tr>
                                <td><?= e($request['employee_name']) ?></td>
                                <td><?= e($request['request_date']) ?></td>
                                <td><?= e($request['shift_name'] ?? 'Day Off') ?></td>
                                <td><span class="pill <?= strtolower($request['status']) ?>"><?= e($request['status']) ?></span></td>
                                <td>
                                    <?php if (!empty($request['importance_level']) && $request['importance_level'] !== 'MEDIUM'): ?>
                                        <span class="pill importance-<?= strtolower($request['importance_level']) ?>"><?= e($request['importance_level']) ?></span>
                                    <?php else: ?>
                                        <span class="muted">Medium</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= e($request['reason'] ?? '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </section>

        <!-- Employee Roster Section -->
        <section class="dashboard-section" data-section="employee-roster">
            <div class="card">
                <div class="section-title">
                    <h3>Employee Roster</h3>
                    <span><?= e(count($employees)) ?> employees</span>
                </div>
                <?php if (empty($employees)): ?>
                    <div class="empty-state">
                        <div class="empty-state-title">No Employees</div>
                        <div class="empty-state-text">No employees found in this section.</div>
                    </div>
                <?php else: ?>
                    <table>
                        <thead>
                        <tr>
                            <th>Name</th>
                            <th>Code</th>
                            <th>Role</th>
                            <th>Seniority</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($employees as $employee): ?>
                            <tr>
                                <td><?= e($employee['full_name']) ?></td>
                                <td><?= e($employee['employee_code']) ?></td>
                                <td><?= e($employee['role_name']) ?></td>
                                <td><?= e((string) $employee['seniority_level']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </section>

        <!-- Break Monitoring Section -->
        <section class="dashboard-section" data-section="break-monitoring">
            <div class="card">
                <div class="section-title">
                    <h3>Break Monitoring</h3>
                    <span>Today</span>
                </div>
                <?php if (empty($breaks)): ?>
                    <div class="empty-state">
                        <div class="empty-state-title">No Break Data</div>
                        <div class="empty-state-text">No break records found for today.</div>
                    </div>
                <?php else: ?>
                    <table>
                        <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Shift</th>
                            <th>Break Start</th>
                            <th>Break End</th>
                            <th>Delay</th>
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
                                <td><?= e((string) $break['delay_minutes']) ?> min</td>
                                <td><span class="pill <?= strtolower(str_replace('_', '-', $break['status'])) ?>"><?= e(str_replace('_', ' ', $break['status'])) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </section>

        <!-- Performance Analytics Section -->
        <section class="dashboard-section" data-section="performance">
            <div class="card">
                <div class="section-title">
                    <h3>Performance Analytics</h3>
                    <span>Sorted by lowest delay</span>
                </div>
                <?php if (empty($performance)): ?>
                    <div class="empty-state">
                        <div class="empty-state-title">No Performance Data</div>
                        <div class="empty-state-text">No performance records found for this period.</div>
                    </div>
                <?php else: ?>
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
                                <td><?= e((string) $row['average_delay_minutes']) ?> min</td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </section>
    </main>
</div>
