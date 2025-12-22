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
    header('Location: /index.php');
    exit;
}

require_once __DIR__ . '/../app/Helpers/helpers.php';
require_once __DIR__ . '/../app/Models/Company.php';

$error = '';
$success = '';

// Handle sign-up form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'signup') {
    require_csrf($_POST);
    
    $companyName = trim($_POST['company_name'] ?? '');
    $adminEmail = trim($_POST['admin_email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $timezone = $_POST['timezone'] ?? 'UTC';
    $country = trim($_POST['country'] ?? '');
    $companySize = $_POST['company_size'] ?? '';
    
    // Validation
    if (empty($companyName) || strlen($companyName) < 2) {
        $error = 'Company name must be at least 2 characters.';
    } elseif (empty($adminEmail) || !filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (empty($password) || strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } else {
        // Check if email already exists
        $existing = Company::findByEmail($adminEmail);
        if ($existing) {
            $error = 'An account with this email already exists.';
        } else {
            // Create company
            $result = Company::create([
                'company_name' => $companyName,
                'admin_email' => $adminEmail,
                'admin_password' => $password,
                'timezone' => $timezone,
                'country' => $country,
                'company_size' => $companySize,
            ]);
            
            if ($result['success']) {
                // Store company ID in session for email verification
                $_SESSION['signup_company_id'] = $result['company_id'];
                $_SESSION['signup_email'] = $adminEmail;
                
                // Send verification email (placeholder - implement email service)
                // EmailService::sendVerificationEmail($adminEmail, $result['verification_token']);
                
                header('Location: /verify-email.php?email=' . urlencode($adminEmail));
                exit;
            } else {
                $error = $result['message'] ?? 'Failed to create account. Please try again.';
            }
        }
    }
}

$title = 'Sign Up - Shift Scheduler';
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
            <h1 class="brand-title">Create Your Account</h1>
            <p class="brand-subtitle">Start managing your workforce today</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= e($success) ?></div>
        <?php endif; ?>

        <form method="post" action="/signup.php" class="login-form">
            <input type="hidden" name="action" value="signup">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            
            <div class="form-group">
                <label for="company_name" class="form-label">Company Name</label>
                <input 
                    type="text" 
                    id="company_name" 
                    name="company_name" 
                    class="form-input" 
                    required 
                    autocomplete="organization"
                    placeholder="Your Company Name"
                    value="<?= e($_POST['company_name'] ?? '') ?>"
                >
            </div>
            
            <div class="form-group">
                <label for="admin_email" class="form-label">Admin Email</label>
                <input 
                    type="email" 
                    id="admin_email" 
                    name="admin_email" 
                    class="form-input" 
                    required 
                    autocomplete="email"
                    placeholder="admin@company.com"
                    value="<?= e($_POST['admin_email'] ?? '') ?>"
                >
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
                        autocomplete="new-password"
                        placeholder="Minimum 8 characters"
                        minlength="8"
                    >
                    <button type="button" class="password-toggle" id="password-toggle">
                        <svg class="icon-eye" width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M10 3C5.5 3 1.73 5.61 0 9C1.73 12.39 5.5 15 10 15C14.5 15 18.27 12.39 20 9C18.27 5.61 14.5 3 10 3Z" fill="currentColor"/>
                        </svg>
                    </button>
                </div>
            </div>
            
            <div class="form-group">
                <label for="confirm_password" class="form-label">Confirm Password</label>
                <div class="input-container password-container">
                    <input 
                        type="password" 
                        id="confirm_password" 
                        name="confirm_password" 
                        class="form-input" 
                        required 
                        autocomplete="new-password"
                        placeholder="Re-enter your password"
                        minlength="8"
                    >
                    <button type="button" class="password-toggle" id="confirm-password-toggle">
                        <svg class="icon-eye" width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M10 3C5.5 3 1.73 5.61 0 9C1.73 12.39 5.5 15 10 15C14.5 15 18.27 12.39 20 9C18.27 5.61 14.5 3 10 3Z" fill="currentColor"/>
                        </svg>
                    </button>
                </div>
            </div>
            
            <div class="form-group">
                <label for="timezone" class="form-label">Timezone</label>
                <select id="timezone" name="timezone" class="form-input" required>
                    <option value="UTC">UTC</option>
                    <option value="America/New_York">Eastern Time (ET)</option>
                    <option value="America/Chicago">Central Time (CT)</option>
                    <option value="America/Denver">Mountain Time (MT)</option>
                    <option value="America/Los_Angeles">Pacific Time (PT)</option>
                    <option value="Europe/London">London (GMT)</option>
                    <option value="Europe/Paris">Paris (CET)</option>
                    <option value="Asia/Dubai">Dubai (GST)</option>
                    <option value="Asia/Tokyo">Tokyo (JST)</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="country" class="form-label">Country</label>
                <input 
                    type="text" 
                    id="country" 
                    name="country" 
                    class="form-input" 
                    placeholder="United States"
                    value="<?= e($_POST['country'] ?? '') ?>"
                >
            </div>
            
            <div class="form-group">
                <label for="company_size" class="form-label">Company Size</label>
                <select id="company_size" name="company_size" class="form-input">
                    <option value="">Select size</option>
                    <option value="1-10">1-10 employees</option>
                    <option value="11-50">11-50 employees</option>
                    <option value="51-200">51-200 employees</option>
                    <option value="201-500">201-500 employees</option>
                    <option value="500+">500+ employees</option>
                </select>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    <span>Create Account</span>
                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                        <path d="M6.75 13.5L11.25 9L6.75 4.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>
            
            <div class="form-footer">
                <p>Already have an account? <a href="/login.php">Sign in</a></p>
            </div>
        </form>
    </div>
</div>

<script>
// Password toggle functionality
document.addEventListener('DOMContentLoaded', function() {
    const toggles = ['password-toggle', 'confirm-password-toggle'];
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

