<?php
declare(strict_types=1);

$title = 'Work Rules - Onboarding';
$step = 2;
$totalSteps = 5;

require_once __DIR__ . '/../../../includes/header.php';

$user = current_user();
$companyId = $user['company_id'] ?? null;
$company = Company::findById((int)$companyId);
$progress = Company::getOnboardingProgress((int)$companyId);
$stepData = $progress["step_2"]['data'] ?? [];

$error = $_GET['error'] ?? '';
?>

<link rel="stylesheet" href="/assets/css/onboarding.css">

<div class="onboarding-container">
    <div class="onboarding-card">
        <div class="onboarding-header">
            <h1 class="onboarding-title">Work Rules</h1>
            <p class="onboarding-subtitle">Define your shift and work policies</p>
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
            <input type="hidden" name="step" value="2">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

            <div class="form-group">
                <label class="form-label" for="shift_duration">Shift Duration (hours)</label>
                <input type="number" class="form-input" id="shift_duration" name="shift_duration" 
                       value="<?= htmlspecialchars($stepData['shift_duration'] ?? '8') ?>" 
                       min="1" max="24" step="0.5" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="max_consecutive_days">Max Consecutive Days</label>
                <input type="number" class="form-input" id="max_consecutive_days" name="max_consecutive_days" 
                       value="<?= htmlspecialchars($stepData['max_consecutive_days'] ?? '5') ?>" 
                       min="1" max="14" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="min_rest_hours">Min Rest Hours</label>
                <input type="number" class="form-input" id="min_rest_hours" name="min_rest_hours" 
                       value="<?= htmlspecialchars($stepData['min_rest_hours'] ?? '11') ?>" 
                       min="0" max="24" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="overtime_threshold">Overtime Threshold (hours)</label>
                <input type="number" class="form-input" id="overtime_threshold" name="overtime_threshold" 
                       value="<?= htmlspecialchars($stepData['overtime_threshold'] ?? '40') ?>" 
                       min="1" step="0.5" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="overtime_rules">Overtime Rules</label>
                <textarea class="form-textarea" id="overtime_rules" name="overtime_rules" 
                          placeholder="Enter overtime rules and policies"><?= htmlspecialchars($stepData['overtime_rules'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label class="form-label" for="work_days_per_week">Work Days Per Week</label>
                <select class="form-select" id="work_days_per_week" name="work_days_per_week" required>
                    <option value="5" <?= ($stepData['work_days_per_week'] ?? '5') === '5' ? 'selected' : '' ?>>5 days</option>
                    <option value="6" <?= ($stepData['work_days_per_week'] ?? '') === '6' ? 'selected' : '' ?>>6 days</option>
                    <option value="7" <?= ($stepData['work_days_per_week'] ?? '') === '7' ? 'selected' : '' ?>>7 days</option>
                </select>
            </div>

            <div class="form-actions">
                <a href="/onboarding.php?step=1" class="btn-secondary">Back</a>
                <button type="submit" class="btn-primary">Continue</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../../includes/footer.php'; ?>
