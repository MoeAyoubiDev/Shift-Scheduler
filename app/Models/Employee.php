<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseModel.php';

class Employee extends BaseModel
{
    protected string $table = 'employees';

    public static function listByCompany(int $companyId): array
    {
        $model = new self();
        return $model->callProcedure('sp_get_employees_by_company', [
            'p_company_id' => $companyId,
        ]);
    }

    public static function availableForDate(int $companyId, string $date): array
    {
        $model = new self();
        return $model->callProcedure('sp_get_available_employees', [
            'p_company_id' => $companyId,
            'p_date' => $date,
        ]);
    }

    public static function listAdmins(): array
    {
        $model = new self();
        return $model->callProcedure('sp_get_admin_directory');
    }

    public static function updateInCompany(
        int $employeeId,
        int $companyId,
        string $fullName,
        ?string $email,
        int $roleId,
        int $seniorityLevel,
        int $isSenior
    ): bool {
        $model = new self();
        $rows = $model->callProcedure('sp_update_employee', [
            'p_employee_id' => $employeeId,
            'p_company_id' => $companyId,
            'p_full_name' => $fullName,
            'p_email' => $email,
            'p_role_id' => $roleId,
            'p_seniority_level' => $seniorityLevel,
            'p_is_senior' => $isSenior,
        ]);

        return (int) ($rows[0]['affected_rows'] ?? 0) > 0;
    }
}
