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
                                <?php if (in_array($role['role_name'], ['Employee', 'Senior', 'Supervisor'], true)): ?>
                                    <option value="<?= e((string) $role['id']) ?>"><?= e($role['role_name']) ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>
                        Seniority Level
                        <input type="number" name="seniority_level" min="0" value="0">
                    </label>
                    <label class="checkbox-row">
                        <input type="checkbox" name="is_senior" value="1">
                        Mark as Senior
                    </label>
                    <div class="form-actions">
                        <button type="submit" class="btn">Create Employee</button>
                    </div>
                </form>
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
            <div class="card">
                <div class="hero-row">
                    <div>
                        <h3>Weekly Schedule</h3>
                        <p>Generate and manually adjust the weekly schedule.</p>
                    </div>
                    <form method="post" action="/index.php" class="inline">
                        <input type="hidden" name="action" value="generate_schedule">
                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                        <button type="submit" class="btn">Generate Schedule</button>
                    </form>
                </div>
                <table>
                    <thead>
                    <tr>
                        <th>Date</th>
                        <th>Shift</th>
                        <th>Employee</th>
                        <th>Source</th>
                        <th>Notes</th>
                        <th>Update Shift</th>
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
                            <td>
                                <form method="post" action="/index.php" class="inline">
                                    <input type="hidden" name="action" value="update_assignment">
                                    <input type="hidden" name="assignment_id" value="<?= e((string) $entry['assignment_id']) ?>">
                                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                    <select name="shift_definition_id">
                                        <?php foreach ($shiftDefinitions as $definition): ?>
                                            <option value="<?= e((string) $definition['definition_id']) ?>" <?= $definition['definition_id'] == $entry['shift_definition_id'] ? 'selected' : '' ?>>
                                                <?= e($definition['definition_name']) ?> (<?= e($definition['shift_type_name']) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="btn small">Update</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
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
