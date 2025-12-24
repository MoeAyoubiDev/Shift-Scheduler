<?php
declare(strict_types=1);

/**
 * Company Onboarding Wizard
 * Multi-step guided setup process
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../app/Helpers/helpers.php';
require_once __DIR__ . '/../app/Models/Company.php';

$companyId = $_SESSION['onboarding_company_id'] ?? $_GET['company_id'] ?? null;

if (!$companyId) {
    header('Location: /signup.php');
    exit;
}

$company = Company::findById((int)$companyId);
if (!$company) {
    header('Location: /signup.php');
    exit;
}

// Company is auto-verified on signup, allow onboarding if status is VERIFIED or ONBOARDING
if (!in_array($company['status'], ['VERIFIED', 'ONBOARDING', 'PAYMENT_PENDING'])) {
    // If company is already active, redirect to login
    if ($company['status'] === 'ACTIVE') {
        header('Location: /login.php?message=' . urlencode('Your account is already set up. Please sign in.'));
        exit;
    }
    // Otherwise, redirect to signup
    header('Location: /signup.php');
    exit;
}

$currentStep = (int)($_GET['step'] ?? 1);
$totalSteps = 5;
$progress = Company::getOnboardingProgress((int)$companyId);

// Prevent skipping steps - ensure previous steps are completed
if ($currentStep > 1) {
    $requiredStep = $currentStep - 1;
    $prevStepData = $progress["step_{$requiredStep}"] ?? null;
    if (!$prevStepData || !$prevStepData['completed']) {
        header('Location: /onboarding.php?step=' . $requiredStep . '&company_id=' . $companyId);
        exit;
    }
}

$error = '';

// Handle step submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['step'])) {
    require_csrf($_POST);
    
    $step = (int)$_POST['step'];
    $stepData = $_POST;
    unset($stepData['step'], $stepData['csrf_token'], $stepData['action']);
    
    // Validation
    if ($step === 1) {
        if (empty($stepData['company_name']) || strlen($stepData['company_name']) < 2) {
            $error = 'Company name must be at least 2 characters.';
        }
    } elseif ($step === 2) {
        if (empty($stepData['default_shift_hours']) || $stepData['default_shift_hours'] < 4 || $stepData['default_shift_hours'] > 12) {
            $error = 'Shift duration must be between 4 and 12 hours.';
        }
    } elseif ($step === 3) {
        if (empty($stepData['employees']) || !is_array($stepData['employees'])) {
            $error = 'Please add at least one employee.';
        } else {
            foreach ($stepData['employees'] as $idx => $emp) {
                if (empty($emp['full_name'])) {
                    $error = "Employee " . ($idx + 1) . " must have a full name.";
                    break;
                }
            }
        }
    }
    
    if (!$error) {
        Company::updateOnboardingStep((int)$companyId, "step_{$step}", $stepData, true);
        
        if ($step < $totalSteps) {
            header('Location: /onboarding.php?step=' . ($step + 1) . '&company_id=' . $companyId);
            exit;
        } else {
            // Complete onboarding and create initial data
            Company::updateOnboardingStep((int)$companyId, 'completed', [], true);
            
            // Create initial sections and employees
            require_once __DIR__ . '/../app/Models/Section.php';
            require_once __DIR__ . '/../app/Models/User.php';
            
            $pdo = db();
            
            // Create default section
            $sectionName = $progress['step_1']['data']['company_name'] ?? $company['company_name'];
            $sectionStmt = $pdo->prepare("INSERT INTO sections (section_name, company_id) VALUES (?, ?)");
            $sectionStmt->execute([$sectionName . ' - Main', $companyId]);
            $sectionId = (int)$pdo->lastInsertId();
            
            // Create admin user
            $adminEmail = $company['admin_email'];
            $adminPassword = $company['admin_password_hash'];
            $username = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '', $company['company_name'])) . '_admin';
            
            $userStmt = $pdo->prepare("INSERT INTO users (username, password_hash, email, company_id) VALUES (?, ?, ?, ?)");
            $userStmt->execute([$username, $adminPassword, $adminEmail, $companyId]);
            $userId = (int)$pdo->lastInsertId();
            
            // Get Director role
            $roleStmt = $pdo->prepare("SELECT id FROM roles WHERE role_name = 'Director' LIMIT 1");
            $roleStmt->execute();
            $roleRow = $roleStmt->fetch(PDO::FETCH_ASSOC);
            $roleId = (int)($roleRow['id'] ?? 0);
            
            // Assign Director role
            $userRoleStmt = $pdo->prepare("INSERT INTO user_roles (user_id, role_id, section_id) VALUES (?, ?, ?)");
            $userRoleStmt->execute([$userId, $roleId, $sectionId]);
            
            // Create employees from step 3
            if (!empty($progress['step_3']['data']['employees'])) {
                $empRoleMap = ['Employee' => 'Employee', 'Senior' => 'Senior', 'Team Leader' => 'Team Leader'];
                foreach ($progress['step_3']['data']['employees'] as $idx => $emp) {
                    $empRoleName = $empRoleMap[$emp['role']] ?? 'Employee';
                    $empRoleStmt = $pdo->prepare("SELECT id FROM roles WHERE role_name = ? LIMIT 1");
                    $empRoleStmt->execute([$empRoleName]);
                    $empRoleRow = $empRoleStmt->fetch(PDO::FETCH_ASSOC);
                    $empRoleId = (int)($empRoleRow['id'] ?? 0);
                    
                    if ($empRoleId > 0) {
                        $empUsername = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '', $emp['full_name'])) . '_' . ($idx + 1);
                        $empPassword = password_hash('TempPass123!', PASSWORD_BCRYPT);
                        
                        $userStmt->execute([$empUsername, $empPassword, $emp['email'] ?? '', $companyId]);
                        $empUserId = (int)$pdo->lastInsertId();
                        
                        $userRoleStmt->execute([$empUserId, $empRoleId, $sectionId]);
                        $empUserRoleId = (int)$pdo->lastInsertId();
                        
                        // Only create employee record if role is Employee or Senior
                        if (in_array($empRoleName, ['Employee', 'Senior'])) {
                            $empCode = 'EMP' . str_pad($idx + 1, 3, '0', STR_PAD_LEFT);
                            $isSenior = ($empRoleName === 'Senior') ? 1 : 0;
                            
                            $employeeStmt = $pdo->prepare("
                                INSERT INTO employees (user_role_id, employee_code, full_name, email, is_senior, seniority_level)
                                VALUES (?, ?, ?, ?, ?, ?)
                            ");
                            $employeeStmt->execute([
                                $empUserRoleId,
                                $empCode,
                                $emp['full_name'],
                                $emp['email'] ?? '',
                                $isSenior,
                                $isSenior ? 5 : 0
                            ]);
                        }
                    }
                }
            }
            
            // Update company status
            $stmt = $pdo->prepare("UPDATE companies SET status = 'PAYMENT_PENDING', onboarding_completed_at = NOW() WHERE id = ?");
            $stmt->execute([$companyId]);
            
            header('Location: /onboarding-preview.php?company_id=' . $companyId);
            exit;
        }
    }
}

// Get saved data for current step
$savedData = $progress["step_{$currentStep}"]['data'] ?? [];

$title = 'Onboarding - Shift Scheduler';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="onboarding-container">
    <div class="onboarding-card">
        <!-- Progress Bar -->
        <div class="onboarding-progress">
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?= ($currentStep / $totalSteps) * 100 ?>%"></div>
            </div>
            <p class="progress-text">Step <?= $currentStep ?> of <?= $totalSteps ?></p>
        </div>

        <!-- Step Content -->
        <div class="onboarding-content">
            <?php if ($error): ?>
                <div class="alert alert-error"><?= e($error) ?></div>
            <?php endif; ?>
            
            <?php if ($currentStep === 1): ?>
                <!-- Step 1: Company Details -->
                <h2>Company Details</h2>
                <p class="step-description">Tell us more about your company</p>
                
                <form method="post" class="onboarding-form">
                    <input type="hidden" name="step" value="1">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    
                    <div class="form-group">
                        <label class="form-label">Company Name</label>
                        <input type="text" name="company_name" class="form-input" value="<?= e($savedData['company_name'] ?? $company['company_name']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Industry</label>
                        <select name="industry" class="form-input">
                            <option value="">Select industry</option>
                            <option value="retail" <?= ($savedData['industry'] ?? '') === 'retail' ? 'selected' : '' ?>>Retail</option>
                            <option value="healthcare" <?= ($savedData['industry'] ?? '') === 'healthcare' ? 'selected' : '' ?>>Healthcare</option>
                            <option value="hospitality" <?= ($savedData['industry'] ?? '') === 'hospitality' ? 'selected' : '' ?>>Hospitality</option>
                            <option value="manufacturing" <?= ($savedData['industry'] ?? '') === 'manufacturing' ? 'selected' : '' ?>>Manufacturing</option>
                            <option value="services" <?= ($savedData['industry'] ?? '') === 'services' ? 'selected' : '' ?>>Services</option>
                            <option value="other" <?= ($savedData['industry'] ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-input" rows="3"><?= e($savedData['address'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn-primary">Continue</button>
                    </div>
                </form>
                
            <?php elseif ($currentStep === 2): ?>
                <!-- Step 2: Work Rules -->
                <h2>Work Rules & Shifts</h2>
                <p class="step-description">Configure your shift patterns and work rules</p>
                
                <form method="post" class="onboarding-form">
                    <input type="hidden" name="step" value="2">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    
                    <div class="form-group">
                        <label class="form-label">Default Shift Duration (hours)</label>
                        <input type="number" name="default_shift_hours" class="form-input" value="<?= e($savedData['default_shift_hours'] ?? '8') ?>" min="4" max="12" step="0.5" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Work Days Per Week</label>
                        <select name="work_days_per_week" class="form-input" required>
                            <option value="5" <?= ($savedData['work_days_per_week'] ?? '5') === '5' ? 'selected' : '' ?>>5 days</option>
                            <option value="6" <?= ($savedData['work_days_per_week'] ?? '') === '6' ? 'selected' : '' ?>>6 days</option>
                            <option value="7" <?= ($savedData['work_days_per_week'] ?? '') === '7' ? 'selected' : '' ?>>7 days</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Break Duration (minutes)</label>
                        <input type="number" name="break_duration" class="form-input" value="<?= e($savedData['break_duration'] ?? '30') ?>" min="15" max="120" step="15" required>
                    </div>
                    
                    <div class="form-actions">
                        <a href="/onboarding.php?step=1&company_id=<?= $companyId ?>" class="btn secondary">Back</a>
                        <button type="submit" class="btn-primary">Continue</button>
                    </div>
                </form>
                
            <?php elseif ($currentStep === 3): ?>
                <!-- Step 3: Initial Employees -->
                <h2>Add Initial Employees</h2>
                <p class="step-description">Add your first team members (you can add more later)</p>
                
                <form method="post" class="onboarding-form" id="employees-form">
                    <input type="hidden" name="step" value="3">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    
                    <div id="employees-list">
                        <?php 
                        $employees = $savedData['employees'] ?? [['full_name' => '', 'email' => '', 'role' => 'Employee']];
                        foreach ($employees as $idx => $emp): 
                        ?>
                        <div class="employee-entry">
                            <div class="form-group">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="employees[<?= $idx ?>][full_name]" class="form-input" value="<?= e($emp['full_name'] ?? '') ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Email</label>
                                <input type="email" name="employees[<?= $idx ?>][email]" class="form-input" value="<?= e($emp['email'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Role</label>
                                <select name="employees[<?= $idx ?>][role]" class="form-input" required>
                                    <option value="Employee" <?= ($emp['role'] ?? 'Employee') === 'Employee' ? 'selected' : '' ?>>Employee</option>
                                    <option value="Senior" <?= ($emp['role'] ?? '') === 'Senior' ? 'selected' : '' ?>>Senior</option>
                                    <option value="Team Leader" <?= ($emp['role'] ?? '') === 'Team Leader' ? 'selected' : '' ?>>Team Leader</option>
                                </select>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <button type="button" class="btn secondary" id="add-employee">+ Add Another Employee</button>
                    
                    <div class="form-actions">
                        <a href="/onboarding.php?step=2&company_id=<?= $companyId ?>" class="btn secondary">Back</a>
                        <button type="submit" class="btn-primary">Continue</button>
                    </div>
                </form>
                
            <?php elseif ($currentStep === 4): ?>
                <!-- Step 4: Scheduling Preferences -->
                <h2>Scheduling Preferences</h2>
                <p class="step-description">Configure how schedules are generated</p>
                
                <form method="post" class="onboarding-form">
                    <input type="hidden" name="step" value="4">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    
                    <div class="form-group">
                        <label class="form-label">Auto-generate schedules?</label>
                        <select name="auto_generate" class="form-input" required>
                            <option value="yes" <?= ($savedData['auto_generate'] ?? 'yes') === 'yes' ? 'selected' : '' ?>>Yes, auto-generate weekly</option>
                            <option value="manual" <?= ($savedData['auto_generate'] ?? '') === 'manual' ? 'selected' : '' ?>>No, manual creation only</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Request submission window</label>
                        <select name="request_window" class="form-input" required>
                            <option value="current_week" <?= ($savedData['request_window'] ?? 'current_week') === 'current_week' ? 'selected' : '' ?>>Current week only</option>
                            <option value="next_week" <?= ($savedData['request_window'] ?? '') === 'next_week' ? 'selected' : '' ?>>Next week only</option>
                            <option value="both" <?= ($savedData['request_window'] ?? '') === 'both' ? 'selected' : '' ?>>Both current and next week</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Notification preferences</label>
                        <div class="checkbox-group">
                            <?php $notifications = $savedData['notifications'] ?? ['email', 'schedule_published', 'request_status']; ?>
                            <label><input type="checkbox" name="notifications[]" value="email" <?= in_array('email', $notifications) ? 'checked' : '' ?>> Email notifications</label>
                            <label><input type="checkbox" name="notifications[]" value="schedule_published" <?= in_array('schedule_published', $notifications) ? 'checked' : '' ?>> Schedule published</label>
                            <label><input type="checkbox" name="notifications[]" value="request_status" <?= in_array('request_status', $notifications) ? 'checked' : '' ?>> Request status updates</label>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <a href="/onboarding.php?step=3&company_id=<?= $companyId ?>" class="btn secondary">Back</a>
                        <button type="submit" class="btn-primary">Continue</button>
                    </div>
                </form>
                
            <?php elseif ($currentStep === 5): ?>
                <!-- Step 5: Review -->
                <h2>Review & Confirm</h2>
                <p class="step-description">Review your settings before proceeding</p>
                
                <div class="review-summary">
                    <?php foreach ($progress as $stepKey => $stepData): ?>
                        <?php if ($stepKey !== 'completed' && !empty($stepData['data'])): ?>
                            <div class="review-section">
                                <h3><?= ucfirst(str_replace('step_', 'Step ', $stepKey)) ?></h3>
                                <ul>
                                    <?php foreach ($stepData['data'] as $key => $value): ?>
                                        <?php if (is_array($value)): ?>
                                            <li><strong><?= e(ucwords(str_replace('_', ' ', $key))) ?>:</strong> <?= e(implode(', ', $value)) ?></li>
                                        <?php else: ?>
                                            <li><strong><?= e(ucwords(str_replace('_', ' ', $key))) ?>:</strong> <?= e($value) ?></li>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                
                <form method="post" class="onboarding-form">
                    <input type="hidden" name="step" value="5">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    
                    <div class="form-actions">
                        <a href="/onboarding.php?step=4&company_id=<?= $companyId ?>" class="btn secondary">Back</a>
                        <button type="submit" class="btn-primary">Complete Setup</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.onboarding-container {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: var(--space-xl);
}

.onboarding-card {
    background: var(--glass-bg);
    backdrop-filter: var(--glass-backdrop);
    border: 1px solid var(--glass-border);
    border-radius: var(--radius-2xl);
    padding: var(--space-2xl);
    max-width: 700px;
    width: 100%;
    box-shadow: var(--shadow-xl);
}

.onboarding-progress {
    margin-bottom: var(--space-xl);
}

.progress-bar {
    height: 8px;
    background: var(--glass-bg);
    border-radius: var(--radius-full);
    overflow: hidden;
    margin-bottom: var(--space-sm);
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--color-primary), var(--color-secondary));
    transition: width var(--transition-base);
}

.progress-text {
    text-align: center;
    color: var(--color-text-secondary);
    font-size: 0.875rem;
}

.onboarding-content h2 {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: var(--space-sm);
}

.step-description {
    color: var(--color-text-secondary);
    margin-bottom: var(--space-xl);
}

.onboarding-form {
    display: flex;
    flex-direction: column;
    gap: var(--space-lg);
}

.employee-entry {
    background: var(--glass-bg);
    border: 1px solid var(--glass-border);
    border-radius: var(--radius-lg);
    padding: var(--space-lg);
    margin-bottom: var(--space-md);
}

.review-summary {
    background: var(--glass-bg);
    border: 1px solid var(--glass-border);
    border-radius: var(--radius-lg);
    padding: var(--space-lg);
    margin-bottom: var(--space-xl);
}

.review-section {
    margin-bottom: var(--space-lg);
}

.review-section h3 {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: var(--space-sm);
}

.review-section ul {
    list-style: none;
    padding: 0;
}

.review-section li {
    padding: var(--space-xs) 0;
    color: var(--color-text-secondary);
}

.checkbox-group {
    display: flex;
    flex-direction: column;
    gap: var(--space-sm);
}

.checkbox-group label {
    display: flex;
    align-items: center;
    gap: var(--space-sm);
    cursor: pointer;
}
</style>

<script>
// Add employee entry
let employeeCount = <?= count($savedData['employees'] ?? [['']]) ?>;
document.getElementById('add-employee')?.addEventListener('click', function() {
    const list = document.getElementById('employees-list');
    const entry = document.createElement('div');
    entry.className = 'employee-entry';
    entry.innerHTML = `
        <div class="form-group">
            <label class="form-label">Full Name</label>
            <input type="text" name="employees[${employeeCount}][full_name]" class="form-input" required>
        </div>
        <div class="form-group">
            <label class="form-label">Email</label>
            <input type="email" name="employees[${employeeCount}][email]" class="form-input">
        </div>
        <div class="form-group">
            <label class="form-label">Role</label>
            <select name="employees[${employeeCount}][role]" class="form-input" required>
                <option value="Employee">Employee</option>
                <option value="Senior">Senior</option>
                <option value="Team Leader">Team Leader</option>
            </select>
        </div>
    `;
    list.appendChild(entry);
    employeeCount++;
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

