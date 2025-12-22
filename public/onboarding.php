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
if (!$company || $company['status'] !== 'VERIFIED') {
    header('Location: /verify-email.php');
    exit;
}

$currentStep = (int)($_GET['step'] ?? 1);
$totalSteps = 5;
$progress = Company::getOnboardingProgress((int)$companyId);

// Handle step submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['step'])) {
    require_csrf($_POST);
    
    $step = (int)$_POST['step'];
    $stepData = $_POST;
    unset($stepData['step'], $stepData['csrf_token'], $stepData['action']);
    
    Company::updateOnboardingStep((int)$companyId, "step_{$step}", $stepData, true);
    
    if ($step < $totalSteps) {
        header('Location: /onboarding.php?step=' . ($step + 1) . '&company_id=' . $companyId);
        exit;
    } else {
        // Complete onboarding
        Company::updateOnboardingStep((int)$companyId, 'completed', [], true);
        // Update company status
        $pdo = db();
        $stmt = $pdo->prepare("UPDATE companies SET status = 'PAYMENT_PENDING', onboarding_completed_at = NOW() WHERE id = ?");
        $stmt->execute([$companyId]);
        
        header('Location: /onboarding-preview.php?company_id=' . $companyId);
        exit;
    }
}

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
            <?php if ($currentStep === 1): ?>
                <!-- Step 1: Company Details -->
                <h2>Company Details</h2>
                <p class="step-description">Tell us more about your company</p>
                
                <form method="post" class="onboarding-form">
                    <input type="hidden" name="step" value="1">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    
                    <div class="form-group">
                        <label class="form-label">Company Name</label>
                        <input type="text" name="company_name" class="form-input" value="<?= e($company['company_name']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Industry</label>
                        <select name="industry" class="form-input">
                            <option value="">Select industry</option>
                            <option value="retail">Retail</option>
                            <option value="healthcare">Healthcare</option>
                            <option value="hospitality">Hospitality</option>
                            <option value="manufacturing">Manufacturing</option>
                            <option value="services">Services</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-input" rows="3"></textarea>
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
                        <input type="number" name="default_shift_hours" class="form-input" value="8" min="4" max="12" step="0.5" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Work Days Per Week</label>
                        <select name="work_days_per_week" class="form-input" required>
                            <option value="5">5 days</option>
                            <option value="6">6 days</option>
                            <option value="7">7 days</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Break Duration (minutes)</label>
                        <input type="number" name="break_duration" class="form-input" value="30" min="15" max="120" step="15" required>
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
                        <div class="employee-entry">
                            <div class="form-group">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="employees[0][full_name]" class="form-input" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Email</label>
                                <input type="email" name="employees[0][email]" class="form-input">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Role</label>
                                <select name="employees[0][role]" class="form-input" required>
                                    <option value="Employee">Employee</option>
                                    <option value="Senior">Senior</option>
                                    <option value="Team Leader">Team Leader</option>
                                </select>
                            </div>
                        </div>
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
                            <option value="yes">Yes, auto-generate weekly</option>
                            <option value="manual">No, manual creation only</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Request submission window</label>
                        <select name="request_window" class="form-input" required>
                            <option value="current_week">Current week only</option>
                            <option value="next_week">Next week only</option>
                            <option value="both">Both current and next week</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Notification preferences</label>
                        <div class="checkbox-group">
                            <label><input type="checkbox" name="notifications[]" value="email" checked> Email notifications</label>
                            <label><input type="checkbox" name="notifications[]" value="schedule_published" checked> Schedule published</label>
                            <label><input type="checkbox" name="notifications[]" value="request_status" checked> Request status updates</label>
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
let employeeCount = 1;
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

