<?php
declare(strict_types=1);
?>
<div class="dashboard-container">
    <!-- Tab Navigation Bar -->
    <nav class="dashboard-tabs" role="tablist">
        <button class="tab-item active" data-section="overview" role="tab" aria-selected="true">
            Overview
        </button>
        <button class="tab-item" data-section="submit-request" role="tab" aria-selected="false">
            Submit Request
        </button>
        <button class="tab-item" data-section="my-requests" role="tab" aria-selected="false">
            My Requests
        </button>
        <button class="tab-item" data-section="weekly-schedule" role="tab" aria-selected="false">
            Weekly Schedule
        </button>
        <button class="tab-item" data-section="break-management" role="tab" aria-selected="false">
            Break Management
        </button>
    </nav>

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
<script src="/assets/js/dashboard.js"></script>
