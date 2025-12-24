<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseModel.php';

class Company extends BaseModel
{
    protected string $table = 'companies';

    /**
     * Create a new company account
     */
    public static function createCompany(array $data): array
    {
        $model = new Company();
        
        try {
            // Check if stored procedure exists, if not use direct insert
            try {
                $rows = $model->callProcedure('sp_create_company', [
                    'p_company_name' => $data['company_name'],
                    'p_admin_email' => $data['admin_email'],
                    'p_admin_password_hash' => password_hash($data['admin_password'], PASSWORD_BCRYPT),
                    'p_timezone' => $data['timezone'] ?? 'UTC',
                    'p_country' => $data['country'] ?? null,
                    'p_company_size' => $data['company_size'] ?? null,
                    'p_verification_token' => null, // No email verification - auto-verified
                ]);
                
                $companyId = (int)($rows[0]['company_id'] ?? 0);
            } catch (PDOException $e) {
                // If stored procedure doesn't exist, use direct insert
                if (strpos($e->getMessage(), 'does not exist') !== false || strpos($e->getMessage(), 'Unknown procedure') !== false) {
                    // Generate unique slug
                    $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $data['company_name']));
                    $slug = trim($slug, '-');
                    
                    // Ensure slug is unique (explicitly set collation for comparison)
                    $counter = 0;
                    $originalSlug = $slug;
                    try {
                        $existing = $model->query("SELECT id FROM companies WHERE company_slug COLLATE utf8mb4_unicode_ci = ?", [$slug]);
                        while (!empty($existing)) {
                            $counter++;
                            $slug = $originalSlug . '-' . $counter;
                            $existing = $model->query("SELECT id FROM companies WHERE company_slug COLLATE utf8mb4_unicode_ci = ?", [$slug]);
                        }
                    } catch (PDOException $e) {
                        // Table doesn't exist - that's okay, this is the first company
                        // Just use the original slug
                    }
                    
                    $passwordHash = password_hash($data['admin_password'], PASSWORD_BCRYPT);
                    
                    $pdo = db();
                    $stmt = $pdo->prepare("
                        INSERT INTO companies (
                            company_name, company_slug, admin_email, admin_password_hash,
                            timezone, country, company_size, verification_token, status, email_verified_at
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, NULL, 'VERIFIED', NOW())
                    ");
                    
                    $stmt->execute([
                        $data['company_name'],
                        $slug,
                        $data['admin_email'],
                        $passwordHash,
                        $data['timezone'] ?? 'UTC',
                        $data['country'] ?? null,
                        $data['company_size'] ?? null,
                    ]);
                    
                    $companyId = (int)$pdo->lastInsertId();
                } else {
                    throw $e;
                }
            }
            
            if ($companyId > 0) {
                // Auto-verify the company (no email verification needed)
                $model->verifyEmailDirect($companyId);
                
                return [
                    'success' => true,
                    'company_id' => $companyId,
                ];
            }
            
            return ['success' => false, 'message' => 'Failed to create company account.'];
        } catch (PDOException $e) {
            // Check if companies table exists
            if (strpos($e->getMessage(), "doesn't exist") !== false || strpos($e->getMessage(), 'Unknown table') !== false) {
                return [
                    'success' => false,
                    'message' => 'Database migrations not run. Please run the migrations first. See docs/MULTI_TENANT_SETUP.md',
                ];
            }
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Find company by email
     */
    public static function findByEmail(string $email): ?array
    {
        try {
            $model = new self();
            $rows = $model->query("SELECT * FROM companies WHERE admin_email = ?", [$email]);
            return $rows[0] ?? null;
        } catch (PDOException $e) {
            // Table doesn't exist - migrations not run
            return null;
        }
    }

    /**
     * Find company by ID
     */
    public static function findById(int $id): ?array
    {
        try {
            $model = new self();
            $rows = $model->query("SELECT * FROM companies WHERE id = ?", [$id]);
            return $rows[0] ?? null;
        } catch (PDOException $e) {
            // Table doesn't exist - migrations not run
            return null;
        }
    }

    /**
     * Verify company email
     */
    /**
     * Directly verify company email (auto-verification)
     * Companies are now auto-verified on signup - no email verification needed
     */
    public static function verifyEmailDirect(int $companyId): bool
    {
        try {
            $pdo = db();
            $stmt = $pdo->prepare("
                UPDATE companies 
                SET status = 'VERIFIED',
                    email_verified_at = NOW(),
                    verification_token = NULL
                WHERE id = ? AND status != 'ACTIVE'
            ");
            $stmt->execute([$companyId]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error verifying company email: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verify email via token (legacy method - kept for backward compatibility)
     * Note: Email verification is now disabled - companies are auto-verified
     */
    public static function verifyEmail(string $token): bool
    {
        $model = new self();
        try {
            $rows = $model->callProcedure('sp_verify_company_email', ['p_token' => $token]);
            return ($rows[0]['updated'] ?? 0) > 0;
        } catch (PDOException $e) {
            // Fallback to direct SQL if stored procedure doesn't exist
            if (strpos($e->getMessage(), 'does not exist') !== false || strpos($e->getMessage(), 'Unknown procedure') !== false) {
                $pdo = db();
                $stmt = $pdo->prepare("
                    UPDATE companies 
                    SET status = 'VERIFIED',
                        email_verified_at = NOW(),
                        verification_token = NULL
                    WHERE verification_token = ?
                      AND status = 'PENDING_VERIFICATION'
                ");
                $stmt->execute([$token]);
                return $stmt->rowCount() > 0;
            }
            throw $e;
        }
    }

    /**
     * Complete payment
     */
    public static function completePayment(int $companyId, string $paymentToken, float $amount): bool
    {
        $model = new self();
        try {
            $rows = $model->callProcedure('sp_complete_company_payment', [
                'p_company_id' => $companyId,
                'p_payment_token' => $paymentToken,
                'p_payment_amount' => $amount,
            ]);
            return ($rows[0]['updated'] ?? 0) > 0;
        } catch (PDOException $e) {
            // Fallback to direct SQL if stored procedure doesn't exist
            if (strpos($e->getMessage(), 'does not exist') !== false || strpos($e->getMessage(), 'Unknown procedure') !== false) {
                $pdo = db();
                $stmt = $pdo->prepare("
                    UPDATE companies 
                    SET payment_status = 'COMPLETED',
                        payment_token = ?,
                        payment_amount = ?,
                        payment_completed_at = NOW(),
                        status = 'ACTIVE'
                    WHERE id = ?
                      AND status = 'PAYMENT_PENDING'
                ");
                $stmt->execute([$paymentToken, $amount, $companyId]);
                return $stmt->rowCount() > 0;
            }
            throw $e;
        }
    }

    /**
     * Update onboarding step
     */
    public static function updateOnboardingStep(int $companyId, string $step, array $data, bool $completed = false): void
    {
        $model = new self();
        $model->query("
            INSERT INTO company_onboarding (company_id, step, step_data, completed, completed_at)
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                step_data = VALUES(step_data),
                completed = VALUES(completed),
                completed_at = IF(VALUES(completed) = 1, NOW(), completed_at)
        ", [
            $companyId,
            $step,
            json_encode($data),
            $completed ? 1 : 0,
            $completed ? date('Y-m-d H:i:s') : null,
        ]);
    }

    /**
     * Get onboarding progress
     */
    public static function getOnboardingProgress(int $companyId): array
    {
        $model = new self();
        $rows = $model->query("
            SELECT step, step_data, completed, completed_at
            FROM company_onboarding
            WHERE company_id = ?
            ORDER BY created_at
        ", [$companyId]);
        
        $progress = [];
        foreach ($rows as $row) {
            $progress[$row['step']] = [
                'data' => json_decode($row['step_data'], true) ?? [],
                'completed' => (bool)$row['completed'],
                'completed_at' => $row['completed_at'],
            ];
        }
        
        return $progress;
    }
}

