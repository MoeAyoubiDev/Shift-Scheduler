<?php
declare(strict_types=1);

/**
 * Onboarding Wizard Router
 * Routes to appropriate step page
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

$companyId = $user['company_id'] ?? null;
if (!$companyId) {
    header('Location: /signup.php');
    exit;
}

$company = Company::findById((int)$companyId);
if (!$company) {
    header('Location: /signup.php');
    exit;
}

// Redirect to dashboard if already completed
if ($company['status'] === 'ACTIVE' || !empty($user['onboarding_completed'])) {
    header('Location: /dashboard');
    exit;
}

// Get current step from URL
$step = (int)($_GET['step'] ?? 1);
if ($step < 1 || $step > 5) {
    $step = 1;
}

// Get progress to prevent skipping steps
$progress = Company::getOnboardingProgress((int)$companyId);

// Prevent skipping steps
if ($step > 1) {
    $prevStepKey = "step_" . ($step - 1);
    if (empty($progress[$prevStepKey]) || !$progress[$prevStepKey]['completed']) {
        header('Location: /onboarding.php?step=' . ($step - 1));
        exit;
    }
}

// Route to step page
$stepFile = __DIR__ . "/onboarding/step-{$step}/index.php";
if (file_exists($stepFile)) {
    require $stepFile;
} else {
    die("Onboarding step {$step} not found.");
}
