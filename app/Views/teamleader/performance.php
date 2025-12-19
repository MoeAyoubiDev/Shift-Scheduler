<?php
require_once __DIR__ . '/../layouts/header.php';
?>

<h1>Performance Analytics</h1>

<div class="filters">
    <form method="GET" class="filter-form">
        <div class="form-group">
            <label for="month">Filter by Month:</label>
            <input type="month" id="month" name="month" value="<?= htmlspecialchars($filters['month'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label for="start_date">Start Date:</label>
            <input type="date" id="start_date" name="start_date" value="<?= htmlspecialchars($filters['start_date'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label for="end_date">End Date:</label>
            <input type="date" id="end_date" name="end_date" value="<?= htmlspecialchars($filters['end_date'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label for="employee_id">Employee:</label>
            <select id="employee_id" name="employee_id">
                <option value="">All Employees</option>
                <?php foreach ($employees as $emp): ?>
                    <option value="<?= $emp['id'] ?>" <?= $filters['employee_id'] == $emp['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($emp['full_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Apply Filters</button>
        <a href="/performance.php" class="btn">Clear Filters</a>
    </form>
</div>

<?php if (empty($report)): ?>
    <p>No performance data found for the selected filters.</p>
<?php else: ?>
    <table class="data-table">
        <thead>
            <tr>
                <th>Employee Name</th>
                <th>Employee Code</th>
                <th>Section</th>
                <th>Days Worked</th>
                <th>Total Delay (minutes)</th>
                <th>Average Delay (minutes)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($report as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['full_name']) ?></td>
                    <td><?= htmlspecialchars($row['employee_code']) ?></td>
                    <td><?= htmlspecialchars($row['section_name']) ?></td>
                    <td><?= $row['days_worked'] ?></td>
                    <td><?= number_format($row['total_delay_minutes'], 2) ?></td>
                    <td><?= number_format($row['average_delay_minutes'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<a href="/dashboard.php" class="btn">Back to Dashboard</a>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

