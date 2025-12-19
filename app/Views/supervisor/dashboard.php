<?php
declare(strict_types=1);
?>
<section class="card">
    <div class="hero-row">
        <div>
            <h2>Supervisor Dashboard</h2>
            <p>Read-only schedule, employee, and performance insights.</p>
        </div>
        <div class="meta-row">
            <span class="pill">Week <?= e($weekStart) ?> → <?= e($weekEnd) ?></span>
        </div>
    </div>
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
            <th>Source</th>
            <th>Notes</th>
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
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>

<section class="card">
    <div class="section-title">
        <h3>Employee Roster</h3>
        <span><?= e(count($employees)) ?> employees</span>
    </div>
    <table>
        <thead>
        <tr>
            <th>Name</th>
            <th>Code</th>
            <th>Role</th>
            <th>Seniority</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($employees as $employee): ?>
            <tr>
                <td><?= e($employee['full_name']) ?></td>
                <td><?= e($employee['employee_code']) ?></td>
                <td><?= e($employee['role_name']) ?></td>
                <td><?= e((string) $employee['seniority_level']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>

<section class="card">
    <div class="section-title">
        <h3>Break Monitoring</h3>
        <span>Today</span>
    </div>
    <table>
        <thead>
        <tr>
            <th>Employee</th>
            <th>Shift</th>
            <th>Break Start</th>
            <th>Break End</th>
            <th>Delay</th>
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
        <span>Sorted by lowest delay</span>
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
