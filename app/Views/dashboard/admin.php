<?php
declare(strict_types=1);
?>
<?php if (is_primary_admin($user)): ?>
    <section class="card">
        <div class="section-title">
            <h2>Submission Controls</h2>
            <span>Lock or reopen requests instantly</span>
        </div>
        <form method="post" class="inline">
            <input type="hidden" name="action" value="toggle_submission">
            <input type="hidden" name="locked" value="1">
            <button class="btn danger" type="submit">Stop submissions for this week</button>
        </form>
        <form method="post" class="inline">
            <input type="hidden" name="action" value="toggle_submission">
            <button class="btn secondary" type="submit">Allow submissions</button>
        </form>
        <p class="muted helper-text">Current status: <?= $submissionLocked ? 'Disabled for this week' : 'Open (resets Monday)' ?></p>
    </section>
<?php endif; ?>

<section class="card">
    <div class="section-title">
        <h2>Requests (Week <?= e($weekStart) ?>)</h2>
        <span>Review demand and request priorities</span>
    </div>
    <table>
        <tr>
            <th>Employee</th>
            <th>Submitted</th>
            <th>Requested</th>
            <th>Schedule option</th>
            <th>Reason / Importance</th>
            <th>Status</th>
            <th>Previous week</th>
            <?php if (is_primary_admin($user)): ?><th>Actions</th><?php endif; ?>
        </tr>
        <?php foreach ($requests as $req): ?>
            <tr>
                <td>
                    <strong><?= e($req['employee_name']) ?></strong><br>
                    <span class="muted"><?= e($req['employee_identifier']) ?></span><br>
                    <span class="muted"><?= e($req['email']) ?></span>
                    <?php if ($req['flagged']): ?><div class="flag">Important</div><?php endif; ?>
                </td>
                <td><?= e($req['submission_date']) ?></td>
                <td><?= e($req['requested_day']) ?> <?= $req['day_off'] ? '(Day off)' : '' ?> &middot; <?= e($req['shift_type']) ?></td>
                <td><?= e(schedule_option_label($req['schedule_option'])) ?></td>
                <td>
                    <?= e($req['reason']) ?><br>
                    <span <?= importance_badge($req['importance']) ?>><?= e(ucfirst($req['importance'])) ?></span>
                </td>
                <td><span class="status <?= e($req['status']) ?>"><?= e(ucfirst($req['status'])) ?></span></td>
                <td><?= e($req['previous_week_request'] ?? 'n/a') ?></td>
                <?php if (is_primary_admin($user)): ?>
                    <td class="table-actions">
                        <form method="post" class="inline">
                            <input type="hidden" name="action" value="update_request_status">
                            <input type="hidden" name="request_id" value="<?= (int) $req['id'] ?>">
                            <input type="hidden" name="status" value="accepted">
                            <button class="btn small" type="submit">Accept</button>
                        </form>
                        <form method="post" class="inline">
                            <input type="hidden" name="action" value="update_request_status">
                            <input type="hidden" name="request_id" value="<?= (int) $req['id'] ?>">
                            <input type="hidden" name="status" value="declined">
                            <button class="btn danger small" type="submit">Decline</button>
                        </form>
                        <form method="post" class="inline">
                            <input type="hidden" name="action" value="update_request_status">
                            <input type="hidden" name="request_id" value="<?= (int) $req['id'] ?>">
                            <input type="hidden" name="status" value="pending">
                            <button class="btn secondary small" type="submit">Pending</button>
                        </form>
                        <form method="post" class="inline inline-block">
                            <input type="hidden" name="action" value="toggle_flag">
                            <input type="hidden" name="request_id" value="<?= (int) $req['id'] ?>">
                            <input type="hidden" name="flagged" value="<?= $req['flagged'] ? 0 : 1 ?>">
                            <button class="btn secondary small" type="submit"><?= $req['flagged'] ? 'Unflag' : 'Flag important' ?></button>
                        </form>
                    </td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
    </table>
</section>

<?php if (is_primary_admin($user)): ?>
    <section class="card">
        <div class="section-title">
            <h2>Shift Requirements &amp; Senior Staff</h2>
            <span>Define weekly coverage targets</span>
        </div>
        <form method="post" class="grid">
            <input type="hidden" name="action" value="save_requirements">
            <div>
                <label>AM required</label>
                <input type="number" name="am_required" value="<?= (int) $requirements['am_required'] ?>" min="0">
            </div>
            <div>
                <label>PM required</label>
                <input type="number" name="pm_required" value="<?= (int) $requirements['pm_required'] ?>" min="0">
            </div>
            <div>
                <label>MID required</label>
                <input type="number" name="mid_required" value="<?= (int) $requirements['mid_required'] ?>" min="0">
            </div>
            <div class="span-full">
                <label>Senior staff (notes)</label>
                <textarea name="senior_staff" placeholder="List senior staff or team leaders"><?= e($requirements['senior_staff'] ?? '') ?></textarea>
            </div>
            <div class="span-full">
                <button class="btn" type="submit">Save requirements</button>
            </div>
        </form>
        <form method="post" class="form-actions">
            <input type="hidden" name="action" value="generate_schedule">
            <button class="btn" type="submit">Generate Schedule</button>
        </form>
    </section>
<?php endif; ?>

<?php render_view('shifts/admin-schedule', [
    'user' => $user,
    'weekStart' => $weekStart,
    'schedule' => $schedule,
]); ?>

<?php if (is_primary_admin($user)): ?>
    <section class="card">
        <div class="section-title">
            <h2>Manage Employees</h2>
            <span>Maintain active employee records</span>
        </div>
        <table>
            <tr>
                <th>Name</th>
                <th>Employee ID</th>
                <th>Email</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($employees as $emp): ?>
                <tr>
                    <td><?= e($emp['name']) ?></td>
                    <td><?= e($emp['employee_identifier']) ?></td>
                    <td><?= e($emp['email']) ?></td>
                    <td class="table-actions">
                        <form method="post" class="inline">
                            <input type="hidden" name="action" value="delete_employee">
                            <input type="hidden" name="employee_id" value="<?= (int) $emp['id'] ?>">
                            <button class="btn danger small" type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <p class="muted helper-text">Add new employees by inserting them into the <code>users</code> table with the <strong>employee</strong> role.</p>
    </section>
<?php endif; ?>
