<?php
declare(strict_types=1);

$shiftDefinitionById = [];
foreach ($shiftDefinitions as $def) {
    $shiftDefinitionById[(int) $def['definition_id']] = $def;
}

$employeeSchedule = [];
$employeeHours = [];
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

    $shiftDef = $shiftDefinitionById[(int) ($entry['shift_definition_id'] ?? 0)] ?? null;

    $employeeSchedule[$empId][$date][] = [
        'shift_name' => $entry['shift_name'] ?? '',
        'start_time' => $shiftDef['start_time'] ?? '',
        'end_time' => $shiftDef['end_time'] ?? '',
        'category' => $entry['shift_category'] ?? '',
        'assignment_id' => $entry['assignment_id'] ?? null,
        'notes' => $entry['notes'] ?? '',
        'shift_definition_id' => $entry['shift_definition_id'] ?? null,
    ];

    if (!isset($employeeHours[$empId])) {
        $employeeHours[$empId] = 0;
    }
    $duration = $shiftDef['duration_hours'] ?? 8.0;
    $employeeHours[$empId] += (float) $duration;
}

$totalShifts = count($schedule);
$totalEmployees = count($uniqueEmployees);
$totalHours = array_sum($employeeHours);
$avgHoursPerEmployee = $totalEmployees > 0 ? $totalHours / $totalEmployees : 0;

$employeeShiftsByDate = [];
foreach ($schedule as $entry) {
    if (empty($entry['employee_id']) || empty($entry['shift_date'])) {
        continue;
    }
    $empId = (int) $entry['employee_id'];
    $date = $entry['shift_date'];
    if (!isset($employeeShiftsByDate[$empId])) {
        $employeeShiftsByDate[$empId] = [];
    }
    if (!isset($employeeShiftsByDate[$empId][$date])) {
        $employeeShiftsByDate[$empId][$date] = [];
    }

    $shiftDef = $shiftDefinitionById[(int) ($entry['shift_definition_id'] ?? 0)] ?? null;
    $employeeShiftsByDate[$empId][$date][] = [
        'employee_name' => $entry['employee_name'] ?? 'Unknown',
        'shift_name' => $entry['shift_name'] ?? '',
        'start_time' => $shiftDef['start_time'] ?? '',
        'end_time' => $shiftDef['end_time'] ?? '',
    ];
}

$conflicts = [];
foreach ($employeeShiftsByDate as $empId => $dates) {
    foreach ($dates as $date => $shifts) {
        if (count($shifts) > 1) {
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

                        if ($start1Time < $end2Time && $end1Time > $start2Time) {
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
<section class="dashboard-surface teamleader-dashboard-page">
    <div class="dashboard-inner">
        <div class="card requests-panel">
            <div class="section-title">
                <h3>Shift Requests for Next Week</h3>
                <span><?= e(count($requests)) ?> pending requests</span>
            </div>
            <?php if (!empty($requests)): ?>
                <div class="requests-list">
                    <?php
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
                            <path d="M4 2H12C12.5304 2 13.0391 2.21071 13.4142 2.58579C13.7893 2.96086 14 3.46957 14 4V12C14 12.5304 13.7893 13.0391 13.4142 13.4142C13.0391 13.7893 12.5304 14 12 14H4C3.46957 14 2.96086 13.7893 2.58579 13.4142C2.21071 13.0391 2 12.5304 2 12V4C2 3.46957 2.21071 2.58579 2.58579 2.58579C2.96086 2.21071 3.46957 2 4 2Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
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
                    <button type="button" class="btn ghost" id="swap-shifts-btn">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M7 7H20M20 7L16 3M20 7L16 11" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M17 17H4M4 17L8 21M4 17L8 13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Swap Shifts
                    </button>
                    <a href="/index.php?download=schedule&week_start=<?= e($weekStart) ?>&week_end=<?= e($weekEnd) ?>" class="btn secondary">
                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M15 11.25V15C15 15.3978 14.842 15.7794 14.5607 16.0607C14.2794 16.342 13.8978 16.5 13.5 16.5H4.5C4.10218 16.5 3.72064 16.342 3.43934 16.0607C3.15804 15.7794 3 15.3978 3 15V11.25M12 7.5L9 4.5M9 4.5L6 7.5M9 4.5V11.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Export CSV
                    </a>
                </div>
            </div>

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
                                <td colspan="8" class="empty-schedule" data-label="Schedule">
                                    <div class="empty-state">
                                        <div class="empty-state-title">No schedule data</div>
                                        <p class="empty-state-text">Generate a schedule or assign shifts to employees to see the weekly view.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($uniqueEmployees as $empId => $employee): ?>
                                <tr class="employee-row" data-employee-id="<?= e((string) $empId) ?>" data-employee-name="<?= e(strtolower($employee['name'])) ?>">
                                    <td class="employee-cell" data-label="Employee">
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
                                        $hasConflict = false;
                                        foreach ($conflicts as $conflict) {
                                            if ($conflict['employee_id'] === $empId && $conflict['date'] === $dayInfo['date']) {
                                                $hasConflict = true;
                                                break;
                                            }
                                        }
                                        ?>
                                        <td class="shift-cell <?= $hasConflict ? 'shift-conflict' : '' ?>" data-date="<?= e($dayInfo['date']) ?>" data-employee-id="<?= e((string) $empId) ?>" data-label="<?= e($dayInfo['day_name'] . ' ' . $dayInfo['day_number']) ?>">
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
                                                        $shiftClass = 'shift-pill';
                                                        $shiftLabel = $shift['shift_name'];

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

            <div id="assign-modal" class="modal" style="display: none;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>Assign Shift</h3>
                        <button type="button" class="modal-close">&times;</button>
                    </div>
                    <form id="assign-shift-form" method="post" action="/index.php" data-ajax="true" data-close-modal="assign-modal">
                        <input type="hidden" name="action" id="assign-action" value="assign_shift">
                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                        <input type="hidden" name="assignment_id" id="assign-assignment-id">
                        <div class="form-group">
                            <label for="assign-date">Date</label>
                            <select name="date" id="assign-date" class="form-input" required>
                                <option value="">Select date</option>
                                <?php foreach ($weekDates as $dayInfo): ?>
                                    <option value="<?= e($dayInfo['date']) ?>">
                                        <?= e($dayInfo['day_name']) ?> (<?= e($dayInfo['date']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
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
                            <button type="submit" class="btn btn-primary" id="assign-submit-btn">Assign Shift</button>
                        </div>
                    </form>
                    <form id="delete-assignment-form" method="post" action="/index.php" data-ajax="true" data-close-modal="assign-modal" style="display: none;">
                        <input type="hidden" name="action" value="delete_assignment">
                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                        <input type="hidden" name="assignment_id" id="delete-assignment-id">
                        <div class="form-actions modal-delete-actions">
                            <button type="submit" class="btn danger">Delete Shift</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div id="swap-modal" class="modal" style="display: none;">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Swap Shifts</h3>
                    <button type="button" class="modal-close">&times;</button>
                </div>
                <form id="swap-shift-form" method="post" action="/index.php" data-ajax="true" data-close-modal="swap-modal">
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
    </div>
</section>
