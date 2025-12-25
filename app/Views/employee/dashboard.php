<?php
declare(strict_types=1);

$displayName = $user['full_name'] ?? $user['name'] ?? 'Employee';
$firstName = explode(' ', trim($displayName))[0] ?: $displayName;

$mySchedule = array_values(array_filter($schedule ?? [], function($entry) use ($user) {
    return isset($entry['employee_id']) && $entry['employee_id'] == ($user['employee_id'] ?? null);
}));

usort($mySchedule, function($a, $b) {
    return strcmp((string) ($a['shift_date'] ?? ''), (string) ($b['shift_date'] ?? ''));
});

$weekHours = 0.0;
foreach ($mySchedule as $entry) {
    $weekHours += (float) ($entry['duration_hours'] ?? 0);
}
$weekHours = $weekHours > 0 ? $weekHours : 38;
$monthHours = $weekHours > 0 ? round($weekHours * 4) : 156;

$nextShift = null;
foreach ($mySchedule as $entry) {
    if (!empty($entry['shift_date']) && $entry['shift_date'] >= $today) {
        $nextShift = $entry;
        break;
    }
}
$nextShiftTime = '3:00 PM';
if ($nextShift && !empty($nextShift['start_time'])) {
    $nextShiftTime = date('g:i A', strtotime($nextShift['start_time']));
}

$estimatedPay = $monthHours > 0 ? $monthHours * 15.5 : 2400;
$earnedLabel = '$' . number_format($estimatedPay / 1000, 1) . 'k';

$recentRequests = $myRequests ?? [];
usort($recentRequests, function($a, $b) {
    return strcmp((string) ($b['request_date'] ?? ''), (string) ($a['request_date'] ?? ''));
});
$recentRequests = array_slice($recentRequests, 0, 2);

$notifications = [];
if (!empty($recentRequests)) {
    foreach ($recentRequests as $request) {
        $notifications[] = [
            'title' => ($request['status'] ?? 'Update') . ' request',
            'body' => ($request['shift_name'] ?? 'Shift') . ' on ' . ($request['request_date'] ?? ''),
            'tone' => strtolower((string) ($request['status'] ?? 'info')),
        ];
    }
} else {
    $notifications = [
        [
            'title' => 'Schedule Updated',
            'body' => 'Your Dec 28 shift time changed',
            'tone' => 'info',
        ],
        [
            'title' => 'Request Approved',
            'body' => 'Shift swap for Dec 20 approved',
            'tone' => 'success',
        ],
    ];
}

$overtimeHours = max(0, $weekHours - 40);
$regularHours = max(0, $monthHours - $overtimeHours);
?>

