<?php
require_once __DIR__ . '/../layouts/header.php';
?>

<h1>Weekly Schedule Summary - Week of <?= date('M d, Y', strtotime($weekStart)) ?></h1>

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
                            <li><?= htmlspecialchars($emp['full_name']) ?> (<?= htmlspecialchars($emp['employee_code']) ?>)</li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<a href="/today-shift.php" class="btn">Back to Today's Shift</a>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

