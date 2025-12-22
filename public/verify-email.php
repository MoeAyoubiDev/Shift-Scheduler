<?php
declare(strict_types=1);

/**
 * Email Verification Page
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../app/Helpers/helpers.php';
require_once __DIR__ . '/../app/Models/Company.php';

$error = '';
$success = '';
$email = $_GET['email'] ?? $_SESSION['signup_email'] ?? '';

// Handle verification token
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    if (Company::verifyEmail($token)) {
        $success = 'Email verified successfully! Redirecting to onboarding...';
        // Get company ID from session or token
        $companyId = $_SESSION['signup_company_id'] ?? null;
        if ($companyId) {
            $_SESSION['onboarding_company_id'] = $companyId;
            header('Location: /onboarding.php?step=1');
            exit;
        }
    } else {
        $error = 'Invalid or expired verification token.';
    }
}

$title = 'Verify Email - Shift Scheduler';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="login-container">
    <div class="login-card">
        <div class="login-brand">
            <div class="brand-logo">
                <svg width="40" height="40" viewBox="0 0 48 48" fill="none">
                    <rect width="48" height="48" rx="10" fill="#4f46e5"/>
                    <path d="M24 12L32 20H28V32H20V20H16L24 12Z" fill="white"/>
                </svg>
            </div>
            <h1 class="brand-title">Verify Your Email</h1>
            <p class="brand-subtitle">Check your inbox for verification link</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= e($success) ?></div>
        <?php endif; ?>

        <?php if ($email && !isset($_GET['token'])): ?>
            <div class="verification-info">
                <p>We've sent a verification email to:</p>
                <p class="email-display"><strong><?= e($email) ?></strong></p>
                <p>Please click the link in the email to verify your account.</p>
                <p class="text-muted">Didn't receive the email? Check your spam folder or <a href="/resend-verification.php?email=<?= urlencode($email) ?>">resend</a>.</p>
            </div>
        <?php endif; ?>

        <div class="form-footer">
            <p><a href="/login.php">Back to Sign In</a></p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

