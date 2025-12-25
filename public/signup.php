<?php
declare(strict_types=1);

/**
 * Company Sign-Up Page
 * First step in the onboarding process
 */

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', '0'); // Don't display errors, but log them
ini_set('log_errors', '1');

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
        // Table doesn't exist - show setup message
        $dbError = true;
    }
} catch (Throwable $e) {
    error_log("Signup page error: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    $dbError = true;
    $error = 'Application initialization error. Please check server logs.';
    // Don't die - show error message instead
}

// Initialize variables
if (!isset($error)) $error = '';
if (!isset($dbError)) $dbError = false;

$title = 'Sign Up - Shift Scheduler';

// Ensure all required functions are available before including header
if (!function_exists('e')) {
    require_once __DIR__ . '/../app/Helpers/view.php';
}
if (!function_exists('app_config')) {
    require_once __DIR__ . '/../app/Core/config.php';
}
if (!function_exists('current_user')) {
    require_once __DIR__ . '/../app/Helpers/helpers.php';
}

// Only include header if we have the required functions
try {
    require_once __DIR__ . '/../includes/header.php';
} catch (Throwable $e) {
    error_log("Header error: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    // Fallback header
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
        <meta name="format-detection" content="telephone=no">
        <title><?= htmlspecialchars($title ?? 'Sign Up') ?></title>
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
                <svg width="32" height="32" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect width="48" height="48" rx="10" fill="white"/>
                    <path d="M24 12L32 20H28V32H20V20H16L24 12Z" fill="#4f46e5"/>
                </svg>
            </div>
            <h1 class="brand-title">Create Your Account</h1>
            <p class="brand-subtitle">Join thousands of companies managing their workforce efficiently</p>
        </div>

        <?php if ($dbError && empty($error)): ?>
            <div class="alert alert-error">
                <strong>⚠️ Database Setup Required</strong><br><br>
                The companies table doesn't exist yet. Please run the database setup script first.<br><br>
                <strong>Quick Fix:</strong><br>
                <code style="background: rgba(0,0,0,0.3); padding: 8px 12px; border-radius: 6px; display: block; margin: 8px 0; font-family: monospace; white-space: pre-wrap;">
php database/setup.php
                </code>
                <small>See <a href="/README.md" target="_blank" style="color: #93c5fd; text-decoration: underline;">README.md</a> for detailed instructions.</small>
            </div>
        <?php elseif ($error): ?>
            <div class="alert alert-error">
                <?= e($error) ?>
                <?php if ($dbError): ?>
                    <br><br>
                    <strong>Database Setup Required:</strong><br>
                    Please run the database setup script. See <a href="/README.md" target="_blank" style="color: #93c5fd;">README.md</a> for instructions.
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <form id="firebase-signup-form" class="login-form firebase-login-form" novalidate>
            <div class="form-group">
                <label for="signup-company-name" class="form-label">Company Name</label>
                <div class="input-container">
                    <input
                        type="text"
                        id="signup-company-name"
                        name="company_name"
                        class="form-input"
                        required
                        autocomplete="organization"
                        placeholder="Acme Corporation"
                    >
                </div>
                <span class="form-error" data-error-for="signup-company-name"></span>
            </div>

            <div class="form-group">
                <label for="signup-admin-email" class="form-label">Admin Email</label>
                <div class="input-container">
                    <input
                        type="email"
                        id="signup-admin-email"
                        name="admin_email"
                        class="form-input"
                        required
                        autocomplete="email"
                        placeholder="admin@company.com"
                    >
                </div>
                <span class="form-error" data-error-for="signup-admin-email"></span>
            </div>

            <div class="form-group">
                <label for="signup-password" class="form-label">Password</label>
                <div class="input-container password-container">
                    <input
                        type="password"
                        id="signup-password"
                        name="admin_password"
                        class="form-input"
                        required
                        autocomplete="new-password"
                        placeholder="Minimum 8 characters"
                        minlength="8"
                    >
                    <button type="button" class="password-toggle" id="signup-password-toggle" aria-label="Toggle password visibility">
                        <svg class="icon-eye" width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M10 3C5.5 3 1.73 5.61 0 9C1.73 12.39 5.5 15 10 15C14.5 15 18.27 12.39 20 9C18.27 5.61 14.5 3 10 3Z" fill="currentColor"/>
                        </svg>
                    </button>
                </div>
                <span class="form-error" data-error-for="signup-password"></span>
            </div>

            <div id="firebase-auth-error" class="form-error form-error-block" role="alert"></div>

            <div class="form-actions">
                <button type="submit" class="btn-primary firebase-submit">
                    <span>Create Account</span>
                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                        <path d="M6.75 13.5L11.25 9L6.75 4.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>
        </form>

        <div class="form-footer">
            <div class="form-footer-content">
                <span class="form-footer-text">Already have an account?</span>
                <a href="/login.php" class="form-footer-link">Sign in</a>
            </div>
        </div>
    </div>
</div>

<script>
// Password toggle functionality
document.addEventListener('DOMContentLoaded', function() {
    const toggles = ['signup-password-toggle'];
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
});
</script>

<script>
window.AppConfig = <?= json_encode([
    'csrfToken' => csrf_token(),
    'firebase' => [
        'apiKey' => 'AIzaSyAw4EH-GEqUUpnmRJ4XfN2AFUwmd5XoaFY',
        'authDomain' => 'shiftscheduler-37b31.firebaseapp.com',
        'projectId' => 'shiftscheduler-37b31',
        'storageBucket' => 'shiftscheduler-37b31.firebasestorage.app',
        'messagingSenderId' => '461867929776',
        'appId' => '1:461867929776:web:cfce1f8f4be7a74f51ab75',
        'measurementId' => 'G-DKCL8P1284',
    ],
], JSON_UNESCAPED_SLASHES) ?>;
</script>
<script src="https://www.gstatic.com/firebasejs/9.22.2/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.22.2/firebase-auth-compat.js"></script>
<script src="/assets/js/firebase-auth.js?v=<?= @filemtime(__DIR__ . '/assets/js/firebase-auth.js') ?: time() ?>"></script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
