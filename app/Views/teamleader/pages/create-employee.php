<?php
declare(strict_types=1);
?>
<section class="dashboard-surface teamleader-dashboard-page">
    <div class="dashboard-inner">
        <div class="card">
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
                            <?php if (in_array($role['role_name'], ['Employee'], true)): ?>
                                <option value="<?= e((string) $role['id']) ?>"><?= e($role['role_name']) ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    Experience Level
                    <input type="number" name="seniority_level" min="0" value="0">
                    <small class="muted">Higher values place employees earlier in coverage sorting.</small>
                </label>
                <div class="form-actions">
                    <button type="submit" class="btn">Create Employee</button>
                </div>
            </form>
        </div>
    </div>
</section>
