<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseModel.php';

class User extends BaseModel
{
    protected string $table = 'users';

    private static function mapUserRows(array $rows): ?array
    {
        if (empty($rows)) {
            return null;
        }

        $primary = $rows[0];
        return [
            'id' => (int) $primary['user_id'],
            'username' => $primary['username'],
            'email' => $primary['email'],
            'company_id' => $primary['company_id'] ? (int) $primary['company_id'] : null,
            'company_name' => $primary['company_name'] ?? null,
            'role' => $primary['role_name'],
            'employee_id' => $primary['employee_id'] ? (int) $primary['employee_id'] : null,
            'is_senior' => (int) ($primary['is_senior'] ?? 0),
            'seniority_level' => (int) ($primary['seniority_level'] ?? 0),
            'employee_code' => $primary['employee_code'] ?? null,
            'full_name' => $primary['employee_name'] ?? null,
            'onboarding_completed' => (bool) ($primary['onboarding_completed'] ?? false),
        ];
    }

    public static function createEmployee(array $payload): int
    {
        $model = new self();
        $rows = $model->callProcedure('sp_create_employee', [
            'p_company_id' => $payload['company_id'],
            'p_username' => $payload['username'],
            'p_password_hash' => $payload['password_hash'],
            'p_email' => $payload['email'],
            'p_role_id' => $payload['role_id'],
            'p_employee_code' => $payload['employee_code'],
            'p_full_name' => $payload['full_name'],
            'p_is_senior' => $payload['is_senior'],
            'p_seniority_level' => $payload['seniority_level'],
        ]);

        return (int) ($rows[0]['employee_id'] ?? 0);
    }

    public static function createLeader(array $payload): int
    {
        $model = new self();
        $rows = $model->callProcedure('sp_create_leader', [
            'p_company_id' => $payload['company_id'],
            'p_username' => $payload['username'],
            'p_password_hash' => $payload['password_hash'],
            'p_email' => $payload['email'],
            'p_role_id' => $payload['role_id'],
            'p_full_name' => $payload['full_name'],
        ]);

        return (int) ($rows[0]['user_id'] ?? 0);
    }

    public static function findByEmail(string $email): ?array
    {
        try {
            $model = new self();
            $rows = $model->callProcedure('sp_get_user_by_email', [
                'p_email' => $email,
            ]);

            return self::mapUserRows($rows ?? []);
        } catch (PDOException $e) {
            error_log("User lookup by email failed: " . $e->getMessage());
            return null;
        }
    }

    public static function authenticate(string $identifier, string $password): ?array
    {
        try {
            $model = new self();
            $rows = $model->callProcedure('sp_get_user_by_identifier', [
                'p_identifier' => $identifier,
            ]);

            if (empty($rows)) {
                return null;
            }

            $passwordHash = $rows[0]['password_hash'] ?? '';
            if ($passwordHash === '' || !password_verify($password, $passwordHash)) {
                return null;
            }

            return self::mapUserRows($rows ?? []);
        } catch (PDOException $e) {
            error_log("User authentication failed: " . $e->getMessage());
            return null;
        }
    }

    public static function emailExists(string $email): bool
    {
        try {
            $model = new self();
            $rows = $model->callProcedure('sp_user_email_exists', [
                'p_email' => $email,
            ]);

            return (bool) ($rows[0]['email_exists'] ?? false);
        } catch (PDOException $e) {
            error_log("Email lookup failed: " . $e->getMessage());
            return false;
        }
    }

    public static function createSupervisor(array $payload): int
    {
        $model = new self();
        $rows = $model->callProcedure('sp_create_supervisor', [
            'p_company_id' => $payload['company_id'],
            'p_username' => $payload['username'],
            'p_password_hash' => $payload['password_hash'],
            'p_email' => $payload['email'],
            'p_full_name' => $payload['full_name'],
        ]);

        return (int) ($rows[0]['user_id'] ?? 0);
    }

}
