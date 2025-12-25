<?php
declare(strict_types=1);

$title = 'Employees Setup - Onboarding';
$step = 3;
$totalSteps = 5;

require_once __DIR__ . '/../../../includes/header.php';

$user = current_user();
$companyId = $user['company_id'] ?? null;
$company = Company::findById((int)$companyId);
$progress = Company::getOnboardingProgress((int)$companyId);
$stepData = $progress["step_3"]['data'] ?? [];
$employees = $stepData['employees'] ?? [];

$error = $_GET['error'] ?? '';
?>

<link rel="stylesheet" href="/assets/css/onboarding.css">

<div class="onboarding-container">
    <div class="onboarding-card">
        <div class="onboarding-header">
            <h1 class="onboarding-title">Employees Setup</h1>
            <p class="onboarding-subtitle">Add your team members</p>
        </div>

        <div class="progress-bar">
            <?php for ($i = 1; $i <= $totalSteps; $i++): ?>
                <div class="progress-step <?= $i === $step ? 'active' : ($i < $step ? 'completed' : '') ?>">
                    <div class="progress-step-circle"><?= $i ?></div>
                    <span class="progress-step-label">Step <?= $i ?></span>
                </div>
            <?php endfor; ?>
        </div>

        <?php if ($error): ?>
            <div class="alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" action="/index.php" id="employees-form">
            <input type="hidden" name="action" value="onboarding_step">
            <input type="hidden" name="step" value="3">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <input type="hidden" name="employees" value='<?= json_encode($employees) ?>' id="employees-data">

            <div style="display: flex; gap: var(--space-4); margin-bottom: var(--space-6);">
                <button type="button" class="btn-primary" onclick="addEmployee()">Add Employee Manually</button>
                <button type="button" class="btn-secondary" onclick="importCSV()">Import from CSV</button>
            </div>

            <div id="employees-list">
                <?php if (empty($employees)): ?>
                    <p style="color: var(--color-text-secondary); text-align: center; padding: var(--space-8);">
                        No employees added yet. Click "Add Employee Manually" to get started.
                    </p>
                <?php else: ?>
                    <table class="employee-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="employees-tbody">
                            <?php foreach ($employees as $index => $emp): ?>
                                <tr data-index="<?= $index ?>">
                                    <td><?= htmlspecialchars($emp['name'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($emp['email'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($emp['role'] ?? 'Employee') ?></td>
                                    <td>
                                        <button type="button" onclick="removeEmployee(<?= $index ?>)" style="color: var(--destructive); background: none; border: none; cursor: pointer;">Remove</button>
                                    </td>
                                </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <div class="form-actions">
                <a href="/onboarding.php?step=2" class="btn-secondary">Back</a>
                <button type="submit" class="btn-primary">Continue</button>
            </div>
        </form>
    </div>
</div>

<div id="employee-modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: var(--glass-bg-dark); padding: var(--space-8); border-radius: var(--radius-xl); max-width: 500px; width: 90%;">
        <h3 style="margin-bottom: var(--space-6);">Add Employee</h3>
        <form id="employee-form-modal">
            <div class="form-group">
                <label class="form-label">Full Name</label>
                <input type="text" class="form-input" id="emp-name" required>
            </div>
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" class="form-input" id="emp-email" required>
            </div>
            <div class="form-group">
                <label class="form-label">Role</label>
                <select class="form-select" id="emp-role">
                    <option value="Employee">Employee</option>
                    <option value="Team Leader">Team Leader</option>
                </select>
            </div>
            <div class="form-actions">
                <button type="button" class="btn-secondary" onclick="closeEmployeeModal()">Cancel</button>
                <button type="button" class="btn-primary" onclick="saveEmployee()">Add</button>
            </div>
        </form>
    </div>
</div>

<script>
let employeesList = <?= json_encode($employees) ?>;

function addEmployee() {
    document.getElementById('employee-modal').style.display = 'flex';
}

function closeEmployeeModal() {
    document.getElementById('employee-modal').style.display = 'none';
    document.getElementById('employee-form-modal').reset();
}

function saveEmployee() {
    const name = document.getElementById('emp-name').value;
    const email = document.getElementById('emp-email').value;
    const role = document.getElementById('emp-role').value;

    if (!name || !email) {
        alert('Please fill in all required fields');
        return;
    }

    employeesList.push({ name, email, role });
    updateEmployeesDisplay();
    closeEmployeeModal();
}

function removeEmployee(index) {
    employeesList.splice(index, 1);
    updateEmployeesDisplay();
}

function updateEmployeesDisplay() {
    document.getElementById('employees-data').value = JSON.stringify(employeesList);
    
    const tbody = document.getElementById('employees-tbody');
    if (!tbody) return;

    if (employeesList.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" style="text-align: center; color: var(--color-text-secondary); padding: var(--space-8);">No employees added yet.</td></tr>';
        return;
    }

    tbody.innerHTML = employeesList.map((emp, index) => `
        <tr data-index="${index}">
            <td>${escapeHtml(emp.name || '')}</td>
            <td>${escapeHtml(emp.email || '')}</td>
            <td>${escapeHtml(emp.role || 'Employee')}</td>
            <td>
                <button type="button" onclick="removeEmployee(${index})" style="color: var(--destructive); background: none; border: none; cursor: pointer;">Remove</button>
            </td>
        </tr>
    `).join('');
}

function importCSV() {
    alert('CSV import feature will be implemented soon.');
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>

<?php require_once __DIR__ . '/../../../includes/footer.php'; ?>
