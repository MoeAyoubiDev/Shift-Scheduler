<?php
declare(strict_types=1);

$title = 'Company Details - Onboarding';
$step = 1;
$totalSteps = 5;

require_once __DIR__ . '/../../../includes/header.php';

$user = current_user();
$companyId = $user['company_id'] ?? null;
$company = Company::findById((int)$companyId);
$progress = Company::getOnboardingProgress((int)$companyId);
$stepData = $progress["step_1"]['data'] ?? [];

$error = $_GET['error'] ?? '';
?>


<div class="onboarding-container">
    <div class="onboarding-card">
        <div class="onboarding-header">
            <h1 class="onboarding-title">Company Details</h1>
            <p class="onboarding-subtitle">Tell us about your company</p>
        </div>

        <div class="progress-bar">
            <?php for ($i = 1; $i <= $totalSteps; $i++): ?>
                <div class="progress-step <?= $i === $step ? 'active' : ($i < $step ? 'completed' : '') ?>">
                    <div class="progress-step-circle"><?= $i ?></div>
                    <span style="font-size: 0.75rem; color: var(--color-text-secondary);">Step <?= $i ?></span>
                </div>
            <?php endfor; ?>
        </div>

        <?php if ($error): ?>
            <div class="alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" action="/index.php">
            <input type="hidden" name="action" value="onboarding_step">
            <input type="hidden" name="step" value="1">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

            <div class="form-group">
                <label class="form-label" for="industry">Industry</label>
                <select class="form-select" id="industry" name="industry" required>
                    <option value="">Select industry</option>
                    <option value="Healthcare" <?= ($stepData['industry'] ?? '') === 'Healthcare' ? 'selected' : '' ?>>Healthcare</option>
                    <option value="Retail" <?= ($stepData['industry'] ?? '') === 'Retail' ? 'selected' : '' ?>>Retail</option>
                    <option value="Hospitality" <?= ($stepData['industry'] ?? '') === 'Hospitality' ? 'selected' : '' ?>>Hospitality</option>
                    <option value="Manufacturing" <?= ($stepData['industry'] ?? '') === 'Manufacturing' ? 'selected' : '' ?>>Manufacturing</option>
                    <option value="Technology" <?= ($stepData['industry'] ?? '') === 'Technology' ? 'selected' : '' ?>>Technology</option>
                    <option value="Other" <?= ($stepData['industry'] ?? '') === 'Other' ? 'selected' : '' ?>>Other</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="company_size">Company Size</label>
                <select class="form-select" id="company_size" name="company_size" required>
                    <option value="">Select size</option>
                    <option value="1-10" <?= ($stepData['company_size'] ?? '') === '1-10' ? 'selected' : '' ?>>1-10 employees</option>
                    <option value="11-50" <?= ($stepData['company_size'] ?? '') === '11-50' ? 'selected' : '' ?>>11-50 employees</option>
                    <option value="51-200" <?= ($stepData['company_size'] ?? '') === '51-200' ? 'selected' : '' ?>>51-200 employees</option>
                    <option value="201-500" <?= ($stepData['company_size'] ?? '') === '201-500' ? 'selected' : '' ?>>201-500 employees</option>
                    <option value="501+" <?= ($stepData['company_size'] ?? '') === '501+' ? 'selected' : '' ?>>501+ employees</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="timezone">Time Zone</label>
                <select class="form-select" id="timezone" name="timezone" required>
                    <option value="">Select time zone</option>
                    <?php
                    $timezones = [
                        'America/New_York' => 'Eastern Time (ET)',
                        'America/Chicago' => 'Central Time (CT)',
                        'America/Denver' => 'Mountain Time (MT)',
                        'America/Los_Angeles' => 'Pacific Time (PT)',
                        'America/Phoenix' => 'Arizona (MST)',
                        'America/Anchorage' => 'Alaska Time (AKT)',
                        'Pacific/Honolulu' => 'Hawaii Time (HST)',
                        'UTC' => 'UTC',
                    ];
                    foreach ($timezones as $tz => $label):
                        $selected = ($stepData['timezone'] ?? $company['timezone'] ?? '') === $tz ? 'selected' : '';
                    ?>
                        <option value="<?= $tz ?>" <?= $selected ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="address">Address</label>
                <input type="text" class="form-input" id="address" name="address" 
                       value="<?= htmlspecialchars($stepData['address'] ?? '') ?>" 
                       placeholder="Enter company address">
            </div>

            <div class="form-group">
                <label class="form-label" for="contact_email">Contact Email</label>
                <input type="email" class="form-input" id="contact_email" name="contact_email" 
                       value="<?= htmlspecialchars($stepData['contact_email'] ?? $company['admin_email'] ?? '') ?>" 
                       placeholder="contact@company.com">
            </div>

            <div class="form-group">
                <label class="form-label" for="phone">Phone Number</label>
                <input type="tel" class="form-input" id="phone" name="phone" 
                       value="<?= htmlspecialchars($stepData['phone'] ?? '') ?>" 
                       placeholder="+1 (555) 123-4567">
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">Continue</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../../includes/footer.php'; ?>
