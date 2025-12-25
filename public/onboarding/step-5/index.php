<?php
declare(strict_types=1);

$title = 'Review & Confirm - Onboarding';
$step = 5;
$totalSteps = 5;

require_once __DIR__ . '/../../../includes/header.php';

$user = current_user();
$companyId = $user['company_id'] ?? null;
$company = Company::findById((int)$companyId);
$progress = Company::getOnboardingProgress((int)$companyId);

$step1Data = $progress["step_1"]['data'] ?? [];
$step2Data = $progress["step_2"]['data'] ?? [];
$step3Data = $progress["step_3"]['data'] ?? [];
$step4Data = $progress["step_4"]['data'] ?? [];

$error = $_GET['error'] ?? '';
?>

<link rel="stylesheet" href="/assets/css/onboarding.css">

<div class="onboarding-container">
    <div class="onboarding-card">
        <div class="onboarding-header">
            <h1 class="onboarding-title">Review & Confirm</h1>
            <p class="onboarding-subtitle">Review your information before completing setup</p>
        </div>

        <div class="progress-bar">
            <?php for ($i = 1; $i <= $totalSteps; $i++): ?>
                <div class="progress-step <?= $i === $step ? 'active' : ($i < $step ? 'completed' : 'completed') ?>">
                    <div class="progress-step-circle"><?= $i ?></div>
                    <span class="progress-step-label">Step <?= $i ?></span>
                </div>
            <?php endfor; ?>
        </div>

        <?php if ($error): ?>
            <div class="alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="summary-section">
            <h3 class="summary-section-title">Company Details</h3>
            <div class="summary-item">
                <span class="summary-label">Industry:</span>
                <span class="summary-value"><?= htmlspecialchars($step1Data['industry'] ?? 'Not set') ?></span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Company Size:</span>
                <span class="summary-value"><?= htmlspecialchars($step1Data['company_size'] ?? 'Not set') ?></span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Time Zone:</span>
                <span class="summary-value"><?= htmlspecialchars($step1Data['timezone'] ?? 'Not set') ?></span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Address:</span>
                <span class="summary-value"><?= htmlspecialchars($step1Data['address'] ?? 'Not set') ?></span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Contact Email:</span>
                <span class="summary-value"><?= htmlspecialchars($step1Data['contact_email'] ?? 'Not set') ?></span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Phone:</span>
                <span class="summary-value"><?= htmlspecialchars($step1Data['phone'] ?? 'Not set') ?></span>
            </div>
        </div>

        <div class="summary-section">
            <h3 class="summary-section-title">Work Rules</h3>
            <div class="summary-item">
                <span class="summary-label">Shift Duration:</span>
                <span class="summary-value"><?= htmlspecialchars($step2Data['shift_duration'] ?? 'Not set') ?> hours</span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Max Consecutive Days:</span>
                <span class="summary-value"><?= htmlspecialchars($step2Data['max_consecutive_days'] ?? 'Not set') ?></span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Min Rest Hours:</span>
                <span class="summary-value"><?= htmlspecialchars($step2Data['min_rest_hours'] ?? 'Not set') ?></span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Overtime Threshold:</span>
                <span class="summary-value"><?= htmlspecialchars($step2Data['overtime_threshold'] ?? 'Not set') ?> hours</span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Work Days Per Week:</span>
                <span class="summary-value"><?= htmlspecialchars($step2Data['work_days_per_week'] ?? 'Not set') ?></span>
            </div>
        </div>

        <div class="summary-section">
            <h3 class="summary-section-title">Employees</h3>
            <?php $employees = $step3Data['employees'] ?? []; ?>
            <?php if (empty($employees)): ?>
                <div class="summary-item">
                    <span class="summary-value">No employees added yet</span>
                </div>
            <?php else: ?>
                <div class="summary-item">
                    <span class="summary-label">Total Employees:</span>
                    <span class="summary-value"><?= count($employees) ?></span>
                </div>
            <?php endif; ?>
        </div>

        <div class="summary-section">
            <h3 class="summary-section-title">Scheduling Preferences</h3>
            <div class="summary-item">
                <span class="summary-label">Default View:</span>
                <span class="summary-value"><?= htmlspecialchars(ucfirst($step4Data['default_view'] ?? 'Not set')) ?></span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Week Start Day:</span>
                <span class="summary-value"><?= htmlspecialchars(ucfirst($step4Data['week_start_day'] ?? 'Not set')) ?></span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Email Notifications:</span>
                <span class="summary-value"><?= !empty($step4Data['email_notifications']) ? 'Enabled' : 'Disabled' ?></span>
            </div>
            <div class="summary-item">
                <span class="summary-label">SMS Notifications:</span>
                <span class="summary-value"><?= !empty($step4Data['sms_notifications']) ? 'Enabled' : 'Disabled' ?></span>
            </div>
        </div>

        <form method="post" action="/index.php" style="margin-top: var(--space-8);">
            <input type="hidden" name="action" value="complete_onboarding">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

            <div class="form-actions">
                <a href="/onboarding.php?step=4" class="btn-secondary">Back</a>
                <button type="submit" class="btn-primary">Confirm & Proceed to Payment</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../../includes/footer.php'; ?>
