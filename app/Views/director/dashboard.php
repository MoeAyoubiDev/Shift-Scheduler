<?php
declare(strict_types=1);
?>
<section class="card">
    <div class="hero-row">
        <div>
            <h2>Director Overview</h2>
            <p>Read-only visibility for <?= e($user['section_name'] ?? 'Selected Section') ?>.</p>
        </div>
        <div class="meta-row">
            <span class="pill">Week <?= e($weekStart) ?> → <?= e($weekEnd) ?></span>
            <a class="btn secondary small" href="/index.php?reset_section=1">Change Section</a>
        </div>
    </div>
    <div class="grid">
        <?php foreach ($dashboard as $metric): ?>
            <div class="metric">
                <h3><?= e($metric['label']) ?></h3>
                <div class="metric-value"><?= e((string) $metric['value']) ?></div>
                <span class="muted"><?= e($metric['description']) ?></span>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<section class="card">
    <div class="section-title">
        <h3>Weekly Schedule Snapshot</h3>
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
        <h3>Shift Requests</h3>
        <span><?= e(count($requests)) ?> requests</span>
    </div>
    <table>
        <thead>
        <tr>
            <th>Employee</th>
            <th>Submit Date</th>
            <th>Shift</th>
            <th>Pattern</th>
            <th>Importance</th>
            <th>Status</th>
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
