<?php
declare(strict_types=1);
?>
<section class="card">
    <div class="hero-row">
        <div>
            <h2>Today's Shift Control</h2>
            <p>Monitor break coverage and active shift teams.</p>
        </div>
        <div class="meta-row">
            <span class="pill">Today <?= e($today) ?></span>
        </div>
    </div>
</section>

<section class="card">
    <div class="section-title">
        <h3>Active Shift Coverage</h3>
        <span><?= e(count($todaySchedule)) ?> assignments</span>
    </div>
    <table>
        <thead>
        <tr>
            <th>Shift</th>
            <th>Employee</th>
            <th>Status</th>
            <th>Break Action</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($todaySchedule as $assignment): ?>
            <tr>
                <td><?= e($assignment['shift_name']) ?></td>
                <td><?= e($assignment['employee_name']) ?></td>
                <td><?= e($assignment['attendance_status']) ?></td>
                <td>
                    <form method="post" action="/index.php" class="inline">
                        <input type="hidden" name="action" value="start_break">
                        <input type="hidden" name="employee_id" value="<?= e((string) $assignment['employee_id']) ?>">
                        <input type="hidden" name="schedule_shift_id" value="<?= e((string) $assignment['schedule_shift_id']) ?>">
                        <input type="hidden" name="worked_date" value="<?= e($today) ?>">
                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                        <button type="submit" class="btn small">Start Break</button>
                    </form>
                    <form method="post" action="/index.php" class="inline">
                        <input type="hidden" name="action" value="end_break">
                        <input type="hidden" name="employee_id" value="<?= e((string) $assignment['employee_id']) ?>">
                        <input type="hidden" name="worked_date" value="<?= e($today) ?>">
                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                        <button type="submit" class="btn secondary small">End Break</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>

<section class="card">
    <div class="section-title">
        <h3>Break Monitoring</h3>
        <span>Live break activity</span>
    </div>
    <table>
        <thead>
        <tr>
            <th>Employee</th>
            <th>Shift</th>
            <th>Break Start</th>
            <th>Break End</th>
            <th>Delay (min)</th>
            <th>Status</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($breaks as $break): ?>
            <tr>
                <td><?= e($break['employee_name']) ?></td>
                <td><?= e($break['shift_name']) ?></td>
                <td><?= e($break['break_start']) ?></td>
                <td><?= e($break['break_end']) ?></td>
                <td><?= e((string) $break['delay_minutes']) ?></td>
                <td><?= e($break['status']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>

<section class="card">
    <div class="section-title">
        <h3>Weekly Schedule Summary</h3>
        <span>Snapshot of the current week</span>
    </div>
    <table>
        <thead>
        <tr>
            <th>Date</th>
            <th>Shift</th>
            <th>Employee</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($weekly as $entry): ?>
            <tr>
                <td><?= e($entry['shift_date']) ?></td>
                <td><?= e($entry['shift_name']) ?></td>
                <td><?= e($entry['employee_name']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
