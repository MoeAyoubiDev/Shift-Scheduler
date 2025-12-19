<?php
declare(strict_types=1);
?>
<section class="card">
    <div class="hero-row">
        <div>
            <h2>My Schedule</h2>
            <p>Submit shift requests and manage your break.</p>
        </div>
        <div class="meta-row">
            <span class="pill">Week <?= e($weekStart) ?> → <?= e($weekEnd) ?></span>
        </div>
    </div>
</section>

<section class="card">
    <div class="section-title">
        <h3>Submit Shift Request</h3>
        <span>Requests allowed Monday through Saturday only</span>
    </div>
    <form method="post" action="/index.php" class="grid">
        <input type="hidden" name="action" value="submit_request">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <label>
            Date
            <input type="date" name="submit_date" required>
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
</section>

<section class="card">
    <div class="section-title">
        <h3>Weekly Schedule</h3>
        <span><?= e(count($schedule)) ?> assignments</span>
    </div>
    <table>
        <thead>
        <tr>
            <th>Date</th>
            <th>Shift</th>
            <th>Employee</th>
            <th>Status</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($schedule as $entry): ?>
            <tr>
                <td><?= e($entry['shift_date']) ?></td>
                <td><?= e($entry['shift_name']) ?></td>
                <td><?= e($entry['employee_name']) ?></td>
                <td><?= e($entry['assignment_source']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>

<section class="card">
    <div class="section-title">
        <h3>Break Actions</h3>
        <span>One 30-minute break per shift</span>
    </div>
    <div class="quick-actions">
        <form method="post" action="/index.php" class="inline">
            <input type="hidden" name="action" value="start_break">
            <input type="hidden" name="worked_date" value="<?= e((new DateTimeImmutable())->format('Y-m-d')) ?>">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <button type="submit" class="btn">Start Break</button>
        </form>
        <form method="post" action="/index.php" class="inline">
            <input type="hidden" name="action" value="end_break">
            <input type="hidden" name="worked_date" value="<?= e((new DateTimeImmutable())->format('Y-m-d')) ?>">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <button type="submit" class="btn secondary">End Break</button>
        </form>
    </div>
    <p class="muted">Break delays are tracked automatically for performance analytics.</p>
</section>
