<?php
declare(strict_types=1);

$scheduleDays = config('schedule', 'schedule_days', ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday', 'N/A']);
$editableShiftTypes = config('schedule', 'editable_shift_types', ['AM', 'PM', 'MID', 'OFF', 'UNASSIGNED']);
?>
<section class="card">
    <div class="section-title">
        <h2>Schedule (Week <?= e($weekStart) ?>)</h2>
        <span>Auto-generated assignments and notes</span>
    </div>
    <div class="quick-actions">
        <a class="btn secondary" href="?download=schedule">Download Excel/CSV</a>
    </div>
    <table>
        <tr>
            <th>Employee</th>
            <th>Day</th>
            <th>Shift</th>
            <th>Status</th>
            <th>Notes</th>
            <?php if (is_primary_admin($user)): ?><th>Edit</th><?php endif; ?>
        </tr>
        <?php foreach ($schedule as $entry): ?>
            <tr>
                <td><?= e($entry['employee_name']) ?></td>
                <td><?= e($entry['day']) ?></td>
                <td><?= e($entry['shift_type']) ?></td>
                <td><span class="status <?= e($entry['status']) ?>"><?= e(ucfirst(str_replace('_', ' ', $entry['status']))) ?></span></td>
                <td><?= e($entry['notes'] ?? '') ?></td>
                <?php if (is_primary_admin($user)): ?>
                    <td>
                        <form method="post" class="grid edit-grid">
                            <input type="hidden" name="action" value="update_schedule_entry">
                            <input type="hidden" name="entry_id" value="<?= (int) $entry['id'] ?>">
                            <select name="day">
                                <?php foreach ($scheduleDays as $day): ?>
                                    <option value="<?= e($day) ?>" <?= $day === $entry['day'] ? 'selected' : '' ?>><?= e($day) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <select name="shift_type">
                                <?php foreach ($editableShiftTypes as $shift): ?>
                                    <option value="<?= e($shift) ?>" <?= $shift === $entry['shift_type'] ? 'selected' : '' ?>><?= e($shift) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <input type="text" name="notes" value="<?= e($entry['notes'] ?? '') ?>" placeholder="Notes">
                            <button class="btn secondary small" type="submit">Save</button>
                        </form>
                    </td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
    </table>
</section>
