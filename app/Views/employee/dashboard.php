<?php
declare(strict_types=1);
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
                <div class="nav-card-subtitle">Dashboard summary</div>
            </div>
        </button>
        
        <button class="nav-card" data-section="submit-request">
            <div class="nav-card-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 5V19M5 12H19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div class="nav-card-content">
                <div class="nav-card-title">Submit Request</div>
                <div class="nav-card-subtitle">New shift request</div>
            </div>
        </button>
        
        <button class="nav-card" data-section="my-requests">
            <div class="nav-card-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M9 5H7C5.89543 5 5 5.89543 5 7V19C5 20.1046 5.89543 21 7 21H17C18.1046 21 19 20.1046 19 19V7C19 5.89543 18.1046 5 17 5H15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M9 5C9 4.46957 9.21071 3.96086 9.58579 3.58579C9.96086 3.21071 10.4696 3 11 3H13C13.5304 3 14.0391 3.21071 14.4142 3.58579C14.7893 3.96086 15 4.46957 15 5C15 5.53043 14.7893 6.03914 14.4142 6.41421C14.0391 6.78929 13.5304 7 13 7H11C10.4696 7 9.96086 6.78929 9.58579 6.41421C9.21071 6.03914 9 5.53043 9 5Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M9 12H15M9 16H13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div class="nav-card-content">
                <div class="nav-card-title">My Requests</div>
                <div class="nav-card-subtitle">View submissions</div>
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
                <div class="nav-card-subtitle">This week's shifts</div>
            </div>
        </button>
        
        <button class="nav-card" data-section="break-management">
            <div class="nav-card-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M12 6V12L16 14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div class="nav-card-content">
                <div class="nav-card-title">Break Management</div>
                <div class="nav-card-subtitle">Manage breaks</div>
            </div>
        </button>
    </div>

    <!-- Main Content Area -->
    <main class="dashboard-content">
        <!-- 
            Navigation Contract:
            Each section must have:
            - class="dashboard-section"
            - data-section="<section-name>" matching nav item data-section
            - Only one section (overview) should have "active" class initially
        -->
        <!-- Overview Section -->
        <section class="dashboard-section active" id="section-overview" data-section="overview">
            <div class="card">
                <div class="hero-row">
                    <div>
                        <h2>My Schedule</h2>
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
            </div>
        </section>

        <!-- Submit Request Section -->
        <section class="dashboard-section" id="section-submit-request" data-section="submit-request">
            <div class="card">
                <div class="section-title">
                    <h3>Submit Shift Request</h3>
                    <span>Submit requests for next week only (Monday-Sunday)</span>
                </div>
                <?php
                // Calculate next week dates
                $today = new DateTimeImmutable();
                $currentWeekStart = $today->modify('monday this week');
                $nextWeekStart = $currentWeekStart->modify('+7 days');
                $nextWeekEnd = $nextWeekStart->modify('+6 days');
                
                // Check if we're still in current week AND not on Sunday (can submit)
                $todayDayOfWeek = (int) $today->format('N'); // 1 = Monday, 7 = Sunday
                $canSubmit = $today->format('Y-m-d') >= $currentWeekStart->format('Y-m-d') 
                          && $today->format('Y-m-d') <= $currentWeekStart->modify('+6 days')->format('Y-m-d')
                          && $todayDayOfWeek !== 7; // Cannot submit on Sunday
                
                // Days of week for next week (Monday to Saturday only)
                $daysOfWeek = [];
                for ($i = 0; $i < 6; $i++) { // Monday to Saturday
                    $dayDate = $nextWeekStart->modify("+{$i} days");
                    $dayName = $dayDate->format('l'); // Monday, Tuesday, etc.
                    $dayDateStr = $dayDate->format('Y-m-d');
                    $daysOfWeek[] = [
                        'name' => $dayName,
                        'date' => $dayDateStr,
                        'display' => $dayName . ' (' . $dayDate->format('M j') . ')'
                    ];
                }
                ?>
                <?php if ($canSubmit): ?>
                    <form method="post" action="/index.php" class="grid" id="request-form">
                        <input type="hidden" name="action" value="submit_request">
                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                        <label>
                            Day (Next Week: <?= e($nextWeekStart->format('M j')) ?> - <?= e($nextWeekEnd->format('M j')) ?>)
                            <select name="request_date" id="request_date" required>
                                <option value="">Select a day</option>
                                <?php foreach ($daysOfWeek as $day): ?>
                                    <option value="<?= e($day['date']) ?>">
                                        <?= e($day['display']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="muted">Requests can only be submitted for next week. Submissions are not allowed on Sundays.</small>
                        </label>
                    <label>
                        Shift Type
                        <select name="shift_definition_id" required>
                            <?php foreach ($shiftDefinitions as $shift): ?>
                                <option value="<?= e((string) $shift['definition_id']) ?>">
                                    <?= e($shift['definition_name']) ?> (<?= e($shift['shift_type_name']) ?>)
                                </option>
                            <?php endforeach; ?>
                            <option value="0">OFF</option>
                        </select>
                    </label>
                    <label>
                        Schedule Pattern
                        <select name="schedule_pattern_id" required>
                            <?php foreach ($patterns as $pattern): ?>
                                <option value="<?= e((string) $pattern['id']) ?>"><?= e($pattern['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>
                        Importance
                        <select name="importance_level">
                            <option value="LOW">Low</option>
                            <option value="MEDIUM" selected>Medium</option>
                            <option value="HIGH">High</option>
                            <option value="EMERGENCY">Emergency</option>
                        </select>
                    </label>
                        <label>
                            Reason
                            <textarea name="reason" placeholder="Optional note"></textarea>
                        </label>
                        <div class="form-actions">
                            <button type="submit" class="btn">Submit Request</button>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="notice">
                        <strong>Submission Window Closed</strong><br>
                        <?php if ($todayDayOfWeek === 7): ?>
                            Submissions are not allowed on Sunday. Please submit your requests Monday through Saturday during the current week.
                        <?php else: ?>
                            Shift requests can only be submitted during the current week (<?= e($currentWeekStart->format('M j')) ?> - <?= e($currentWeekStart->modify('+6 days')->format('M j')) ?>). 
                            Please contact your Team Leader if you need to submit a request outside this window.
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- My Requests Section -->
        <section class="dashboard-section" id="section-my-requests" data-section="my-requests">
            <div class="card">
                <div class="section-title">
                    <h3>My Shift Requests</h3>
                    <span><?= e(count($myRequests ?? [])) ?> requests</span>
                </div>
                <?php if (!empty($myRequests)): ?>
                    <table>
                        <thead>
                        <tr>
                            <th>Date</th>
                            <th>Shift</th>
                            <th>Pattern</th>
                            <th>Importance</th>
                            <th>Status</th>
                            <th>Reason</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($myRequests as $request): ?>
                            <tr>
                                <td><?= e($request['request_date']) ?></td>
                                <td><?= e($request['shift_name'] ?? 'OFF') ?></td>
                                <td><?= e($request['pattern_name']) ?></td>
                                <td><span class="pill <?= strtolower($request['importance_level']) ?>"><?= e($request['importance_level']) ?></span></td>
                                <td><span class="pill <?= strtolower($request['status']) ?>"><?= e($request['status']) ?></span></td>
                                <td><?= e($request['reason'] ?? '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-title">No shift requests yet</div>
                        <p class="empty-state-text">Submit your first shift request above to get started. Requests are reviewed by your Team Leader.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Weekly Schedule Section -->
        <section class="dashboard-section" id="section-weekly-schedule" data-section="weekly-schedule">
            <div class="card">
                <div class="section-title">
                    <h3>My Weekly Schedule</h3>
                    <span>Your assigned shifts for this week</span>
                </div>
                <?php 
                $mySchedule = array_filter($schedule ?? [], function($entry) use ($user) {
                    return isset($entry['employee_id']) && $entry['employee_id'] == ($user['employee_id'] ?? null);
                });
                ?>
                <?php if (!empty($mySchedule)): ?>
                    <table>
                        <thead>
                        <tr>
                            <th>Date</th>
                            <th>Shift</th>
                            <th>Status</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($mySchedule as $entry): ?>
                            <tr>
                                <td><?= e($entry['shift_date']) ?></td>
                                <td><?= e($entry['shift_name']) ?></td>
                                <td><?= e($entry['assignment_source']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-title">No schedule assigned</div>
                        <p class="empty-state-text">Your weekly schedule will appear here once your Team Leader generates and assigns shifts for this week.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Break Management Section -->
        <section class="dashboard-section" id="section-break-management" data-section="break-management">
            <div class="card">
                <div class="section-title">
                    <h3>Break Management</h3>
                    <span>One 30-minute break per day</span>
                </div>
                <?php if ($myBreak): ?>
                    <div class="break-status">
                        <?php if ($myBreak['is_active']): ?>
                            <div class="alert info">
                                <strong>On Break</strong><br>
                                Started: <?= e(date('H:i:s', strtotime($myBreak['break_start']))) ?><br>
                                <?php if ($myBreak['delay_minutes'] > 0): ?>
                                    <span class="warning">Delay: <?= e($myBreak['delay_minutes']) ?> minutes</span>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="alert success">
                                <strong>Break Completed</strong><br>
                                Duration: <?= e($myBreak['delay_minutes'] >= 0 ? 'On time' : abs($myBreak['delay_minutes']) . ' min delay') ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                <div class="quick-actions">
                    <form method="post" action="/index.php" class="inline">
                        <input type="hidden" name="action" value="start_break">
                        <input type="hidden" name="worked_date" value="<?= e($today) ?>">
                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                        <button type="submit" class="btn" <?= ($myBreak['is_active'] ?? false) ? 'disabled' : '' ?>>Start Break</button>
                    </form>
                    <form method="post" action="/index.php" class="inline">
                        <input type="hidden" name="action" value="end_break">
                        <input type="hidden" name="worked_date" value="<?= e($today) ?>">
                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                        <button type="submit" class="btn secondary" <?= !($myBreak['is_active'] ?? false) ? 'disabled' : '' ?>>End Break</button>
                    </form>
                </div>
                <p class="muted">Break delays are tracked automatically for performance analytics.</p>
            </div>
        </section>
    </main>
</div>
