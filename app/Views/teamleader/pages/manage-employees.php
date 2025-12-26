<?php
declare(strict_types=1);

$roleIdByName = [];
foreach ($roles as $role) {
    $roleIdByName[$role['role_name']] = (int) $role['id'];
}
?>
<section class="dashboard-surface teamleader-dashboard-page">
    <div class="dashboard-inner">
        <div class="card">
            <div class="section-title">
                <div>
                    <h3>Manage Employees</h3>
                    <span><?= e(count($employees ?? [])) ?> employees in this section</span>
                </div>
                <a class="btn small" href="/dashboard/index.php?page=create-employee" data-teamleader-nav="true">Add Employee</a>
            </div>
            <?php if (!empty($employees)): ?>
                <table>
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Employee Code</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($employees as $employee): ?>
                        <tr>
                            <td><?= e($employee['full_name']) ?></td>
                            <td><?= e($employee['employee_code']) ?></td>
                            <td><?= e($employee['username']) ?></td>
                            <td><?= e($employee['email'] ?? '-') ?></td>
                            <td><span class="pill"><?= e($employee['role_name']) ?></span></td>
                            <td class="table-actions">
                                <button
                                    type="button"
                                    class="btn secondary small btn-update-employee"
                                    data-employee-id="<?= e((string) $employee['id']) ?>"
                                    data-full-name="<?= e($employee['full_name']) ?>"
                                    data-email="<?= e($employee['email'] ?? '') ?>"
                                    data-role-id="<?= e((string) ($roleIdByName[$employee['role_name']] ?? 0)) ?>"
                                    data-seniority-level="<?= e((string) ($employee['seniority_level'] ?? 0)) ?>"
                                >
                                    Update
                                </button>
                                <form method="post" action="/index.php" class="inline" style="display: inline-block; margin-left: 0.5rem;" onsubmit="return confirm('Are you sure you want to delete this employee?');">
                                    <input type="hidden" name="action" value="delete_employee">
                                    <input type="hidden" name="employee_id" value="<?= e((string) $employee['id']) ?>">
                                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                    <button type="submit" class="btn secondary small" style="background: var(--danger);">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-title">No employees found</div>
                    <p class="empty-state-text">Create your first employee using the "Create Employee" page.</p>
                    <a class="btn small" href="/dashboard/index.php?page=create-employee" data-teamleader-nav="true">Add Employee</a>
                </div>
            <?php endif; ?>
        </div>
        <div id="update-employee-modal" class="modal" style="display: none;">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Update Employee</h3>
                    <button type="button" class="modal-close" data-modal-close="update-employee-modal">&times;</button>
                </div>
                <form method="post" action="/index.php" id="update-employee-form">
                    <input type="hidden" name="action" value="update_employee">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="employee_id" id="update-employee-id">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="update-full-name" class="form-label">Full Name</label>
                            <input type="text" id="update-full-name" name="full_name" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label for="update-email" class="form-label">Email</label>
                            <input type="email" id="update-email" name="email" class="form-input" placeholder="Optional">
                        </div>
                        <div class="form-group">
                            <label for="update-role" class="form-label">Role</label>
                            <select id="update-role" name="role_id" class="form-input" required>
                                <?php foreach ($roles as $role): ?>
                                    <?php if (in_array($role['role_name'], ['Employee'], true)): ?>
                                        <option value="<?= e((string) $role['id']) ?>"><?= e($role['role_name']) ?></option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="update-seniority" class="form-label">Experience Level</label>
                            <input type="number" id="update-seniority" name="seniority_level" class="form-input" min="0" value="0">
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn secondary modal-cancel" data-modal-close="update-employee-modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
