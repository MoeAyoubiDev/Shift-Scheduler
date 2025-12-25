<?php
declare(strict_types=1);

$title = 'Scheduling Preferences - Onboarding';
$step = 4;
$totalSteps = 5;

require_once __DIR__ . '/../../../includes/header.php';

$user = current_user();
$companyId = $user['company_id'] ?? null;
$company = Company::findById((int)$companyId);
$progress = Company::getOnboardingProgress((int)$companyId);
$stepData = $progress["step_4"]['data'] ?? [];

$error = $_GET['error'] ?? '';
?>

<link rel="stylesheet" href="/assets/css/onboarding.css">

<div class="onboarding-container">
    <div class="onboarding-card">
        <div class="onboarding-header">
            <h1 class="onboarding-title">Scheduling Preferences</h1>
            <p class="onboarding-subtitle">Configure your scheduling settings</p>
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

        <form method="post" action="/index.php">
            <input type="hidden" name="action" value="onboarding_step">
            <input type="hidden" name="step" value="4">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

            <div class="form-group">
                <label class="form-label" for="default_view">Default View</label>
                <select class="form-select" id="default_view" name="default_view" required>
                    <option value="weekly" <?= ($stepData['default_view'] ?? 'weekly') === 'weekly' ? 'selected' : '' ?>>Weekly</option>
                    <option value="bi-weekly" <?= ($stepData['default_view'] ?? '') === 'bi-weekly' ? 'selected' : '' ?>>Bi-weekly</option>
                    <option value="monthly" <?= ($stepData['default_view'] ?? '') === 'monthly' ? 'selected' : '' ?>>Monthly</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="week_start_day">Week Start Day</label>
                <select class="form-select" id="week_start_day" name="week_start_day" required>
                    <option value="monday" <?= ($stepData['week_start_day'] ?? 'monday') === 'monday' ? 'selected' : '' ?>>Monday</option>
                    <option value="sunday" <?= ($stepData['week_start_day'] ?? '') === 'sunday' ? 'selected' : '' ?>>Sunday</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Notifications</label>
                <div class="checkbox-group" style="margin-bottom: var(--space-3);">
                    <input type="checkbox" class="checkbox-input" id="email_notifications" name="email_notifications" 
                           value="1" <?= !empty($stepData['email_notifications']) ? 'checked' : 'checked' ?>>
                    <label for="email_notifications" style="color: var(--color-text-primary); cursor: pointer;">Email Notifications</label>
                </div>
                <div class="checkbox-group">
                    <input type="checkbox" class="checkbox-input" id="sms_notifications" name="sms_notifications" 
                           value="1" <?= !empty($stepData['sms_notifications']) ? 'checked' : '' ?>>
                    <label for="sms_notifications" style="color: var(--color-text-primary); cursor: pointer;">SMS Notifications</label>
                </div>
            </div>

            <div class="form-actions">
                <a href="/onboarding.php?step=3" class="btn-secondary">Back</a>
                <button type="submit" class="btn-primary">Continue</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../../includes/footer.php'; ?>
