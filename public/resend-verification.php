<?php
declare(strict_types=1);

/**
 * Resend Email Verification
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../app/Helpers/helpers.php';
require_once __DIR__ . '/../app/Models/Company.php';

$email = $_GET['email'] ?? '';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = trim($_POST['email']);
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $company = Company::findByEmail($email);
        if ($company && $company['status'] === 'PENDING_VERIFICATION') {
            // In production, send verification email here
            // For now, show the verification link
            $verificationUrl = '/verify-email.php?token=' . urlencode($company['verification_token']);
            $success = 'Verification email sent! Check your inbox.';
            // In development, show the link
            if (getenv('APP_ENV') === 'development') {
                $success .= ' <a href="' . $verificationUrl . '">Click here to verify</a>';
            }
        } else {
            $error = 'No pending verification found for this email.';
        }
    }
}

$title = 'Resend Verification - Shift Scheduler';
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
            <h1 class="brand-title">Resend Verification</h1>
            <p class="brand-subtitle">Get a new verification link</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <form method="post" class="login-form">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            
            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-input" value="<?= e($email) ?>" required autofocus>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">Resend Verification Email</button>
            </div>
        </form>

        <div class="form-footer">
            <p><a href="/login.php">Back to Sign In</a> | <a href="/signup.php">Sign Up</a></p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

