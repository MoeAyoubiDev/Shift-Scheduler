<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseModel.php';

class ShiftDefinition extends BaseModel
{
    protected string $table = 'shift_definitions';
    protected array $fillable = [
        'shiftName',
        'start_time',
        'end_time',
        'duration_hours',
        'category',
        'color_code',
        'shift_type_id',
    ];
}
