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
    <form method="post" action="/index.php" class="grid" id="request-form">
        <input type="hidden" name="action" value="submit_request">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <label>
            Date (Monday-Saturday only)
            <input type="date" name="request_date" id="request_date" required>
            <small class="muted">Sunday is not allowed for shift requests</small>
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
        <h3>My Shift Requests</h3>
        <span><?= e(count($myRequests ?? [])) ?> requests</span>
    </div>
    <?php if (!empty($myRequests)): ?>
        <table>
            <thead>
            <tr>
                <th>Date</th>
                <th>Shift</th>
                <th>Pattern</th>
                <th>Importance</th>
                <th>Status</th>
                <th>Reason</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($myRequests as $request): ?>
                <tr>
                    <td><?= e($request['request_date']) ?></td>
                    <td><?= e($request['shift_name'] ?? 'OFF') ?></td>
                    <td><?= e($request['pattern_name']) ?></td>
                    <td><span class="pill"><?= e($request['importance_level']) ?></span></td>
                    <td><span class="pill <?= strtolower($request['status']) ?>"><?= e($request['status']) ?></span></td>
                    <td><?= e($request['reason'] ?? '-') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="muted">No shift requests submitted yet.</p>
    <?php endif; ?>
</section>

<section class="card">
    <div class="section-title">
        <h3>My Weekly Schedule</h3>
        <span>Your assigned shifts for this week</span>
    </div>
    <?php 
    $mySchedule = array_filter($schedule ?? [], function($entry) use ($user) {
        return isset($entry['employee_id']) && $entry['employee_id'] == ($user['employee_id'] ?? null);
    });
    ?>
    <?php if (!empty($mySchedule)): ?>
        <table>
            <thead>
            <tr>
                <th>Date</th>
                <th>Shift</th>
                <th>Status</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($mySchedule as $entry): ?>
                <tr>
                    <td><?= e($entry['shift_date']) ?></td>
                    <td><?= e($entry['shift_name']) ?></td>
                    <td><?= e($entry['assignment_source']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="muted">No schedule assignments for this week.</p>
    <?php endif; ?>
</section>

<section class="card">
    <div class="section-title">
        <h3>Break Management</h3>
        <span>One 30-minute break per day</span>
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
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dateInput = document.getElementById('request_date');
    if (dateInput) {
        dateInput.addEventListener('change', function() {
            const selectedDate = new Date(this.value);
            const dayOfWeek = selectedDate.getDay(); // 0 = Sunday, 1 = Monday, etc.
            
            if (dayOfWeek === 0) {
                alert('Shift requests are not allowed on Sunday. Please select a date from Monday to Saturday.');
                this.value = '';
                return false;
            }
        });
    }
});
</script>