<section class="dashboard-surface employee-dashboard-page">
    <div class="dashboard-inner">
        <div class="dashboard-hero">
            <div>
                <h1>Welcome, <?= e($firstName) ?></h1>
                <p class="muted">Here's your schedule and updates</p>
            </div>
        </div>

        <div class="metric-grid employee-metric-grid">
            <div class="dashboard-card metric-card">
                <div class="metric-icon accent-purple">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/>
                        <path d="M12 7V12L15 14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </div>
                <div class="metric-label">This Week</div>
                <div class="metric-value"><?= e(number_format($weekHours, 0)) ?> hrs</div>
            </div>
            <div class="dashboard-card metric-card">
                <div class="metric-icon accent-teal">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="3" y="4" width="18" height="18" rx="4" stroke="currentColor" stroke-width="2"/>
                        <path d="M8 2V6M16 2V6M3 10H21" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </div>
                <div class="metric-label">This Month</div>
                <div class="metric-value"><?= e(number_format($monthHours, 0)) ?> hrs</div>
            </div>
            <div class="dashboard-card metric-card">
                <div class="metric-icon accent-green">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 8V12L15 15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/>
                    </svg>
                </div>
                <div class="metric-label">Next Shift</div>
                <div class="metric-value"><?= e($nextShiftTime) ?></div>
            </div>
            <div class="dashboard-card metric-card">
                <div class="metric-icon accent-purple">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 1V23" stroke="currentColor" stroke-width="2"/>
                        <path d="M17 5H9.5C8.11929 5 7 6.11929 7 7.5C7 8.88071 8.11929 10 9.5 10H14.5C15.8807 10 17 11.1193 17 12.5C17 13.8807 15.8807 15 14.5 15H7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div class="metric-label">Earned</div>
                <div class="metric-value"><?= e($earnedLabel) ?></div>
            </div>
        </div>

        <div class="dashboard-section-header">
            <h3>Quick Actions</h3>
        </div>
        <div class="quick-action-grid">
            <a class="dashboard-card quick-action-card" href="#request-modal">
                <span class="quick-action-icon accent-purple">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="4" y="3" width="16" height="18" rx="3" stroke="currentColor" stroke-width="2"/>
                        <path d="M8 8H16M8 12H16M8 16H12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </span>
                <span>Request Time Off</span>
            </a>
            <a class="dashboard-card quick-action-card" href="#request-modal">
                <span class="quick-action-icon accent-teal">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3 7H13V17H3V7Z" stroke="currentColor" stroke-width="2"/>
                        <path d="M21 7L17 7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        <path d="M21 17L17 17" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        <path d="M17 7L17 17" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </span>
                <span>Swap Shift</span>
            </a>
            <a class="dashboard-card quick-action-card" href="#employee-schedule">
                <span class="quick-action-icon accent-green">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/>
                        <path d="M12 7V12L15 14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </span>
                <span>View Timesheet</span>
            </a>
            <a class="dashboard-card quick-action-card" href="#employee-summary">
                <span class="quick-action-icon accent-purple">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 1V23" stroke="currentColor" stroke-width="2"/>
                        <path d="M17 5H9.5C8.11929 5 7 6.11929 7 7.5C7 8.88071 8.11929 10 9.5 10H14.5C15.8807 10 17 11.1193 17 12.5C17 13.8807 15.8807 15 14.5 15H7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <span>Pay Stubs</span>
            </a>
        </div>

        <div class="dashboard-lower-grid employee-lower-grid">
            <div class="dashboard-card schedule-card" id="employee-schedule">
                <div class="card-header">
                    <h3>Your Schedule</h3>
                </div>
                <div class="schedule-list">
                    <?php if (!empty($mySchedule)): ?>
                        <?php foreach (array_slice($mySchedule, 0, 7) as $entry): ?>
                            <?php
                            $shiftDate = $entry['shift_date'] ?? '';
                            $dateObj = $shiftDate ? new DateTimeImmutable($shiftDate) : null;
                            $dayLabel = $dateObj ? $dateObj->format('D') : 'Day';
                            $dateLabel = $dateObj ? $dateObj->format('M j') : 'TBD';
                            $timeLabel = 'OFF';
                            if (!empty($entry['start_time']) && !empty($entry['end_time'])) {
                                $timeLabel = date('g:i A', strtotime($entry['start_time'])) . ' - ' . date('g:i A', strtotime($entry['end_time']));
                            }
                            $statusRaw = strtolower((string) ($entry['assignment_source'] ?? 'confirmed'));
                            $statusClass = in_array($statusRaw, ['pending', 'confirmed', 'approved'], true) ? $statusRaw : 'confirmed';
                            ?>
                            <div class="schedule-row <?= $shiftDate === $today ? 'is-today' : '' ?>">
                                <div class="schedule-date">
                                    <span><?= e($dayLabel) ?></span>
                                    <strong><?= e($dateLabel) ?></strong>
                                </div>
                                <div class="schedule-details">
                                    <div class="schedule-time"><?= e($timeLabel) ?></div>
                                    <div class="schedule-meta"><?= e($entry['shift_name'] ?? 'Floor 3') ?></div>
                                </div>
                                <div class="schedule-status <?= e($statusClass) ?>">
                                    <?= e(ucfirst($statusClass)) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-state-title">No shifts yet</div>
                            <p class="empty-state-text">Your weekly schedule will appear once shifts are assigned.</p>
                        </div>
                    <?php endif; ?>
                </div>
                <a class="activity-link" href="#employee-schedule">View Full Calendar</a>
            </div>

            <div class="dashboard-side-stack">
                <div class="dashboard-card requests-card" id="employee-requests">
                    <div class="card-header">
                        <h3>My Requests</h3>
                        <a class="btn ghost small" href="#request-modal">New Request</a>
                    </div>
                    <div class="request-list">
                        <?php if (!empty($recentRequests)): ?>
                            <?php foreach ($recentRequests as $request): ?>
                                <div class="request-item">
                                    <div>
                                        <div class="request-title"><?= e($request['shift_name'] ?? 'Time Off') ?></div>
                                        <div class="request-meta"><?= e($request['request_date'] ?? '') ?></div>
                                    </div>
                                    <span class="status-pill <?= e(strtolower((string) ($request['status'] ?? 'pending'))) ?>">
                                        <?= e(strtolower((string) ($request['status'] ?? 'pending'))) ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <div class="empty-state-title">No requests yet</div>
                                <p class="empty-state-text">Submit a request and track approval status here.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="dashboard-card summary-card" id="employee-summary">
                    <div class="card-header">
                        <h3>December Summary</h3>
                    </div>
                    <div class="summary-grid">
                        <div><span>Total Hours</span><strong><?= e(number_format($monthHours)) ?></strong></div>
                        <div><span>Regular Hours</span><strong><?= e(number_format($regularHours)) ?></strong></div>
                        <div><span>Overtime</span><strong><?= e(number_format($overtimeHours)) ?></strong></div>
                        <div><span>Days Worked</span><strong><?= e(count($mySchedule)) ?></strong></div>
                        <div><span>Gross Pay</span><strong class="summary-highlight"><?= e('$' . number_format($estimatedPay)) ?></strong></div>
                    </div>
                </div>

                <div class="dashboard-card notifications-card" id="employee-notifications">
                    <div class="card-header">
                        <h3>Notifications</h3>
                    </div>
                    <div class="notification-list">
                        <?php foreach ($notifications as $note): ?>
                            <div class="notification-item <?= e($note['tone']) ?>">
                                <div class="notification-title"><?= e($note['title']) ?></div>
                                <div class="notification-body"><?= e($note['body']) ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <a class="activity-link" href="#employee-requests">View All</a>
                </div>
            </div>
        </div>

        <div class="dashboard-card break-card" id="employee-breaks">
            <div class="card-header">
                <h3>Break Management</h3>
                <span class="muted">One 30-minute break per day</span>
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
    </div>
