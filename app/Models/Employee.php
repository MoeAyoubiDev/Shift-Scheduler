<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseModel.php';

class Employee extends BaseModel
{
    protected string $table = 'employees';

    public static function listBySection(int $sectionId): array
    {
        $model = new self();
        return $model->callProcedure('sp_get_employees_by_section', [
            'p_section_id' => $sectionId,
        ]);
    }

    public static function availableForDate(int $sectionId, string $date): array
    {
        $model = new self();
        return $model->callProcedure('sp_get_available_employees', [
            'p_section_id' => $sectionId,
            'p_date' => $date,
        ]);
    }

    public static function listAdmins(): array
    {
        $model = new self();
        $sql = "SELECT e.id, e.employee_code, e.full_name, e.email, r.role_name, u.username, s.section_name
                FROM employees e
                INNER JOIN user_roles ur ON ur.id = e.user_role_id
                INNER JOIN roles r ON r.id = ur.role_id
                INNER JOIN users u ON u.id = ur.user_id
                INNER JOIN sections s ON s.id = ur.section_id
                WHERE e.is_active = 1
                  AND r.role_name IN ('Team Leader', 'Supervisor')
                ORDER BY s.section_name, r.role_name, e.full_name";

        return $model->query($sql);
    }

    public static function updateInSection(
        int $employeeId,
        int $sectionId,
        string $fullName,
        ?string $email,
        int $roleId,
        int $seniorityLevel,
        int $isSenior
    ): bool {
        $model = new self();
        $rows = $model->callProcedure('sp_update_employee', [
            'p_employee_id' => $employeeId,
            'p_section_id' => $sectionId,
            'p_full_name' => $fullName,
            'p_email' => $email,
            'p_role_id' => $roleId,
            'p_seniority_level' => $seniorityLevel,
            'p_is_senior' => $isSenior,
        ]);

        return (int) ($rows[0]['affected_rows'] ?? 0) > 0;
    }
}
