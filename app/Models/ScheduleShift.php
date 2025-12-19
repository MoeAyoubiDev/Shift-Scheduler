<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseModel.php';

class ScheduleShift extends BaseModel
{
    protected string $table = 'schedule_shifts';
    protected array $fillable = [
        'schedule_id',
        'date',
        'shift_definition_id',
        'required_count',
        'created_at',
    ];
}
