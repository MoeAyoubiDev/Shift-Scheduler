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
    header('Location: /index.php');
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
$success = '';

// Handle sign-up form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'signup') {
    // Ensure require_csrf is available
    if (!function_exists('require_csrf')) {
        require_once __DIR__ . '/../app/Helpers/helpers.php';
    }
    
    try {
        require_csrf($_POST);
    } catch (Exception $e) {
        error_log("CSRF error in signup: " . $e->getMessage());
        $error = 'Invalid session. Please refresh the page and try again.';
    }
    
    // Only proceed if no CSRF error occurred
    if (empty($error)) {
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
            try {
                // Check if email already exists
                $existing = Company::findByEmail($adminEmail);
                if ($existing) {
                    $error = 'An account with this email already exists.';
                } else {
                    // Create company
                    $result = Company::createCompany([
                        'company_name' => $companyName,
                        'admin_email' => $adminEmail,
                        'admin_password' => $password,
                        'timezone' => $timezone,
                        'country' => $country,
                        'company_size' => $companySize,
                    ]);
                    
                    if ($result['success']) {
                        // Store company ID in session for onboarding
                        $_SESSION['signup_company_id'] = $result['company_id'];
                        $_SESSION['onboarding_company_id'] = $result['company_id'];
                        $_SESSION['signup_email'] = $adminEmail;
                        
                        // Company is auto-verified - redirect directly to onboarding
                        header('Location: /onboarding.php?company_id=' . $result['company_id']);
                        exit;
                    } else {
                        $error = $result['message'] ?? 'Failed to create account. Please try again.';
                        if (strpos($error, 'migrations') !== false) {
                            $dbError = true;
                        }
                    }
                }
            } catch (Throwable $e) {
                error_log("Signup error: " . $e->getMessage());
                $error = 'An error occurred. Please check if database migrations have been run. See QUICK_FIX.md for instructions.';
                $dbError = true;
            }
        }
    }
}

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
    <div class="login-card">
        <div class="login-brand">
            <div class="brand-logo">
                <svg width="40" height="40" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect width="48" height="48" rx="10" fill="#4f46e5"/>
                    <path d="M24 12L32 20H28V32H20V20H16L24 12Z" fill="white"/>
                </svg>
            </div>
            <h1 class="brand-title">Create Your Account</h1>
            <p class="brand-subtitle">Get started with professional workforce management</p>
        </div>

        <?php if ($dbError && empty($error)): ?>
            <div class="alert alert-error">
                <strong>⚠️ Database Setup Required</strong><br><br>
                The companies table doesn't exist yet. Please run the database migrations first.<br><br>
                <strong>Quick Fix:</strong><br>
                <code style="background: rgba(0,0,0,0.3); padding: 8px 12px; border-radius: 6px; display: block; margin: 8px 0; font-family: monospace; white-space: pre-wrap;">
mysql -u root -p ShiftSchedulerDB &lt; database/migrations/001_add_companies_table.sql
mysql -u root -p ShiftSchedulerDB &lt; database/migrations/002_add_company_id_to_tables.sql
mysql -u root -p ShiftSchedulerDB &lt; database/migrations/003_update_stored_procedures.sql
                </code>
                <small>See <a href="/QUICK_FIX.md" target="_blank" style="color: #93c5fd; text-decoration: underline;">QUICK_FIX.md</a> for detailed instructions.</small>
            </div>
        <?php elseif ($error): ?>
            <div class="alert alert-error">
                <?= e($error) ?>
                <?php if ($dbError): ?>
                    <br><br>
                    <strong>Database Setup Required:</strong><br>
                    Please run the database migrations. See <a href="/QUICK_FIX.md" target="_blank" style="color: #93c5fd;">QUICK_FIX.md</a> for instructions.
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= e($success) ?></div>
        <?php endif; ?>

        <form method="post" action="/signup.php" class="login-form">
            <input type="hidden" name="action" value="signup">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            
            <!-- Company Information Section -->
            <div class="signup-form-section">
                <h3 class="signup-form-section-title">Company Information</h3>
                <div class="signup-form-grid">
                    <div class="form-group">
                        <label for="company_name" class="form-label">Company Name <span class="required">*</span></label>
                        <input 
                            type="text" 
                            id="company_name" 
                            name="company_name" 
                            class="form-input" 
                            required 
                            autocomplete="organization"
                            placeholder="Acme Corporation"
                            value="<?= e($_POST['company_name'] ?? '') ?>"
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
                </div>
            </div>
            
            <!-- Account Credentials Section -->
            <div class="signup-form-section">
                <h3 class="signup-form-section-title">Account Credentials</h3>
                <div class="form-group">
                    <label for="admin_email" class="form-label">Admin Email <span class="required">*</span></label>
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
                    <small class="form-hint">This will be your login email address</small>
                </div>
                
                <div class="signup-form-grid">
                    <div class="form-group">
                        <label for="password" class="form-label">Password <span class="required">*</span></label>
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
                            <button type="button" class="password-toggle" id="password-toggle" aria-label="Toggle password visibility">
                                <svg class="icon-eye" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                    <path d="M10 3C5.5 3 1.73 5.61 0 9C1.73 12.39 5.5 15 10 15C14.5 15 18.27 12.39 20 9C18.27 5.61 14.5 3 10 3Z" fill="currentColor"/>
                                </svg>
                            </button>
                        </div>
                        <small class="form-hint">Must be at least 8 characters</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password" class="form-label">Confirm Password <span class="required">*</span></label>
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
                            <button type="button" class="password-toggle" id="confirm-password-toggle" aria-label="Toggle password visibility">
                                <svg class="icon-eye" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                    <path d="M10 3C5.5 3 1.73 5.61 0 9C1.73 12.39 5.5 15 10 15C14.5 15 18.27 12.39 20 9C18.27 5.61 14.5 3 10 3Z" fill="currentColor"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Location Settings Section -->
            <div class="signup-form-section">
                <h3 class="signup-form-section-title">Location Settings</h3>
                <div class="signup-form-grid">
                    <div class="form-group">
                        <label for="timezone" class="form-label">Timezone <span class="required">*</span></label>
                        <select id="timezone" name="timezone" class="form-input" required>
                            <option value="UTC">UTC (Coordinated Universal Time)</option>
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
                </div>
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
                <div class="form-footer-content">
                    <span class="form-footer-text">Already have an account?</span>
                    <a href="/login.php" class="form-footer-link">Sign in to your account</a>
                </div>
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

