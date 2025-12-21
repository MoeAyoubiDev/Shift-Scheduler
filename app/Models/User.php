<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseModel.php';

class User extends BaseModel
{
    protected string $table = 'users';

    public static function authenticate(string $username, string $password): ?array
    {
        $model = new self();
        $rows = $model->callProcedure('sp_verify_login', [
            'p_username' => $username,
        ]);

        if (!$rows) {
            return null;
        }

        $primary = $rows[0];
        if (!password_verify($password, $primary['password_hash'])) {
            return null;
        }

        if (!$primary['is_active']) {
            return null;
        }

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
            'role' => $primary['role_name'],
            'section_id' => $primary['section_id'] ? (int) $primary['section_id'] : null,
            'section_name' => $primary['section_name'],
            'employee_id' => $primary['employee_id'] ? (int) $primary['employee_id'] : null,
            'is_senior' => (int) ($primary['is_senior'] ?? 0),
            'seniority_level' => (int) ($primary['seniority_level'] ?? 0),
            'employee_code' => $primary['employee_code'] ?? null,
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
}