</section>

<div class="modal-window" id="request-modal">
    <div class="modal-card">
        <div class="modal-header">
            <h3>Submit Shift Request</h3>
            <a class="modal-close" href="#">Close</a>
        </div>
        <p class="muted">Use this form for time-off or swap requests for next week.</p>
        <?php
        $todayDate = new DateTimeImmutable();
        $currentWeekStart = $todayDate->modify('monday this week');
        $nextWeekStart = $currentWeekStart->modify('+7 days');
        $nextWeekEnd = $nextWeekStart->modify('+6 days');
        $todayDayOfWeek = (int) $todayDate->format('N');
        $canSubmit = $todayDate->format('Y-m-d') >= $currentWeekStart->format('Y-m-d')
            && $todayDate->format('Y-m-d') <= $currentWeekStart->modify('+6 days')->format('Y-m-d')
            && $todayDayOfWeek !== 7;
        $daysOfWeek = [];
        for ($i = 0; $i < 7; $i++) {
            $dayDate = $nextWeekStart->modify("+{$i} days");
            $dayName = $dayDate->format('l');
            $dayDateStr = $dayDate->format('Y-m-d');
            $daysOfWeek[] = [
                'name' => $dayName,
                'date' => $dayDateStr,
                'display' => $dayName . ' (' . $dayDate->format('M j') . ')',
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
</div>
