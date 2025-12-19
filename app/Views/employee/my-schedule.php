<?php
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../../Core/CSRF.php';
?>

<h1>My Schedule - Week of <?= date('M d, Y', strtotime($weekStart)) ?></h1>

<div class="schedule-section">
    <h2>This Week's Schedule</h2>
    <?php if (empty($mySchedule)): ?>
        <p>No schedule assigned for this week.</p>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Shift</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($mySchedule as $sched): ?>
                    <tr>
                        <td><?= date('D, M d', strtotime($sched['date'])) ?></td>
                        <td><?= htmlspecialchars($sched['shift_name']) ?></td>
                        <td><?= $sched['start_time'] ?></td>
                        <td><?= $sched['end_time'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<div class="break-section">
    <h2>Today's Break</h2>
    <?php if ($currentDate == date('Y-m-d')): ?>
        <?php if (!$todayBreak || !$todayBreak['break_start']): ?>
            <p>No break started yet today.</p>
            <form method="POST" action="/manage-break.php">
                <?= CSRF::tokenField() ?>
                <input type="hidden" name="action" value="start">
                <button type="submit" class="btn btn-primary">Start Break</button>
            </form>
        <?php elseif ($todayBreak['break_start'] && !$todayBreak['break_end']): ?>
            <p>Break started at: <?= date('H:i', strtotime($todayBreak['break_start'])) ?></p>
            <p>Duration: <?= round((time() - strtotime($todayBreak['break_start'])) / 60) ?> minutes</p>
            <form method="POST" action="/manage-break.php">
                <?= CSRF::tokenField() ?>
                <input type="hidden" name="action" value="end">
                <button type="submit" class="btn btn-primary">End Break</button>
            </form>
        <?php else: ?>
            <p>Break completed.</p>
            <p>Started: <?= date('H:i', strtotime($todayBreak['break_start'])) ?></p>
            <p>Ended: <?= date('H:i', strtotime($todayBreak['break_end'])) ?></p>
            <p>Duration: <?= round((strtotime($todayBreak['break_end']) - strtotime($todayBreak['break_start'])) / 60) ?> minutes</p>
        <?php endif; ?>
    <?php endif; ?>
</div>

<div class="request-section">
    <h2>Submit Shift Request</h2>
    <form method="POST" action="/submit-request.php">
        <?= CSRF::tokenField() ?>
        <div class="form-group">
            <label for="submit_date">Date (Monday-Saturday only):</label>
            <input type="date" id="submit_date" name="submit_date" required>
        </div>
        <div class="form-group">
            <label>
                <input type="checkbox" name="is_day_off" value="1" id="is_day_off">
                Day Off
            </label>
        </div>
        <div class="form-group" id="shift_select">
            <label for="shift_definition_id">Shift:</label>
            <select id="shift_definition_id" name="shift_definition_id">
                <option value="">Select Shift</option>
                <?php foreach ($shiftDefs as $def): ?>
                    <option value="<?= $def['id'] ?>"><?= htmlspecialchars($def['shift_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="schedule_pattern_id">Schedule Pattern:</label>
            <select id="schedule_pattern_id" name="schedule_pattern_id" required>
                <?php foreach ($patterns as $pattern): ?>
                    <option value="<?= $pattern['id'] ?>"><?= htmlspecialchars($pattern['pattern_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="importance_level">Importance:</label>
            <select id="importance_level" name="importance_level">
                <option value="LOW">Low</option>
                <option value="NORMAL" selected>Normal</option>
                <option value="HIGH">High</option>
            </select>
        </div>
        <div class="form-group">
            <label for="reason">Reason:</label>
            <textarea id="reason" name="reason" rows="3"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Submit Request</button>
    </form>
</div>

<div class="requests-section">
    <h2>My Shift Requests</h2>
    <?php if (empty($requests)): ?>
        <p>No shift requests submitted.</p>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Shift</th>
                    <th>Pattern</th>
                    <th>Status</th>
                    <th>Submitted</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requests as $req): ?>
                    <tr>
                        <td><?= date('M d, Y', strtotime($req['submit_date'])) ?></td>
                        <td><?= $req['is_day_off'] ? 'OFF' : htmlspecialchars($req['shift_name'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($req['pattern_name']) ?></td>
                        <td><span class="badge badge-<?= strtolower($req['status']) ?>"><?= $req['status'] ?></span></td>
                        <td><?= date('M d, H:i', strtotime($req['submitted_at'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<script>
document.getElementById('is_day_off').addEventListener('change', function() {
    document.getElementById('shift_select').style.display = this.checked ? 'none' : 'block';
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

