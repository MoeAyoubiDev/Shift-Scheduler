<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseModel.php';

class SchedulePattern extends BaseModel
{
    protected string $table = 'schedule_patterns';
    protected array $fillable = [
        'names',
        'work_days_per_week',
        'off_days_per_week',
        'default_shift_duration_hours',
        'description',
    ];
}
