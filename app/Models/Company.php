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
        $adminPasswordHash = $data['admin_password_hash'] ?? null;
        
        try {
            $rows = $model->callProcedure('sp_create_company', [
                'p_company_name' => $data['company_name'],
                'p_admin_email' => $data['admin_email'],
                'p_admin_password_hash' => $adminPasswordHash,
                'p_timezone' => $data['timezone'] ?? 'UTC',
                'p_country' => $data['country'] ?? null,
                'p_company_size' => $data['company_size'] ?? null,
                'p_verification_token' => null,
            ]);
            
            $companyId = (int)($rows[0]['company_id'] ?? 0);
            
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
            $rows = $model->callProcedure('sp_get_company_by_email', [
                'p_admin_email' => $email,
            ]);
            return $rows[0] ?? null;
        } catch (PDOException $e) {
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
            $rows = $model->callProcedure('sp_get_company_by_id', [
                'p_company_id' => $id,
            ]);
            return $rows[0] ?? null;
        } catch (PDOException $e) {
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
            $model = new self();
            $rows = $model->callProcedure('sp_mark_company_verified', [
                'p_company_id' => $companyId,
            ]);

            return (int) ($rows[0]['updated'] ?? 0) > 0;
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

    public static function activateCompany(int $companyId): bool
    {
        try {
            $model = new self();
            $rows = $model->callProcedure('sp_activate_company', [
                'p_company_id' => $companyId,
            ]);

            return (int) ($rows[0]['updated'] ?? 0) > 0;
        } catch (PDOException $e) {
            error_log("Company activation failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update onboarding step
     */
    public static function updateOnboardingStep(int $companyId, string $step, array $data, bool $completed = false): void
    {
        $model = new self();
        $model->callProcedure('sp_upsert_onboarding_step', [
            'p_company_id' => $companyId,
            'p_step' => $step,
            'p_step_data' => json_encode($data),
            'p_completed' => $completed ? 1 : 0,
        ]);
    }

    /**
     * Get onboarding progress
     */
    public static function getOnboardingProgress(int $companyId): array
    {
        $model = new self();
        $rows = $model->callProcedure('sp_get_onboarding_progress', [
            'p_company_id' => $companyId,
        ]);
        
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
