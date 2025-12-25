<?php
declare(strict_types=1);

/**
 * Company Sign-Up Page
 * First step in the onboarding process
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if already logged in
if (isset($_SESSION['user'])) {
    header('Location: /dashboard');
    exit;
}

try {
    require_once __DIR__ . '/../app/Core/config.php';
    require_once __DIR__ . '/../app/Helpers/view.php';
    require_once __DIR__ . '/../app/Helpers/helpers.php';
    require_once __DIR__ . '/../app/Models/BaseModel.php';
    require_once __DIR__ . '/../app/Models/Company.php';
    
    // Check if companies table exists
    try {
        $pdo = db();
        $pdo->query("SELECT 1 FROM companies LIMIT 1");
        $dbError = false;
    } catch (PDOException $e) {
        $dbError = true;
    }
} catch (Throwable $e) {
    error_log("Signup page error: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    $dbError = true;
    $error = 'Application initialization error. Please check server logs.';
}

// Initialize variables
if (!isset($error)) $error = '';
if (!isset($dbError)) $dbError = false;
$message = isset($_GET['message']) ? (string) $_GET['message'] : '';

$title = 'Create Account - Shift Scheduler';

// Ensure all required functions are available
if (!function_exists('e')) {
    require_once __DIR__ . '/../app/Helpers/view.php';
}
if (!function_exists('app_config')) {
    require_once __DIR__ . '/../app/Core/config.php';
}
if (!function_exists('current_user')) {
    require_once __DIR__ . '/../app/Helpers/helpers.php';
}
if (!function_exists('csrf_token')) {
    require_once __DIR__ . '/../app/Helpers/helpers.php';
}

try {
    require_once __DIR__ . '/../includes/header.php';
} catch (Throwable $e) {
    error_log("Header error: " . $e->getMessage());
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= htmlspecialchars($title) ?></title>
        <link rel="stylesheet" href="/assets/css/app.css">
    </head>
    <body class="page-shell login-page">
    <main>
    <?php
}
?>

<div class="login-container">
    <div class="login-card signup-card">
        <div class="login-brand">
            <div class="brand-logo">
                <svg width="40" height="40" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect width="48" height="48" rx="10" fill="#4f46e5"/>
                    <path d="M24 12L32 20H28V32H20V20H16L24 12Z" fill="white"/>
                </svg>
            </div>
            <h1 class="brand-title">Create Account</h1>
            <p class="brand-subtitle">Get started with Shift Scheduler today</p>
        </div>

        <?php if ($dbError && empty($error)): ?>
            <div class="alert alert-error">
                <strong>⚠️ Database Setup Required</strong><br><br>
                The companies table doesn't exist yet. Please run the database setup script first.<br><br>
                <strong>Quick Fix:</strong><br>
                <code style="background: rgba(0,0,0,0.3); padding: 8px 12px; border-radius: 6px; display: block; margin: 8px 0; font-family: monospace;">
mysql -u shift_user -p ShiftSchedulerDB &lt; database/shift_scheduler.sql
                </code>
            </div>
        <?php elseif ($error || $message): ?>
            <div class="alert alert-error">
                <?= e($error ?: $message) ?>
            </div>
        <?php endif; ?>

        <form id="signup-form" class="login-form" method="post" action="/index.php" novalidate>
            <input type="hidden" name="action" value="signup">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            
            <div class="form-group">
                <label for="signup-company-name" class="form-label">Company Name <span class="required">*</span></label>
                <div class="input-container">
                    <input
                        type="text"
                        id="signup-company-name"
                        name="company_name"
                        class="form-input"
                        required
                        autocomplete="organization"
                        placeholder="Enter your company name"
                    >
                </div>
                <span class="form-error" data-error-for="signup-company-name"></span>
            </div>

            <div class="form-group">
                <label for="signup-full-name" class="form-label">Full Name <span class="required">*</span></label>
                <div class="input-container">
                    <input
                        type="text"
                        id="signup-full-name"
                        name="full_name"
                        class="form-input"
                        required
                        autocomplete="name"
                        placeholder="Enter your full name"
                    >
                </div>
                <span class="form-error" data-error-for="signup-full-name"></span>
            </div>

            <div class="form-group">
                <label for="signup-admin-email" class="form-label">Work Email <span class="required">*</span></label>
                <div class="input-container">
                    <input
                        type="email"
                        id="signup-admin-email"
                        name="admin_email"
                        class="form-input"
                        required
                        autocomplete="email"
                        placeholder="you@company.com"
                    >
                </div>
                <span class="form-error" data-error-for="signup-admin-email"></span>
            </div>

            <div class="form-group">
                <label for="signup-password" class="form-label">Password <span class="required">*</span></label>
                <div class="input-container password-container">
                    <input
                        type="password"
                        id="signup-password"
                        name="admin_password"
                        class="form-input"
                        required
                        autocomplete="new-password"
                        placeholder="Create a password"
                        minlength="8"
                    >
                    <button type="button" class="password-toggle" id="signup-password-toggle" aria-label="Toggle password visibility">
                        <svg class="icon-eye" width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M10 3C5.5 3 1.73 5.61 0 9C1.73 12.39 5.5 15 10 15C14.5 15 18.27 12.39 20 9C18.27 5.61 14.5 3 10 3Z" stroke="currentColor" stroke-width="2"/>
                            <circle cx="10" cy="9" r="2.5" stroke="currentColor" stroke-width="2"/>
                        </svg>
                    </button>
                </div>
                <span class="form-error" data-error-for="signup-password"></span>
            </div>

            <div class="form-group">
                <label for="signup-confirm-password" class="form-label">Confirm Password <span class="required">*</span></label>
                <div class="input-container password-container">
                    <input
                        type="password"
                        id="signup-confirm-password"
                        name="confirm_password"
                        class="form-input"
                        required
                        autocomplete="new-password"
                        placeholder="Confirm your password"
                        minlength="8"
                    >
                    <button type="button" class="password-toggle" id="signup-confirm-password-toggle" aria-label="Toggle password visibility">
                        <svg class="icon-eye" width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M10 3C5.5 3 1.73 5.61 0 9C1.73 12.39 5.5 15 10 15C14.5 15 18.27 12.39 20 9C18.27 5.61 14.5 3 10 3Z" stroke="currentColor" stroke-width="2"/>
                            <circle cx="10" cy="9" r="2.5" stroke="currentColor" stroke-width="2"/>
                        </svg>
                    </button>
                </div>
                <span class="form-error" data-error-for="signup-confirm-password"></span>
            </div>

            <div class="form-group">
                <label class="checkbox-label" style="display: flex; align-items: flex-start; gap: var(--space-2); cursor: pointer;">
                    <input 
                        type="checkbox" 
                        id="signup-terms" 
                        name="accept_terms" 
                        required
                        style="margin-top: 2px; width: 18px; height: 18px; cursor: pointer;"
                    >
                    <span style="font-size: var(--font-size-sm); color: var(--color-text-secondary); line-height: 1.5;">
                        I agree to the <a href="/terms" target="_blank" style="color: var(--primary); text-decoration: underline;">Terms of Service</a> and <a href="/privacy" target="_blank" style="color: var(--primary); text-decoration: underline;">Privacy Policy</a>
                    </span>
                </label>
                <span class="form-error" data-error-for="signup-terms"></span>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary btn-full-width">
                    <span>Create Account</span>
                </button>
            </div>
        </form>

        <div class="form-footer" style="margin-top: var(--space-6); padding-top: var(--space-6); border-top: 1px solid var(--border); text-align: center;">
            <div class="form-footer-content" style="display: flex; align-items: center; justify-content: center; gap: var(--space-2);">
                <span class="form-footer-text" style="color: var(--color-text-secondary); font-size: var(--font-size-sm);">Already have an account?</span>
                <a href="/login.php" class="form-footer-link" style="color: var(--primary); text-decoration: none; font-weight: var(--font-weight-semibold); font-size: var(--font-size-sm);">Sign in</a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Password toggle functionality
    const toggles = ['signup-password-toggle', 'signup-confirm-password-toggle'];
    toggles.forEach(toggleId => {
        const toggle = document.getElementById(toggleId);
        if (toggle) {
            const input = toggle.closest('.password-container').querySelector('input');
            toggle.addEventListener('click', function() {
                const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                input.setAttribute('type', type);
            });
        }
    });
    
    // Password confirmation validation
    const password = document.getElementById('signup-password');
    const confirmPassword = document.getElementById('signup-confirm-password');
    if (password && confirmPassword) {
        confirmPassword.addEventListener('input', function() {
            if (confirmPassword.value !== password.value) {
                confirmPassword.setCustomValidity('Passwords do not match');
            } else {
                confirmPassword.setCustomValidity('');
            }
        });
    }

    // Form submission
    const form = document.getElementById('signup-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            // Client-side validation already handled by HTML5 required and custom validity
            // Server will handle additional validation
        });
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
