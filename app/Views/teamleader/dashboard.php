<?php
declare(strict_types=1);

$weekDates = [];
$weekStartDate = new DateTimeImmutable($weekStart);
for ($i = 0; $i < 7; $i++) {
    $weekDates[] = $weekStartDate->modify('+' . $i . ' day')->format('Y-m-d');
}
?>
<section class="card">
    <div class="hero-row">
        <div>
            <h2>Team Leader Control Center</h2>
            <p>Full CRUD permissions for <?= e($user['section_name'] ?? 'your section') ?>.</p>
        </div>
        <div class="meta-row">
            <span class="pill">Week <?= e($weekStart) ?> → <?= e($weekEnd) ?></span>
            <a class="btn secondary small" href="/index.php?download=schedule">Export CSV</a>
        </div>
    </div>
</section>

<section class="card">
    <div class="section-title">
        <h3>Create Employee</h3>
        <span>Add employees to this section</span>
    </div>
    <form method="post" action="/index.php" class="grid">
        <input type="hidden" name="action" value="create_employee">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="section_id" value="<?= e((string) $user['section_id']) ?>">
        <label>
            Full Name
            <input type="text" name="full_name" required>
        </label>
        <label>
            Employee Code
            <input type="text" name="employee_code" required>
        </label>
        <label>
            Username
            <input type="text" name="username" required>
        </label>
        <label>
            Email
            <input type="email" name="email">
        </label>
        <label>
            Password
            <input type="password" name="password" required>
        </label>
        <label>
            Role
            <select name="role_id" required>
                <?php foreach ($roles as $role): ?>
                    <?php if (in_array($role['role_name'], ['Employee', 'Senior', 'Supervisor'], true)): ?>
                        <option value="<?= e((string) $role['id']) ?>\"><?= e($role['role_name']) ?></option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
        </label>
        <label>
            Seniority Level
            <input type="number" name="seniority_level" min="0" value="0">
        </label>
        <label class="checkbox-row">
            <input type="checkbox" name="is_senior" value="1">
            Mark as Senior
        </label>
        <div class="form-actions">
            <button type="submit" class="btn">Create Employee</button>
        </div>
    </form>
</section>

<section class="card">
    <div class="section-title">
        <h3>Shift Requirements</h3>
        <span>Define required coverage per shift</span>
    </div>
    <form method="post" action="/index.php">
        <input type="hidden" name="action" value="save_requirements">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <div class="table-wrapper">
            <table>
                <thead>
                <tr>
                    <th>Date</th>
                    <?php foreach ($shiftTypes as $shiftType): ?>
                        <th><?= e($shiftType['shift_type_name']) ?></th>
                    <?php endforeach; ?>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($weekDates as $date): ?>
                    <tr>
                        <td><?= e($date) ?></td>
                        <?php foreach ($shiftTypes as $shiftType): ?>
                            <?php
                                $existing = 0;
                                foreach ($requirements as $requirement) {
                                    if ($requirement['shift_date'] === $date && (int) $requirement['shift_type_id'] === (int) $shiftType['shift_type_id']) {
                                        $existing = (int) $requirement['required_count'];
                                        break;
                                    }
                                }
                            ?>
                            <td>
                                <input type="number" name="requirements[<?= e((string) $shiftType['shift_type_id']) ?>][<?= e($date) ?>]" min="0" value="<?= e((string) $existing) ?>">
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn">Save Requirements</button>
        </div>
    </form>
</section>

<section class="card">
    <div class="section-title">
        <h3>Shift Requests</h3>
        <span><?= e(count($requests)) ?> requests awaiting review</span>
    </div>
    <table>
        <thead>
        <tr>
            <th>Employee</th>
            <th>Date</th>
            <th>Shift</th>
            <th>Pattern</th>
            <th>Importance</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($requests as $request): ?>
            <tr>
                <td><?= e($request['employee_name']) ?></td>
                <td><?= e($request['submit_date']) ?></td>
                <td><?= e($request['shift_name']) ?></td>
                <td><?= e($request['pattern_name']) ?></td>
                <td><?= e($request['importance_level']) ?></td>
                <td><span class="status <?= strtolower($request['status']) ?>"><?= e($request['status']) ?></span></td>
                <td>
                    <form method="post" action="/index.php" class="inline">
                        <input type="hidden" name="action" value="update_request_status">
                        <input type="hidden" name="request_id" value="<?= e((string) $request['id']) ?>">
                        <input type="hidden" name="status" value="APPROVED">
                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                        <button type="submit" class="btn small">Approve</button>
                    </form>
                    <form method="post" action="/index.php" class="inline">
                        <input type="hidden" name="action" value="update_request_status">
                        <input type="hidden" name="request_id" value="<?= e((string) $request['id']) ?>">
                        <input type="hidden" name="status" value="DECLINED">
                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                        <button type="submit" class="btn danger small">Decline</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>

<section class="card">
    <div class="hero-row">
        <div>
            <h3>Weekly Schedule</h3>
            <p>Generate and manually adjust the weekly schedule.</p>
        </div>
        <form method="post" action="/index.php" class="inline">
            <input type="hidden" name="action" value="generate_schedule">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <button type="submit" class="btn">Generate Schedule</button>
        </form>
    </div>
    <table>
        <thead>
        <tr>
            <th>Date</th>
            <th>Shift</th>
            <th>Employee</th>
            <th>Source</th>
            <th>Notes</th>
            <th>Update Shift</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($schedule as $entry): ?>
            <tr>
                <td><?= e($entry['shift_date']) ?></td>
                <td><?= e($entry['shift_name']) ?></td>
                <td><?= e($entry['employee_name']) ?></td>
                <td><?= e($entry['assignment_source']) ?></td>
                <td><?= e($entry['notes']) ?></td>
                <td>
                    <form method="post" action="/index.php" class="inline">
                        <input type="hidden" name="action" value="update_assignment">
                        <input type="hidden" name="assignment_id" value="<?= e((string) $entry['assignment_id']) ?>">
                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                        <select name="shift_definition_id">
                            <?php foreach ($shiftDefinitions as $definition): ?>
                                <option value="<?= e((string) $definition['definition_id']) ?>" <?= $definition['definition_id'] == $entry['shift_definition_id'] ? 'selected' : '' ?>>
                                    <?= e($definition['definition_name']) ?> (<?= e($definition['shift_type_name']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn small">Update</button>
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
        <span>Track active breaks and delays</span>
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
        <h3>Performance Analytics</h3>
        <span>Month-to-date delay summary</span>
    </div>
    <table>
        <thead>
        <tr>
            <th>Employee</th>
            <th>Days Worked</th>
            <th>Total Delay (min)</th>
            <th>Average Delay</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($performance as $row): ?>
            <tr>
                <td><?= e($row['employee_name']) ?></td>
                <td><?= e((string) $row['days_worked']) ?></td>
                <td><?= e((string) $row['total_delay_minutes']) ?></td>
                <td><?= e((string) $row['average_delay_minutes']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
