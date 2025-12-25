<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseModel.php';

class ShiftRequest extends BaseModel
{
    protected string $table = 'shift_requests';

    public static function submit(array $payload): int
    {
        $model = new self();
        $rows = $model->callProcedure('sp_submit_shift_request', [
            'p_employee_id' => $payload['employee_id'],
            'p_week_id' => $payload['week_id'],
            'p_request_date' => $payload['request_date'],
            'p_shift_definition_id' => $payload['shift_definition_id'] ?? 0,
            'p_is_day_off' => $payload['is_day_off'] ?? 0,
            'p_schedule_pattern_id' => $payload['schedule_pattern_id'],
            'p_reason' => $payload['reason'] ?? null,
            'p_importance_level' => $payload['importance_level'] ?? 'MEDIUM',
        ]);

        return (int) ($rows[0]['request_id'] ?? 0);
    }

    public static function listByWeek(int $weekId, int $companyId, ?int $employeeId = null): array
    {
        $model = new self();
        return $model->callProcedure('sp_get_shift_requests', [
            'p_week_id' => $weekId,
            'p_company_id' => $companyId,
            'p_employee_id' => $employeeId,
        ]);
    }

    public static function updateStatus(int $requestId, string $status, int $reviewerId): void
    {
        $model = new self();
        $model->callProcedure('sp_update_shift_request_status', [
            'p_request_id' => $requestId,
            'p_status' => $status,
            'p_reviewer_id' => $reviewerId,
        ]);
    }
}
