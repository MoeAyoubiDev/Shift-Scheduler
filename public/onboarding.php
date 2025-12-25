<?php
declare(strict_types=1);

/**
 * Company Onboarding Wizard
 * Multi-step guided setup process
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../app/Core/config.php';
require_once __DIR__ . '/../app/Helpers/helpers.php';
require_once __DIR__ . '/../app/Models/Company.php';

$user = current_user();
if (!$user) {
    header('Location: /signup.php');
    exit;
}

$companyId = $_SESSION['onboarding_company_id'] ?? ($user['company_id'] ?? null);

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
if ($company['status'] === 'ACTIVE' || !empty($user['onboarding_completed'])) {
    header('Location: /dashboard');
    exit;
}

if (!in_array($company['status'], ['VERIFIED', 'ONBOARDING', 'PAYMENT_PENDING'], true)) {
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
        header('Location: /onboarding/step-' . $requiredStep);
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
        try {
            if ($step === 1) {
                $updatedCompanyName = trim((string) ($stepData['company_name'] ?? ''));
                if ($updatedCompanyName !== '' && $updatedCompanyName !== $company['company_name']) {
                    $pdo = db();
                    $updateStmt = $pdo->prepare("UPDATE companies SET company_name = ? WHERE id = ?");
                    $updateStmt->execute([$updatedCompanyName, $companyId]);
                    $company['company_name'] = $updatedCompanyName;
                }
            }

            $statusStmt = db()->prepare("UPDATE companies SET status = 'ONBOARDING' WHERE id = ? AND status != 'ACTIVE'");
            $statusStmt->execute([$companyId]);

            Company::updateOnboardingStep((int)$companyId, "step_{$step}", $stepData, true);
            
            if ($step < $totalSteps) {
                header('Location: /onboarding/step-' . ($step + 1));
                exit;
            } else {
                // Complete onboarding and create initial data
                Company::updateOnboardingStep((int)$companyId, 'completed', [], true);

                require_once __DIR__ . '/../app/Models/User.php';

                $pdo = db();
                $pdo->beginTransaction();

                try {
                    $sectionName = $progress['step_1']['data']['company_name'] ?? $company['company_name'];
                    $sectionNameFull = $sectionName . ' - Main';

                    $checkSectionStmt = $pdo->prepare("SELECT id FROM sections WHERE section_name = ? AND company_id = ? LIMIT 1");
                    $checkSectionStmt->execute([$sectionNameFull, $companyId]);
                    $existingSection = $checkSectionStmt->fetch(PDO::FETCH_ASSOC);

                    if ($existingSection) {
                        $sectionId = (int) $existingSection['id'];
                    } else {
                        $sectionStmt = $pdo->prepare("INSERT INTO sections (section_name, company_id) VALUES (?, ?)");
                        $sectionStmt->execute([$sectionNameFull, $companyId]);
                        $sectionId = (int) $pdo->lastInsertId();
                    }

                    if ($sectionId <= 0) {
                        throw new RuntimeException('Failed to create section.');
                    }

                    $roleStmt = $pdo->prepare("SELECT id FROM roles WHERE role_name = 'Supervisor' LIMIT 1");
                    $roleStmt->execute();
                    $roleRow = $roleStmt->fetch(PDO::FETCH_ASSOC);
                    $supervisorRoleId = (int) ($roleRow['id'] ?? 0);
                    if ($supervisorRoleId === 0) {
                        throw new RuntimeException(
                            'Supervisor role not found. Database seed data is missing. Please run: mysql < database/shift_scheduler.sql'
                        );
                    }

                    $userId = (int) ($_SESSION['user_id'] ?? $user['id'] ?? 0);
                    if ($userId <= 0) {
                        throw new RuntimeException('Unable to identify admin user.');
                    }

                    $adminRoleStmt = $pdo->prepare("SELECT id, section_id FROM user_roles WHERE user_id = ? AND role_id = ? LIMIT 1");
                    $adminRoleStmt->execute([$userId, $supervisorRoleId]);
                    $adminRoleRow = $adminRoleStmt->fetch(PDO::FETCH_ASSOC);

                    if ($adminRoleRow) {
                        $existingRoleSection = (int) $adminRoleRow['section_id'];
                        if ($existingRoleSection !== $sectionId) {
                            $updateRoleStmt = $pdo->prepare("UPDATE user_roles SET section_id = ? WHERE id = ?");
                            $updateRoleStmt->execute([$sectionId, (int) $adminRoleRow['id']]);
                        }
                    } else {
                        $userRoleStmt = $pdo->prepare("INSERT INTO user_roles (user_id, role_id, section_id) VALUES (?, ?, ?)");
                        $userRoleStmt->execute([$userId, $supervisorRoleId, $sectionId]);
                    }

                    $updateAdminStmt = $pdo->prepare("UPDATE users SET company_id = ?, role = 'Supervisor', onboarding_completed = 1 WHERE id = ?");
                    $updateAdminStmt->execute([$companyId, $userId]);

                    $employeesData = $progress['step_3']['data']['employees'] ?? [];
                    $empRoleMap = ['Employee' => 'Employee', 'Team Leader' => 'Team Leader'];

                    foreach ($employeesData as $idx => $emp) {
                        if (empty($emp['full_name'])) {
                            continue;
                        }

                        $empRoleName = $empRoleMap[$emp['role']] ?? 'Employee';
                        $empRoleStmt = $pdo->prepare("SELECT id FROM roles WHERE role_name = ? LIMIT 1");
                        $empRoleStmt->execute([$empRoleName]);
                        $empRoleRow = $empRoleStmt->fetch(PDO::FETCH_ASSOC);
                        $empRoleId = (int) ($empRoleRow['id'] ?? 0);

                        if ($empRoleId === 0) {
                            throw new RuntimeException('Role not found for employee: ' . $empRoleName);
                        }

                        $email = trim((string) ($emp['email'] ?? ''));
                        $existingUserId = 0;
                        if ($email !== '') {
                            $findByEmailStmt = $pdo->prepare("SELECT id, company_id FROM users WHERE email = ? LIMIT 1");
                            $findByEmailStmt->execute([$email]);
                            $existingEmailUser = $findByEmailStmt->fetch(PDO::FETCH_ASSOC);
                            if ($existingEmailUser) {
                                $existingUserCompany = $existingEmailUser['company_id'] ?? null;
                                if ($existingUserCompany !== null && (int) $existingUserCompany !== (int) $companyId) {
                                    throw new RuntimeException('Employee email already belongs to another company.');
                                }
                                $existingUserId = (int) $existingEmailUser['id'];
                            }
                        }

                        if ($existingUserId === 0) {
                            $usernameBase = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '', $emp['full_name']));
                            $usernameBase = $usernameBase !== '' ? $usernameBase : 'employee';
                            $usernameCandidate = $usernameBase;
                            $suffix = 1;
                            while (true) {
                                $checkUsernameStmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND company_id <=> ? LIMIT 1");
                                $checkUsernameStmt->execute([$usernameCandidate, $companyId]);
                                if (!$checkUsernameStmt->fetch(PDO::FETCH_ASSOC)) {
                                    break;
                                }
                                $usernameCandidate = $usernameBase . $suffix;
                                $suffix++;
                            }

                            $userInsertStmt = $pdo->prepare("
                                INSERT INTO users (company_id, username, password_hash, email, role, onboarding_completed)
                                VALUES (?, ?, NULL, ?, ?, 1)
                            ");
                            $userInsertStmt->execute([$companyId, $usernameCandidate, $email !== '' ? $email : null, $empRoleName]);
                            $existingUserId = (int) $pdo->lastInsertId();
                        } else {
                            $updateUserRoleStmt = $pdo->prepare("UPDATE users SET role = COALESCE(role, ?) WHERE id = ?");
                            $updateUserRoleStmt->execute([$empRoleName, $existingUserId]);
                        }

                        if ($existingUserId <= 0) {
                            throw new RuntimeException('Failed to create employee user.');
                        }

                        $empUserRoleStmt = $pdo->prepare("SELECT id FROM user_roles WHERE user_id = ? AND role_id = ? AND section_id = ? LIMIT 1");
                        $empUserRoleStmt->execute([$existingUserId, $empRoleId, $sectionId]);
                        $empUserRoleRow = $empUserRoleStmt->fetch(PDO::FETCH_ASSOC);

                        if ($empUserRoleRow) {
                            $empUserRoleId = (int) $empUserRoleRow['id'];
                        } else {
                            $insertUserRoleStmt = $pdo->prepare("INSERT INTO user_roles (user_id, role_id, section_id) VALUES (?, ?, ?)");
                            $insertUserRoleStmt->execute([$existingUserId, $empRoleId, $sectionId]);
                            $empUserRoleId = (int) $pdo->lastInsertId();
                        }

                        if (in_array($empRoleName, ['Employee'], true) && $empUserRoleId > 0) {
                            $checkEmployeeStmt = $pdo->prepare("SELECT id FROM employees WHERE user_role_id = ? LIMIT 1");
                            $checkEmployeeStmt->execute([$empUserRoleId]);
                            $existingEmployee = $checkEmployeeStmt->fetch(PDO::FETCH_ASSOC);

                            if (!$existingEmployee) {
                                $empCode = 'EMP' . str_pad((string) ($idx + 1), 3, '0', STR_PAD_LEFT);
                                $codeCheck = $pdo->prepare("SELECT id FROM employees WHERE employee_code = ? LIMIT 1");
                                while (true) {
                                    $codeCheck->execute([$empCode]);
                                    if (!$codeCheck->fetch(PDO::FETCH_ASSOC)) {
                                        break;
                                    }
                                    $empCode = 'EMP' . str_pad((string) random_int(0, 999), 3, '0', STR_PAD_LEFT);
                                }

                                $employeeStmt = $pdo->prepare("
                                    INSERT INTO employees (user_role_id, employee_code, full_name, email, is_senior, seniority_level)
                                    VALUES (?, ?, ?, ?, ?, ?)
                                ");
                                $employeeStmt->execute([
                                    $empUserRoleId,
                                    $empCode,
                                    $emp['full_name'],
                                    $email !== '' ? $email : null,
                                    0,
                                    0,
                                ]);
                            }
                        }
                    }

                    $companyUpdateStmt = $pdo->prepare("
                        UPDATE companies
                        SET status = 'ACTIVE',
                            onboarding_completed_at = NOW()
                        WHERE id = ?
                    ");
                    $companyUpdateStmt->execute([$companyId]);

                    $pdo->commit();

                    $freshUser = User::findByEmail($user['email']);
                    if ($freshUser) {
                        $_SESSION['user'] = $freshUser;
                        $_SESSION['user_id'] = $freshUser['id'];
                        $_SESSION['role'] = $freshUser['role'];
                        $_SESSION['company_id'] = $freshUser['company_id'];
                    }
                    $_SESSION['selected_section_id'] = $sectionId;
                    unset($_SESSION['onboarding_company_id']);

                    header('Location: /index.php?message=' . urlencode('Your account has been created successfully.'));
                    exit;
                } catch (Exception $e) {
                    $pdo->rollBack();
                    error_log("Onboarding completion error: " . $e->getMessage());
                    $error = 'Failed to complete setup: ' . $e->getMessage();
                }
            }
        } catch (Exception $e) {
            error_log("Onboarding step error: " . $e->getMessage());
            $error = 'An error occurred: ' . $e->getMessage();
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
                        <a href="/onboarding/step-1" class="btn secondary">Back</a>
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
                                    <option value="Team Leader" <?= ($emp['role'] ?? '') === 'Team Leader' ? 'selected' : '' ?>>Team Leader</option>
                                </select>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <button type="button" class="btn secondary" id="add-employee">+ Add Another Employee</button>
                    
                    <div class="form-actions">
                        <a href="/onboarding/step-2" class="btn secondary">Back</a>
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
                        <a href="/onboarding/step-3" class="btn secondary">Back</a>
                        <button type="submit" class="btn-primary">Continue</button>
                    </div>
                </form>
                
            <?php elseif ($currentStep === 5): ?>
                <!-- Step 5: Review -->
                <h2>Review & Confirm</h2>
                <p class="step-description">Review the details below before completing your setup.</p>

                <?php
                $step1 = $progress['step_1']['data'] ?? [];
                $step2 = $progress['step_2']['data'] ?? [];
                $step3 = $progress['step_3']['data'] ?? [];
                $step4 = $progress['step_4']['data'] ?? [];

                $industryLabel = $step1['industry'] ?? '';
                $industryLabel = $industryLabel !== '' ? ucwords(str_replace('_', ' ', $industryLabel)) : 'Not specified';
                $addressLabel = trim((string) ($step1['address'] ?? ''));
                $addressLabel = $addressLabel !== '' ? $addressLabel : 'Not provided';

                $workDays = $step2['work_days_per_week'] ?? '';
                $workDaysLabel = $workDays !== '' ? $workDays . ' days' : 'Not specified';

                $autoGenerate = ($step4['auto_generate'] ?? '') === 'yes' ? 'Enabled' : 'Manual scheduling';
                $requestWindow = $step4['request_window'] ?? '';
                $requestWindowMap = [
                    'current_week' => 'Current week only',
                    'next_week' => 'Next week only',
                    'both' => 'Current and next week',
                ];
                $requestWindowLabel = $requestWindowMap[$requestWindow] ?? 'Not specified';
                $notificationMap = [
                    'email' => 'Email notifications',
                    'schedule_published' => 'Schedule published',
                    'request_status' => 'Request status updates',
                ];
                $notifications = array_filter($step4['notifications'] ?? []);
                ?>

                <div class="review-summary">
                    <div class="review-section">
                        <h3>Company Details</h3>
                        <div class="review-grid">
                            <div class="review-item">
                                <span class="review-label">Company Name</span>
                                <span class="review-value"><?= e($step1['company_name'] ?? $company['company_name']) ?></span>
                            </div>
                            <div class="review-item">
                                <span class="review-label">Industry</span>
                                <span class="review-value"><?= e($industryLabel) ?></span>
                            </div>
                            <div class="review-item review-item-full">
                                <span class="review-label">Address</span>
                                <span class="review-value"><?= e($addressLabel) ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="review-section">
                        <h3>Work Rules &amp; Shifts</h3>
                        <div class="review-grid">
                            <div class="review-item">
                                <span class="review-label">Default Shift Duration</span>
                                <span class="review-value"><?= e((string) ($step2['default_shift_hours'] ?? '8')) ?> hours</span>
                            </div>
                            <div class="review-item">
                                <span class="review-label">Work Days Per Week</span>
                                <span class="review-value"><?= e($workDaysLabel) ?></span>
                            </div>
                            <div class="review-item">
                                <span class="review-label">Break Duration</span>
                                <span class="review-value"><?= e((string) ($step2['break_duration'] ?? '30')) ?> minutes</span>
                            </div>
                        </div>
                    </div>

                    <div class="review-section">
                        <h3>Initial Team</h3>
                        <div class="review-list">
                            <?php $employees = $step3['employees'] ?? []; ?>
                            <?php if (empty($employees)): ?>
                                <p class="review-empty">No employees added yet.</p>
                            <?php else: ?>
                                <?php foreach ($employees as $emp): ?>
                                    <?php if (empty($emp['full_name'])): ?>
                                        <?php continue; ?>
                                    <?php endif; ?>
                                    <div class="review-employee">
                                        <div>
                                            <div class="review-employee-name"><?= e($emp['full_name']) ?></div>
                                            <div class="review-employee-meta">
                                                <?= e($emp['email'] ?? 'No email provided') ?>
                                            </div>
                                        </div>
                                        <span class="review-pill"><?= e($emp['role'] ?? 'Employee') ?></span>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="review-section">
                        <h3>Scheduling Preferences</h3>
                        <div class="review-grid">
                            <div class="review-item">
                                <span class="review-label">Auto-generate schedules</span>
                                <span class="review-value"><?= e($autoGenerate) ?></span>
                            </div>
                            <div class="review-item">
                                <span class="review-label">Request submission window</span>
                                <span class="review-value"><?= e($requestWindowLabel) ?></span>
                            </div>
                            <div class="review-item review-item-full">
                                <span class="review-label">Notification preferences</span>
                                <?php if (!empty($notifications)): ?>
                                    <div class="review-pill-group">
                                        <?php foreach ($notifications as $notification): ?>
                                            <span class="review-pill"><?= e($notificationMap[$notification] ?? $notification) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="review-value">No notifications selected</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <form method="post" class="onboarding-form">
                    <input type="hidden" name="step" value="5">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    
                    <div class="form-actions">
                        <a href="/onboarding/step-4" class="btn secondary">Back</a>
                        <button type="submit" class="btn-primary">Complete Setup</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- All onboarding styles are now in app.css -->

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
                <option value="Team Leader">Team Leader</option>
            </select>
        </div>
    `;
    list.appendChild(entry);
    employeeCount++;
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
