<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseModel.php';

class Company extends BaseModel
{
    protected string $table = 'companies';

    /**
     * Create a new company account
     */
    public static function create(array $data): array
    {
        $model = new self();
        
        try {
            $rows = $model->callProcedure('sp_create_company', [
                'p_company_name' => $data['company_name'],
                'p_admin_email' => $data['admin_email'],
                'p_admin_password_hash' => password_hash($data['admin_password'], PASSWORD_BCRYPT),
                'p_timezone' => $data['timezone'] ?? 'UTC',
                'p_country' => $data['country'] ?? null,
                'p_company_size' => $data['company_size'] ?? null,
                'p_verification_token' => bin2hex(random_bytes(32)),
            ]);
            
            $companyId = (int)($rows[0]['company_id'] ?? 0);
            
            if ($companyId > 0) {
                // Get the verification token
                $company = $model->findById($companyId);
                $verificationToken = $company['verification_token'] ?? null;
                
                return [
                    'success' => true,
                    'company_id' => $companyId,
                    'verification_token' => $verificationToken,
                ];
            }
            
            return ['success' => false, 'message' => 'Failed to create company account.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Find company by email
     */
    public static function findByEmail(string $email): ?array
    {
        $model = new self();
        $rows = $model->query("SELECT * FROM companies WHERE admin_email = ?", [$email]);
        return $rows[0] ?? null;
    }

    /**
     * Find company by ID
     */
    public static function findById(int $id): ?array
    {
        $model = new self();
        $rows = $model->query("SELECT * FROM companies WHERE id = ?", [$id]);
        return $rows[0] ?? null;
    }

    /**
     * Verify company email
     */
    public static function verifyEmail(string $token): bool
    {
        $model = new self();
        $rows = $model->callProcedure('sp_verify_company_email', ['p_token' => $token]);
        return ($rows[0]['updated'] ?? 0) > 0;
    }

    /**
     * Complete payment
     */
    public static function completePayment(int $companyId, string $paymentToken, float $amount): bool
    {
        $model = new self();
        $rows = $model->callProcedure('sp_complete_company_payment', [
            'p_company_id' => $companyId,
            'p_payment_token' => $paymentToken,
            'p_payment_amount' => $amount,
        ]);
        return ($rows[0]['updated'] ?? 0) > 0;
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

