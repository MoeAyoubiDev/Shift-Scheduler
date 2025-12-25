<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseModel.php';

class Performance extends BaseModel
{
    public static function report(string $startDate, string $endDate, int $companyId, ?int $employeeId = null): array
    {
        $model = new self();
        return $model->callProcedure('sp_performance_report', [
            'p_start_date' => $startDate,
            'p_end_date' => $endDate,
            'p_company_id' => $companyId,
            'p_employee_id' => $employeeId,
        ]);
    }

    public static function supervisorDashboard(int $companyId, int $weekId): array
    {
        $model = new self();
        return $model->callProcedure('sp_supervisor_dashboard', [
            'p_company_id' => $companyId,
            'p_week_id' => $weekId,
        ]);
    }
}
