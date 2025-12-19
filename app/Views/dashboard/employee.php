<?php
declare(strict_types=1);
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
                        <?php foreach (['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'] as $day): ?>
                            <option value="<?= e($day) ?>"><?= e($day) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label>Shift Type</label>
                    <select name="shift_type" required>
                        <option value="AM">AM</option>
                        <option value="PM">PM</option>
                        <option value="MID">MID</option>
                    </select>
                </div>
                <div>
                    <label>Schedule Option</label>
                    <select name="schedule_option" required>
                        <option value="5x2">Work 5 days / 2 off (9 hours)</option>
                        <option value="6x1">Work 6 days / 1 off (7.5 hours)</option>
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
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
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

<section class="card">
    <div class="section-title">
        <h2>My Schedule</h2>
        <span>Personal assignments for the week</span>
    </div>
    <table>
        <tr>
            <th>Day</th>
            <th>Shift</th>
            <th>Status</th>
            <th>Notes</th>
        </tr>
        <?php foreach (array_filter($schedule, fn($s) => (int) $s['user_id'] === (int) $user['id']) as $entry): ?>
            <tr>
                <td><?= e($entry['day']) ?></td>
                <td><?= e($entry['shift_type']) ?></td>
                <td><span class="status <?= e($entry['status']) ?>"><?= e(ucfirst(str_replace('_', ' ', $entry['status']))) ?></span></td>
                <td><?= e($entry['notes'] ?? '') ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</section>
