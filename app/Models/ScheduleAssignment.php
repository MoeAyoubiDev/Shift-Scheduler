<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseModel.php';

class ScheduleAssignment extends BaseModel
{
    protected string $table = 'schedule_assignments';
    protected array $fillable = [
        'schedule_shift_id',
        'employee_id',
        'assignment_source',
        'is_senior',
        'created_at',
    ];
}
