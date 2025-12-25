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
require_once __DIR__ . '/../app/Controllers/AuthController.php';
require_once __DIR__ . '/../app/Core/ActionHandler.php';
require_once __DIR__ . '/../app/Models/Schedule.php';

// Initialize week data for action handler
$today = new DateTimeImmutable();
$weekStart = $today->modify('monday this week')->format('Y-m-d');
$weekEnd = $today->modify('sunday this week')->format('Y-m-d');
$weekId = Schedule::upsertWeek($weekStart, $weekEnd);
ActionHandler::initialize($weekId);

$error = '';
$message = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $result = ActionHandler::process($_POST);
    
    if (isset($result['redirect'])) {
        header('Location: ' . $result['redirect']);
        exit;
    }
    
    if (isset($result['message'])) {
        $error = $result['message'];
    }
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
            <div class="alert alert-success"><?= e($message) ?></div>
        <?php endif; ?>

        <div id="firebase-auth-error" class="alert alert-error" style="display: none;"></div>

        <div class="firebase-auth">
            <form id="firebase-login-form" class="login-form firebase-login-form" novalidate>
                <div class="form-group">
                    <label for="firebase-email" class="form-label">Email</label>
                    <div class="input-container">
                        <input
                            type="email"
                            id="firebase-email"
                            name="firebase_email"
                            class="form-input"
                            required
                            autocomplete="email"
                            placeholder="you@company.com"
                        >
                    </div>
                    <span class="form-error" data-error-for="firebase-email"></span>
                </div>

                <div class="form-group">
                    <label for="firebase-password" class="form-label">Password</label>
                    <div class="input-container">
                        <input
                            type="password"
                            id="firebase-password"
                            name="firebase_password"
                            class="form-input"
                            required
                            autocomplete="current-password"
                            placeholder="Enter your password"
                        >
                    </div>
                    <span class="form-error" data-error-for="firebase-password"></span>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary firebase-submit">
                        <span>Sign In</span>
                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                            <path d="M6.75 13.5L11.25 9L6.75 4.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

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
