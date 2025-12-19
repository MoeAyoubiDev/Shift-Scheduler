<?php
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../../Core/CSRF.php';
?>

<h1>Today's Shift Management - <?= date('F d, Y') ?></h1>

<?php if (empty($todayShift)): ?>
    <p>No employees scheduled for today.</p>
<?php else: ?>
    <table class="data-table">
        <thead>
            <tr>
                <th>Employee</th>
                <th>Shift</th>
                <th>Start Time</th>
                <th>End Time</th>
                <th>Break Status</th>
                <th>Break Duration</th>
                <th>Delay (min)</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($todayShift as $shift): ?>
                <tr>
                    <td><?= htmlspecialchars($shift['full_name']) ?> (<?= htmlspecialchars($shift['employee_code']) ?>)</td>
                    <td><?= htmlspecialchars($shift['shift_name']) ?></td>
                    <td><?= $shift['start_time'] ?></td>
                    <td><?= $shift['end_time'] ?></td>
                    <td>
                        <?php if ($shift['break_status'] == 'ON_BREAK'): ?>
                            <span class="badge badge-warning">ON BREAK</span>
                        <?php elseif ($shift['break_status'] == 'BREAK_COMPLETED'): ?>
                            <span class="badge badge-success">COMPLETED</span>
                        <?php else: ?>
                            <span class="badge">NO BREAK</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($shift['break_start']): ?>
                            <?= round($shift['break_duration_minutes'] ?? 0) ?> min
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($shift['delay_minutes'] > 0): ?>
                            <span class="text-danger"><?= round($shift['delay_minutes']) ?> min</span>
                        <?php else: ?>
                            <span class="text-success">0 min</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($shift['break_status'] == 'NO_BREAK' || $shift['break_status'] == 'BREAK_COMPLETED'): ?>
                            <form method="POST" style="display: inline;">
                                <?= CSRF::tokenField() ?>
                                <input type="hidden" name="action" value="start_break">
                                <input type="hidden" name="employee_id" value="<?= $shift['employee_id'] ?>">
                                <input type="hidden" name="schedule_shift_id" value="<?= $shift['schedule_shift_id'] ?>">
                                <button type="submit" class="btn btn-sm btn-primary">Start Break</button>
                            </form>
                        <?php endif; ?>
                        <?php if ($shift['break_status'] == 'ON_BREAK'): ?>
                            <form method="POST" style="display: inline;">
                                <?= CSRF::tokenField() ?>
                                <input type="hidden" name="action" value="end_break">
                                <input type="hidden" name="employee_id" value="<?= $shift['employee_id'] ?>">
                                <button type="submit" class="btn btn-sm btn-success">End Break</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<div class="nav-links">
    <a href="/weekly-schedule.php" class="btn">View Weekly Schedule</a>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

