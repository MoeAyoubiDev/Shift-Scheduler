<?php
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../../Core/CSRF.php';
require_once __DIR__ . '/../../Core/Database.php';

$db = Database::getInstance();
$roles = $db->query("SELECT * FROM roles WHERE role_name != 'Director'")->fetchAll();
$users = $db->query("SELECT * FROM users WHERE is_active = 1")->fetchAll();
?>

<h1>Manage Employees</h1>

<div class="card">
    <h2>Add New Employee</h2>
    <form method="POST" action="/employees.php">
        <?= CSRF::tokenField() ?>
        <input type="hidden" name="action" value="create">
        <div class="form-group">
            <label for="user_id">User:</label>
            <select id="user_id" name="user_id" required>
                <option value="">Select User</option>
                <?php foreach ($users as $u): ?>
                    <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['username']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="role_id">Role:</label>
            <select id="role_id" name="role_id" required>
                <?php foreach ($roles as $r): ?>
                    <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['role_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="employee_code">Employee Code:</label>
            <input type="text" id="employee_code" name="employee_code" required>
        </div>
        <div class="form-group">
            <label for="full_name">Full Name:</label>
            <input type="text" id="full_name" name="full_name" required>
        </div>
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email">
        </div>
        <div class="form-group">
            <label>
                <input type="checkbox" name="is_senior" value="1">
                Is Senior
            </label>
        </div>
        <div class="form-group">
            <label for="seniority_level">Seniority Level:</label>
            <input type="number" id="seniority_level" name="seniority_level" value="0" min="0">
        </div>
        <button type="submit" class="btn btn-primary">Create Employee</button>
    </form>
</div>

<div class="card">
    <h2>Existing Employees</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th>Employee Code</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Is Senior</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($employees as $emp): ?>
                <tr>
                    <td><?= htmlspecialchars($emp['employee_code']) ?></td>
                    <td><?= htmlspecialchars($emp['full_name']) ?></td>
                    <td><?= htmlspecialchars($emp['email'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($emp['role_name']) ?></td>
                    <td><?= $emp['is_senior'] ? 'Yes' : 'No' ?></td>
                    <td>
                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure?');">
                            <?= CSRF::tokenField() ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="employee_id" value="<?= $emp['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<a href="/dashboard.php" class="btn">Back to Dashboard</a>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

