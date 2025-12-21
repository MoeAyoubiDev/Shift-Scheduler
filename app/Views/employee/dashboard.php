<?php
declare(strict_types=1);
?>
<div class="dashboard-layout">
    <!-- Sidebar Overlay for Mobile -->
    <div class="sidebar-overlay" id="sidebar-overlay"></div>
    
    <!-- Sidebar Navigation -->
    <aside class="dashboard-sidebar" id="dashboard-sidebar">
        <div class="sidebar-header">
            <h3>My Schedule</h3>
            <button class="sidebar-toggle" id="sidebar-toggle" aria-label="Toggle sidebar">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M5 5L15 15M15 5L5 15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </button>
        </div>
        <nav class="sidebar-nav">
            <a href="#overview" class="nav-item active" data-section="overview">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M3 4C3 3.44772 3.44772 3 4 3H16C16.5523 3 17 3.44772 17 4V16C17 16.5523 16.5523 17 16 17H4C3.44772 17 3 16.5523 3 16V4Z" stroke="currentColor" stroke-width="1.5"/>
                    <path d="M3 8H17M8 3V17" stroke="currentColor" stroke-width="1.5"/>
                </svg>
                <span>Overview</span>
            </a>
            <a href="#submit-request" class="nav-item" data-section="submit-request">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M10 3V17M3 10H17" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                <span>Submit Request</span>
            </a>
            <a href="#my-requests" class="nav-item" data-section="my-requests">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M4 4H16C16.5523 4 17 4.44772 17 5V15C17 15.5523 16.5523 16 16 16H4C3.44772 16 3 15.5523 3 15V5C3 4.44772 3.44772 4 4 4Z" stroke="currentColor" stroke-width="1.5"/>
                    <path d="M7 8H13M7 12H11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
                <span>My Requests</span>
            </a>
            <a href="#weekly-schedule" class="nav-item" data-section="weekly-schedule">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M4 4H16C16.5523 4 17 4.44772 17 5V15C17 15.5523 16.5523 16 16 16H4C3.44772 16 3 15.5523 3 15V5C3 4.44772 3.44772 4 4 4Z" stroke="currentColor" stroke-width="1.5"/>
                    <path d="M7 3V7M13 3V7M3 9H17" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
                <span>Weekly Schedule</span>
            </a>
            <a href="#break-management" class="nav-item" data-section="break-management">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M10 18C14.4183 18 18 14.4183 18 10C18 5.58172 14.4183 2 10 2C5.58172 2 2 5.58172 2 10C2 14.4183 5.58172 18 10 18Z" stroke="currentColor" stroke-width="1.5"/>
                    <path d="M10 6V10L13 13" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
                <span>Break Management</span>
            </a>
        </nav>
    </aside>

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
                        <p>Submit shift requests and manage your break.</p>
                    </div>
                    <div class="meta-row">
                        <span class="pill">Week <?= e($weekStart) ?> → <?= e($weekEnd) ?></span>
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
                
                // Days of week for next week (Monday to Sunday - all 7 days)
                $daysOfWeek = [];
                for ($i = 0; $i < 7; $i++) { // Monday to Sunday
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
                        <input type="hidden" name="request_date" id="request_date" required>
                        <label>
                            Day (Next Week: <?= e($nextWeekStart->format('M j')) ?> - <?= e($nextWeekEnd->format('M j')) ?>)
                            <select name="request_day" id="request_day" required>
                                <option value="">Select a day</option>
                                <?php foreach ($daysOfWeek as $day): ?>
                                    <option value="<?= e($day['date']) ?>" data-date="<?= e($day['date']) ?>">
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
                            <option value="NORMAL" selected>Normal</option>
                            <option value="HIGH">High</option>
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
<script src="/assets/js/dashboard.js" defer></script>
