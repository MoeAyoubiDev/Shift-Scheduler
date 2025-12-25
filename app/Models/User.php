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
        $sections = [];
        foreach ($rows as $row) {
            $sections[] = [
                'section_id' => (int) $row['section_id'],
                'section_name' => $row['section_name'],
            ];
        }

        return [
            'id' => (int) $primary['user_id'],
            'username' => $primary['username'],
            'email' => $primary['email'],
            'company_id' => $primary['company_id'] ? (int) $primary['company_id'] : null,
            'role' => $primary['role_name'],
            'section_id' => $primary['section_id'] ? (int) $primary['section_id'] : null,
            'section_name' => $primary['section_name'],
            'employee_id' => $primary['employee_id'] ? (int) $primary['employee_id'] : null,
            'is_senior' => (int) ($primary['is_senior'] ?? 0),
            'seniority_level' => (int) ($primary['seniority_level'] ?? 0),
            'employee_code' => $primary['employee_code'] ?? null,
            'full_name' => $primary['employee_name'] ?? null,
            'onboarding_completed' => (bool) ($primary['onboarding_completed'] ?? false),
            'sections' => $sections,
        ];
    }

    public static function createEmployee(array $payload): int
    {
        $model = new self();
        $rows = $model->callProcedure('sp_create_employee', [
            'p_username' => $payload['username'],
            'p_password_hash' => $payload['password_hash'],
            'p_email' => $payload['email'],
            'p_role_id' => $payload['role_id'],
            'p_section_id' => $payload['section_id'],
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
            'p_username' => $payload['username'],
            'p_password_hash' => $payload['password_hash'],
            'p_email' => $payload['email'],
            'p_role_id' => $payload['role_id'],
            'p_section_id' => $payload['section_id'],
            'p_full_name' => $payload['full_name'],
        ]);

        return (int) ($rows[0]['user_id'] ?? 0);
    }

    public static function findByEmail(string $email): ?array
    {
        try {
            $pdo = db();
            $sql = "
                SELECT u.id AS user_id,
                       u.username,
                       u.email,
                       u.onboarding_completed,
                       u.is_active,
                       u.company_id,
                       ur.id AS user_role_id,
                       r.id AS role_id,
                       r.role_name,
                       s.id AS section_id,
                       s.section_name,
                       e.id AS employee_id,
                       e.full_name AS employee_name,
                       e.is_senior,
                       e.seniority_level,
                       e.employee_code
                FROM users u
                INNER JOIN user_roles ur ON ur.user_id = u.id
                INNER JOIN roles r ON r.id = ur.role_id
                INNER JOIN sections s ON s.id = ur.section_id
                LEFT JOIN employees e ON e.user_role_id = ur.id
                WHERE u.email = ? AND u.is_active = 1
            ";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$email]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return self::mapUserRows($rows);
        } catch (PDOException $e) {
            error_log("User lookup by email failed: " . $e->getMessage());
            return null;
        }
    }

    public static function authenticate(string $identifier, string $password): ?array
    {
        try {
            $pdo = db();
            $sql = "
                SELECT u.id AS user_id,
                       u.username,
                       u.email,
                       u.password_hash,
                       u.onboarding_completed,
                       u.is_active,
                       u.company_id,
                       ur.id AS user_role_id,
                       r.id AS role_id,
                       r.role_name,
                       s.id AS section_id,
                       s.section_name,
                       e.id AS employee_id,
                       e.full_name AS employee_name,
                       e.is_senior,
                       e.seniority_level,
                       e.employee_code
                FROM users u
                INNER JOIN user_roles ur ON ur.user_id = u.id
                INNER JOIN roles r ON r.id = ur.role_id
                INNER JOIN sections s ON s.id = ur.section_id
                LEFT JOIN employees e ON e.user_role_id = ur.id
                WHERE (u.username = ? OR u.email = ?) AND u.is_active = 1
            ";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$identifier, $identifier]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($rows)) {
                return null;
            }

            $passwordHash = $rows[0]['password_hash'] ?? '';
            if ($passwordHash === '' || !password_verify($password, $passwordHash)) {
                return null;
            }

            return self::mapUserRows($rows);
        } catch (PDOException $e) {
            error_log("User authentication failed: " . $e->getMessage());
            return null;
        }
    }

    public static function emailExists(string $email): bool
    {
        try {
            $pdo = db();
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Email lookup failed: " . $e->getMessage());
            return false;
        }
    }

}
