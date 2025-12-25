<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseModel.php';

class BreakModel extends BaseModel
{
    protected string $table = 'employee_breaks';

    public static function start(int $employeeId, string $date, ?int $scheduleShiftId): void
    {
        $model = new self();
        $model->callProcedure('sp_start_break', [
            'p_employee_id' => $employeeId,
            'p_worked_date' => $date,
            'p_schedule_shift_id' => $scheduleShiftId,
        ]);
    }

    public static function end(int $employeeId, string $date): void
    {
        $model = new self();
        $model->callProcedure('sp_end_break', [
            'p_employee_id' => $employeeId,
            'p_worked_date' => $date,
        ]);
    }

    public static function currentBreaks(int $companyId, string $date): array
    {
        $model = new self();
        return $model->callProcedure('sp_get_break_status', [
            'p_company_id' => $companyId,
            'p_today' => $date,
        ]);
    }

    public static function getEmployeeBreak(int $employeeId, string $date): ?array
    {
        $model = new self();
        $rows = $model->callProcedure('sp_get_employee_break', [
            'p_employee_id' => $employeeId,
            'p_worked_date' => $date,
        ], false);

        return $rows[0] ?? null;
    }
}
