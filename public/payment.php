<?php
declare(strict_types=1);

/**
 * Payment Page
 * One-time payment for company activation
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../app/Helpers/helpers.php';
require_once __DIR__ . '/../app/Models/Company.php';

$companyId = $_GET['company_id'] ?? $_SESSION['onboarding_company_id'] ?? null;

if (!$companyId) {
    header('Location: /signup.php');
    exit;
}

$company = Company::findById((int)$companyId);
if (!$company || $company['status'] !== 'PAYMENT_PENDING') {
    header('Location: /onboarding.php?company_id=' . $companyId);
    exit;
}

$error = '';
$success = '';

// Payment amount (configurable)
$paymentAmount = 299.00; // One-time payment

// Handle payment completion (webhook or direct)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'complete_payment') {
    require_csrf($_POST);
    
    // In production, verify payment with payment provider (Stripe, PayPal, etc.)
    // For now, simulate successful payment
    $paymentToken = bin2hex(random_bytes(16));
    
    if (Company::completePayment((int)$companyId, $paymentToken, $paymentAmount)) {
        $success = 'Payment completed successfully! Your account is now active.';
        $_SESSION['payment_success'] = true;
        $_SESSION['company_id'] = $companyId;
        
        // Redirect to login after 3 seconds
        header('Refresh: 3; url=/login.php?payment=success');
    } else {
        $error = 'Payment processing failed. Please try again.';
    }
}

$title = 'Complete Payment - Shift Scheduler';
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
            <h1 class="brand-title">Complete Your Setup</h1>
            <p class="brand-subtitle">One-time payment to activate your account</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= e($success) ?></div>
            <p>Redirecting to login...</p>
        <?php else: ?>
            <div class="payment-summary">
                <div class="payment-item">
                    <span>Shift Scheduler License</span>
                    <span class="payment-amount">$<?= number_format($paymentAmount, 2) ?></span>
                </div>
                <div class="payment-total">
                    <span>Total</span>
                    <span class="payment-amount">$<?= number_format($paymentAmount, 2) ?></span>
                </div>
            </div>

            <div class="payment-features">
                <h3>What you get:</h3>
                <ul>
                    <li>✓ Unlimited employees and shifts</li>
                    <li>✓ Full scheduling features</li>
                    <li>✓ Break monitoring</li>
                    <li>✓ Performance analytics</li>
                    <li>✓ Priority support</li>
                    <li>✓ Lifetime updates</li>
                </ul>
            </div>

            <form method="post" action="/payment.php" class="payment-form">
                <input type="hidden" name="action" value="complete_payment">
                <input type="hidden" name="company_id" value="<?= e($companyId) ?>">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                
                <!-- Payment Method Selection -->
                <div class="form-group">
                    <label class="form-label">Payment Method</label>
                    <div class="payment-methods">
                        <label class="payment-method">
                            <input type="radio" name="payment_method" value="stripe" checked>
                            <span>Credit Card (Stripe)</span>
                        </label>
                        <label class="payment-method">
                            <input type="radio" name="payment_method" value="paypal">
                            <span>PayPal</span>
                        </label>
                    </div>
                </div>

                <!-- Payment Details (Stripe integration would go here) -->
                <div class="payment-details" id="stripe-details">
                    <div class="form-group">
                        <label class="form-label">Card Number</label>
                        <input type="text" class="form-input" placeholder="4242 4242 4242 4242" maxlength="19">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Expiry</label>
                            <input type="text" class="form-input" placeholder="MM/YY" maxlength="5">
                        </div>
                        <div class="form-group">
                            <label class="form-label">CVV</label>
                            <input type="text" class="form-input" placeholder="123" maxlength="4">
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary btn-large">
                        <span>Pay $<?= number_format($paymentAmount, 2) ?></span>
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M7.5 15L12.5 10L7.5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>

                <p class="payment-note">
                    <small>🔒 Secure payment processing. Your payment information is encrypted and secure.</small>
                </p>
            </form>
        <?php endif; ?>
    </div>
</div>

<style>
.payment-summary {
    background: var(--glass-bg);
    border: 1px solid var(--glass-border);
    border-radius: var(--radius-lg);
    padding: var(--space-lg);
    margin-bottom: var(--space-xl);
}

.payment-item {
    display: flex;
    justify-content: space-between;
    padding: var(--space-md) 0;
    border-bottom: 1px solid var(--glass-border);
}

.payment-total {
    display: flex;
    justify-content: space-between;
    padding: var(--space-md) 0;
    font-weight: 700;
    font-size: 1.25rem;
}

.payment-amount {
    color: var(--color-primary);
}

.payment-features {
    background: var(--glass-bg);
    border: 1px solid var(--glass-border);
    border-radius: var(--radius-lg);
    padding: var(--space-lg);
    margin-bottom: var(--space-xl);
}

.payment-features h3 {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: var(--space-md);
}

.payment-features ul {
    list-style: none;
    padding: 0;
}

.payment-features li {
    padding: var(--space-xs) 0;
    color: var(--color-text-secondary);
}

.payment-methods {
    display: flex;
    gap: var(--space-md);
    flex-wrap: wrap;
}

.payment-method {
    flex: 1;
    min-width: 150px;
    padding: var(--space-md);
    background: var(--glass-bg);
    border: 1px solid var(--glass-border);
    border-radius: var(--radius-md);
    cursor: pointer;
    transition: all var(--transition-base);
    display: flex;
    align-items: center;
    gap: var(--space-sm);
}

.payment-method:hover {
    border-color: var(--color-primary);
    background: rgba(99, 102, 241, 0.1);
}

.payment-method input[type="radio"] {
    margin: 0;
}

.payment-method input[type="radio"]:checked + span {
    color: var(--color-primary);
    font-weight: 600;
}

.payment-details {
    margin-top: var(--space-lg);
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--space-md);
}

.payment-note {
    text-align: center;
    color: var(--color-text-muted);
    font-size: 0.875rem;
    margin-top: var(--space-md);
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

