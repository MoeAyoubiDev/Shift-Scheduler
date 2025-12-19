<?php
require_once __DIR__ . '/../layouts/header.php';
?>

<h1>Break Reports - <?= date('F d, Y') ?></h1>

<?php if (empty($todayShift)): ?>
    <p>No employees on shift today.</p>
<?php else: ?>
    <table class="data-table">
        <thead>
            <tr>
                <th>Employee</th>
                <th>Shift</th>
                <th>Break Status</th>
                <th>Break Start</th>
                <th>Break End</th>
                <th>Duration</th>
                <th>Delay</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($todayShift as $shift): ?>
                <tr>
                    <td><?= htmlspecialchars($shift['full_name']) ?> (<?= htmlspecialchars($shift['employee_code']) ?>)</td>
                    <td><?= htmlspecialchars($shift['shift_name']) ?></td>
                    <td>
                        <?php if ($shift['break_status'] == 'ON_BREAK'): ?>
                            <span class="badge badge-warning">ON BREAK</span>
                        <?php elseif ($shift['break_status'] == 'BREAK_COMPLETED'): ?>
                            <span class="badge badge-success">COMPLETED</span>
                        <?php else: ?>
                            <span class="badge">NO BREAK</span>
                        <?php endif; ?>
                    </td>
                    <td><?= $shift['break_start'] ? date('H:i', strtotime($shift['break_start'])) : '-' ?></td>
                    <td><?= $shift['break_end'] ? date('H:i', strtotime($shift['break_end'])) : '-' ?></td>
                    <td><?= $shift['break_duration_minutes'] ? round($shift['break_duration_minutes']) . ' min' : '-' ?></td>
                    <td>
                        <?php if ($shift['delay_minutes'] > 0): ?>
                            <span class="text-danger"><?= round($shift['delay_minutes']) ?> min</span>
                        <?php else: ?>
                            <span class="text-success">0 min</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<a href="/dashboard.php" class="btn">Back to Dashboard</a>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

