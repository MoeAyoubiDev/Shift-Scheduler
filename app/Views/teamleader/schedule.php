<?php
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../../Core/CSRF.php';
?>

<h1>Weekly Schedule - Week of <?= date('M d, Y', strtotime($weekStart)) ?></h1>

<div class="schedule-actions">
    <form method="POST" style="display: inline;">
        <?= CSRF::tokenField() ?>
        <input type="hidden" name="action" value="generate">
        <button type="submit" class="btn btn-primary">Generate Schedule</button>
    </form>
    <form method="POST" style="display: inline;">
        <?= CSRF::tokenField() ?>
        <input type="hidden" name="action" value="export">
        <button type="submit" class="btn">Export CSV</button>
    </form>
</div>

<?php
// Group schedules by date and shift
$grouped = [];
foreach ($schedules as $schedule) {
    $key = $schedule['date'] . '_' . $schedule['shift_definition_id'];
    if (!isset($grouped[$key])) {
        $grouped[$key] = [
            'date' => $schedule['date'],
            'shift_name' => $schedule['shift_name'],
            'category' => $schedule['category'],
            'start_time' => $schedule['start_time'],
            'end_time' => $schedule['end_time'],
            'schedule_shift_id' => $schedule['schedule_shift_id'],
            'employees' => []
        ];
    }
    if ($schedule['employee_id']) {
        $grouped[$key]['employees'][] = $schedule;
    }
}
?>

<div class="schedule-grid">
    <?php foreach ($grouped as $shift): ?>
        <div class="schedule-card">
            <h3><?= date('D, M d', strtotime($shift['date'])) ?></h3>
            <p><strong><?= htmlspecialchars($shift['shift_name']) ?></strong></p>
            <p><?= $shift['start_time'] ?> - <?= $shift['end_time'] ?></p>
            
            <div class="assigned-employees">
                <h4>Assigned Employees:</h4>
                <?php if (empty($shift['employees'])): ?>
                    <p class="text-muted">No employees assigned</p>
                <?php else: ?>
                    <ul>
                        <?php foreach ($shift['employees'] as $emp): ?>
                            <li>
                                <?= htmlspecialchars($emp['full_name']) ?> (<?= htmlspecialchars($emp['employee_code']) ?>)
                                <form method="POST" style="display: inline;">
                                    <?= CSRF::tokenField() ?>
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="schedule_shift_id" value="<?= $shift['schedule_shift_id'] ?>">
                                    <input type="hidden" name="employee_id" value="<?= $emp['employee_id'] ?>">
                                    <input type="hidden" name="update_action" value="REMOVE">
                                    <button type="submit" class="btn btn-sm btn-danger">Remove</button>
                                </form>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
            
            <div class="add-employee">
                <form method="POST">
                    <?= CSRF::tokenField() ?>
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="schedule_shift_id" value="<?= $shift['schedule_shift_id'] ?>">
                    <input type="hidden" name="update_action" value="ADD">
                    <select name="employee_id" required>
                        <option value="">Select Employee</option>
                        <?php foreach ($employees as $emp): ?>
                            <option value="<?= $emp['id'] ?>">
                                <?= htmlspecialchars($emp['full_name']) ?> (<?= htmlspecialchars($emp['employee_code']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-sm">Add Employee</button>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<a href="/dashboard.php" class="btn">Back to Dashboard</a>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

