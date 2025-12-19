<?php
declare(strict_types=1);

$days = config('schedule', 'days', ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']);
$shiftTypes = config('schedule', 'shift_types', ['AM', 'PM', 'MID']);
$scheduleOptions = config('schedule', 'schedule_options', []);
$importanceLevels = config('schedule', 'importance_levels', []);

if ($scheduleOptions === []) {
    $scheduleOptions = [
        '5x2' => 'Work 5 days / 2 off (9 hours)',
        '6x1' => 'Work 6 days / 1 off (7.5 hours)',
    ];
}

if ($importanceLevels === []) {
    $importanceLevels = [
        'low' => 'Low',
        'medium' => 'Medium',
        'high' => 'High',
    ];
}
?>
<section class="card">
    <div class="section-title">
        <h2>Submit Weekly Request</h2>
        <span>Share availability before weekly lock</span>
    </div>
    <?php if (!$submissionLocked && submission_window_open()): ?>
        <form method="post">
            <input type="hidden" name="action" value="submit_request">
            <input type="hidden" name="week_start" value="<?= e($weekStart ?? '') ?>">
            <div class="grid">
                <div>
                    <label>Day</label>
                    <select name="requested_day" required>
                        <?php foreach ($days as $day): ?>
                            <option value="<?= e($day) ?>"><?= e($day) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label>Shift Type</label>
                    <select name="shift_type" required>
                        <?php foreach ($shiftTypes as $shiftType): ?>
                            <option value="<?= e($shiftType) ?>"><?= e($shiftType) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label>Schedule Option</label>
                    <select name="schedule_option" required>
                        <?php foreach ($scheduleOptions as $optionValue => $optionLabel): ?>
                            <option value="<?= e($optionValue) ?>"><?= e($optionLabel) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label>Day Off</label>
                    <label class="checkbox-row">
                        <input type="checkbox" name="day_off" value="1"> Request this day off
                    </label>
                </div>
                <div>
                    <label>Importance</label>
                    <select name="importance" required>
                        <?php foreach ($importanceLevels as $importanceValue => $importanceLabel): ?>
                            <option value="<?= e($importanceValue) ?>"><?= e($importanceLabel) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <label class="spacer-top">Reason</label>
            <textarea name="reason" required placeholder="Provide a reason for your request"></textarea>
            <div class="form-actions">
                <button class="btn" type="submit">Submit request</button>
            </div>
        </form>
    <?php elseif ($submissionLocked): ?>
        <div class="notice">Please contact your Team Leader for more information.</div>
    <?php else: ?>
        <div class="notice">Sorry, you cannot submit a late request. Please contact your Team Leader for more information.</div>
    <?php endif; ?>
</section>

<section class="card">
    <div class="section-title">
        <h2>My Request History</h2>
        <span>Track approvals and updates</span>
    </div>
    <form method="get">
        <div class="grid">
            <div>
                <label>From</label>
                <input type="date" name="from" value="<?= e($filters['from'] ?? '') ?>">
            </div>
            <div>
                <label>To</label>
                <input type="date" name="to" value="<?= e($filters['to'] ?? '') ?>">
            </div>
            <div>
                <label>Specific date</label>
                <input type="date" name="on" value="<?= e($filters['on'] ?? '') ?>">
            </div>
        </div>
        <div class="form-actions">
            <button class="btn secondary" type="submit">Filter</button>
        </div>
    </form>
    <table>
        <tr>
            <th>Submitted</th>
            <th>Week</th>
            <th>Day</th>
            <th>Shift</th>
            <th>Option</th>
            <th>Reason</th>
            <th>Importance</th>
            <th>Status</th>
        </tr>
        <?php foreach ($history as $row): ?>
            <tr>
                <td><?= e($row['submission_date']) ?></td>
                <td><?= e($row['week_start']) ?></td>
                <td><?= e($row['requested_day']) ?> <?= $row['day_off'] ? '(Day off)' : '' ?></td>
                <td><?= e($row['shift_type']) ?></td>
                <td><?= e(schedule_option_label($row['schedule_option'])) ?></td>
                <td><?= e($row['reason']) ?></td>
                <td <?= importance_badge($row['importance']) ?>><?= e(ucfirst($row['importance'])) ?></td>
                <td><span class="status <?= e($row['status']) ?>"><?= e(ucfirst($row['status'])) ?></span></td>
            </tr>
        <?php endforeach; ?>
    </table>
</section>

<?php render_view('shifts/employee-schedule', [
    'user' => $user,
    'schedule' => $schedule,
]); ?>
