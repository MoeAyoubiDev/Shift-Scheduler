<?php
declare(strict_types=1);

/**
 * Onboarding Preview Page
 * Shows live preview of dashboard with example data
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

// Handle confirmation POST request (must be before any HTML output)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'confirm') {
    require_csrf($_POST);
    
    // Redirect to payment
    header('Location: /payment.php?company_id=' . (int)$_POST['company_id']);
    exit;
}

$onboardingData = Company::getOnboardingProgress((int)$companyId);

// Get actual data created during onboarding
$pdo = db();
$sections = [];
$employees = [];

try {
    $sectionStmt = $pdo->prepare("SELECT id, section_name FROM sections WHERE company_id = ?");
    $sectionStmt->execute([$companyId]);
    $sections = $sectionStmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($sections)) {
        $sectionId = (int)$sections[0]['id'];
        $empStmt = $pdo->prepare("
            SELECT e.id, e.full_name, e.email, e.employee_code, r.role_name
            FROM employees e
            INNER JOIN user_roles ur ON ur.id = e.user_role_id
            INNER JOIN roles r ON r.id = ur.role_id
            WHERE ur.section_id = ? AND e.is_active = 1
            LIMIT 5
        ");
        $empStmt->execute([$sectionId]);
        $employees = $empStmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    // Data might not be created yet, use onboarding data
}

$title = 'Preview Your Dashboard - Shift Scheduler';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="preview-container">
    <div class="preview-header">
        <h1>Preview Your Dashboard</h1>
        <p>This is how your shift scheduler will look. Review and confirm to proceed to payment.</p>
    </div>

    <!-- Preview Dashboard -->
    <div class="preview-dashboard">
        <div class="preview-card">
            <div class="preview-card-header">
                <h3>Overview</h3>
                <span class="preview-badge">Preview Mode</span>
            </div>
            <div class="preview-metrics">
                <div class="preview-metric">
                    <div class="metric-icon" style="background: linear-gradient(135deg, #6366f1, #8b5cf6);">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <path d="M17 21V19C17 17.9391 16.5786 16.9217 15.8284 16.1716C15.0783 15.4214 14.0609 15 13 15H5C3.93913 15 2.92172 15.4214 2.17157 16.1716C1.42143 16.9217 1 17.9391 1 19V21" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M9 11C11.2091 11 13 9.20914 13 7C13 4.79086 11.2091 3 9 3C6.79086 3 5 4.79086 5 7C5 9.20914 6.79086 11 9 11Z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <h4>Total Employees</h4>
                    <div class="metric-value"><?= e(count($employees) ?: count($onboardingData['step_3']['data']['employees'] ?? [])) ?></div>
                </div>
                
                <div class="preview-metric">
                    <div class="metric-icon" style="background: linear-gradient(135deg, #10b981, #059669);">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <path d="M8 2V6M16 2V6M3 10H21M5 4H19C20.1046 4 21 4.89543 21 6V19C21 20.1046 20.1046 21 19 21H5C3.89543 21 3 20.1046 3 19V6C3 4.89543 3.89543 4 5 4Z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <h4>Weekly Shifts</h4>
                    <div class="metric-value"><?= e($onboardingData['step_2']['data']['work_days_per_week'] ?? '5') ?> days</div>
                </div>
                
                <div class="preview-metric">
                    <div class="metric-icon" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="10" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M12 6V12L16 14" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <h4>Shift Duration</h4>
                    <div class="metric-value"><?= e($onboardingData['step_2']['data']['default_shift_hours'] ?? '8') ?> hrs</div>
                </div>
            </div>
        </div>

        <div class="preview-card">
            <div class="preview-card-header">
                <h3>Example Schedule</h3>
            </div>
            <div class="preview-schedule">
                <table class="preview-table">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Mon</th>
                            <th>Tue</th>
                            <th>Wed</th>
                            <th>Thu</th>
                            <th>Fri</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if (empty($employees)) {
                            $onboardingEmps = $onboardingData['step_3']['data']['employees'] ?? [];
                            $sampleEmployees = array_slice($onboardingEmps, 0, 3);
                            foreach ($sampleEmployees as $idx => $emp): 
                        ?>
                            <tr>
                                <td><?= e($emp['full_name'] ?? 'Employee ' . ($idx + 1)) ?></td>
                                <td><span class="shift-pill shift-am">AM</span></td>
                                <td><span class="shift-pill shift-pm">PM</span></td>
                                <td><span class="shift-pill shift-mid">MID</span></td>
                                <td><span class="shift-pill shift-am">AM</span></td>
                                <td><span class="shift-pill">OFF</span></td>
                            </tr>
                        <?php 
                            endforeach;
                        } else {
                            $sampleEmployees = array_slice($employees, 0, 3);
                            foreach ($sampleEmployees as $emp): 
                        ?>
                            <tr>
                                <td><?= e($emp['full_name']) ?></td>
                                <td><span class="shift-pill shift-am">AM</span></td>
                                <td><span class="shift-pill shift-pm">PM</span></td>
                                <td><span class="shift-pill shift-mid">MID</span></td>
                                <td><span class="shift-pill shift-am">AM</span></td>
                                <td><span class="shift-pill">OFF</span></td>
                            </tr>
                        <?php 
                            endforeach;
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Confirmation Actions -->
    <div class="preview-actions">
        <form method="post" action="/onboarding-preview.php" class="preview-form">
            <input type="hidden" name="action" value="confirm">
            <input type="hidden" name="company_id" value="<?= e($companyId) ?>">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            
            <div class="form-actions">
                <a href="/onboarding.php?step=5&company_id=<?= $companyId ?>" class="btn secondary">Back to Review</a>
                <button type="submit" class="btn-primary btn-large">
                    <span>Confirm & Proceed to Payment</span>
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path d="M7.5 15L12.5 10L7.5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.preview-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: var(--space-2xl);
    width: 100%;
    max-width: min(1200px, 100vw - 2rem);
    box-sizing: border-box;
    overflow-x: hidden;
}

.preview-header {
    text-align: center;
    margin-bottom: var(--space-2xl);
    width: 100%;
    max-width: 100%;
}

.preview-header h1 {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: var(--space-md);
    background: linear-gradient(135deg, #ffffff 0%, #a0aec0 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    word-wrap: break-word;
}

.preview-header p {
    color: var(--color-text-secondary);
    font-size: 1.125rem;
    word-wrap: break-word;
}

.preview-dashboard {
    display: flex;
    flex-direction: column;
    gap: var(--space-xl);
    margin-bottom: var(--space-2xl);
    width: 100%;
    max-width: 100%;
}

.preview-card {
    background: var(--glass-bg);
    backdrop-filter: var(--glass-backdrop);
    border: 1px solid var(--glass-border);
    border-radius: var(--radius-xl);
    padding: var(--space-xl);
    box-shadow: var(--shadow-lg);
    width: 100%;
    max-width: 100%;
    box-sizing: border-box;
    overflow: visible;
}

.preview-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--space-lg);
    padding-bottom: var(--space-md);
    border-bottom: 1px solid var(--glass-border);
}

.preview-card-header h3 {
    font-size: 1.5rem;
    font-weight: 600;
}

.preview-badge {
    padding: var(--space-xs) var(--space-md);
    background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
    border-radius: var(--radius-full);
    font-size: 0.875rem;
    font-weight: 600;
    color: white;
}

.preview-metrics {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: var(--space-lg);
}

.preview-metric {
    text-align: center;
}

.preview-metric h4 {
    font-size: 0.875rem;
    color: var(--color-text-secondary);
    margin: var(--space-sm) 0;
}

.preview-table {
    width: 100%;
    border-collapse: collapse;
}

.preview-table th,
.preview-table td {
    padding: var(--space-md);
    text-align: left;
    border-bottom: 1px solid var(--glass-border);
}

.preview-table th {
    font-weight: 600;
    color: var(--color-text-secondary);
    font-size: 0.875rem;
}

.shift-pill {
    display: inline-block;
    padding: var(--space-xs) var(--space-sm);
    border-radius: var(--radius-full);
    font-size: 0.75rem;
    font-weight: 600;
    background: var(--glass-bg);
    border: 1px solid var(--glass-border);
}

.shift-pill.shift-am { background: rgba(59, 130, 246, 0.2); color: #93c5fd; }
.shift-pill.shift-pm { background: rgba(245, 158, 11, 0.2); color: #fcd34d; }
.shift-pill.shift-mid { background: rgba(139, 92, 246, 0.2); color: #c4b5fd; }

.preview-actions {
    text-align: center;
}

.preview-form {
    display: inline-block;
}
</style>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>

