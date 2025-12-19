<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseModel.php';

class ShiftRequest extends BaseModel
{
    protected string $table = 'shift_requests';
    protected array $fillable = [
        'employee_id',
        'week_id',
        'SubmitDate',
        'shift_definition_id',
        'is_day_off',
        'schedule_pattern_id',
        'reason',
        'importance_level',
        'status',
        'flagged_as_important',
        'submitted_at',
        'reviewed_by_admin_id',
        'reviewed_at',
    ];
}
