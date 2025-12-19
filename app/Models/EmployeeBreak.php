<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseModel.php';

class EmployeeBreak extends BaseModel
{
    protected string $table = 'employee_breaks';
    protected array $fillable = [
        'employee_id',
        'schedule_shift_id',
        'worked_date',
        'break_start',
        'break_end',
        'is_active',
        'created_at',
    ];
}
