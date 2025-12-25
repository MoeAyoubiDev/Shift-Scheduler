<?php
declare(strict_types=1);

/**
 * Login Page
 * Handles both company admin login and employee login
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if already logged in
if (isset($_SESSION['user'])) {
    header('Location: /index.php');
    exit;
}

require_once __DIR__ . '/../app/Helpers/helpers.php';

$error = '';
$message = '';
if (isset($_GET['message'])) {
    $message = (string) $_GET['message'];
}

$title = 'Sign In - Shift Scheduler';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="login-container">
    <div class="login-card">
        <div class="login-brand">
            <div class="brand-logo">
                <svg width="40" height="40" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect width="48" height="48" rx="10" fill="#4f46e5"/>
                    <path d="M24 12L32 20H28V32H20V20H16L24 12Z" fill="white"/>
                </svg>
            </div>
            <h1 class="brand-title">Welcome Back</h1>
            <p class="brand-subtitle">Sign in to access your dashboard</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>

        <?php if ($message): ?>
            <div class="alert alert-error"><?= e($message) ?></div>
        <?php endif; ?>

        <form method="post" action="/index.php" id="login-form" class="login-form" novalidate>
            <input type="hidden" name="action" value="login">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

            <div class="form-group">
                <label for="username" class="form-label">Email or Username</label>
                <div class="input-container">
                    <input
                        type="text"
                        id="username"
                        name="username"
                        class="form-input"
                        required
                        autocomplete="username"
                        placeholder="you@company.com"
                    >
                </div>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <div class="input-container password-container">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-input"
                        required
                        autocomplete="current-password"
                        placeholder="Enter your password"
                    >
                    <button type="button" class="password-toggle" id="password-toggle" aria-label="Toggle password visibility">
                        <svg class="icon-eye" width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M10 3C5.5 3 1.73 5.61 0 9C1.73 12.39 5.5 15 10 15C14.5 15 18.27 12.39 20 9C18.27 5.61 14.5 3 10 3Z" fill="currentColor"/>
                        </svg>
                        <svg class="icon-eye-off" width="20" height="20" viewBox="0 0 20 20" fill="none" style="display: none;">
                            <path d="M2.5 2.5L17.5 17.5M8.16 8.16C7.84 8.5 7.65 8.96 7.65 9.45C7.65 10.43 8.42 11.2 9.4 11.2C9.89 11.2 10.35 11.01 10.69 10.69M14.84 14.84C13.94 15.54 12.78 16 11.5 16C7.91 16 4.81 13.92 2.5 10.5C3.46 8.64 4.9 7.2 6.66 6.34M12.41 4.41C13.5 4.78 14.52 5.32 15.43 6C18.09 8.08 21.19 10.16 24.5 10.5C23.54 12.36 22.1 13.8 20.34 14.66" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M10.59 6.59C11.37 6.22 12.15 6 12.5 6C15.09 6 17.19 8.1 17.19 10.69C17.19 11.04 16.97 11.82 16.6 12.6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary" id="submit-btn">
                    <span>Sign In</span>
                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                        <path d="M6.75 13.5L11.25 9L6.75 4.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>
        </form>

        <div class="form-footer">
            <div class="form-footer-content">
                <span class="form-footer-text">Don’t have an account?</span>
                <a href="/signup.php" class="btn btn-secondary">Sign Up</a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const passwordToggle = document.getElementById('password-toggle');
    const passwordInput = document.getElementById('password');
    const iconEye = passwordToggle?.querySelector('.icon-eye');
    const iconEyeOff = passwordToggle?.querySelector('.icon-eye-off');

    if (passwordToggle && passwordInput && iconEye && iconEyeOff) {
        passwordToggle.addEventListener('click', function(e) {
            e.preventDefault();
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            iconEye.style.display = type === 'text' ? 'none' : 'block';
            iconEyeOff.style.display = type === 'text' ? 'block' : 'none';
        });
    }

    const form = document.getElementById('login-form');
    const submitBtn = document.getElementById('submit-btn');
    if (form && submitBtn) {
        form.addEventListener('submit', function() {
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
        });
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
