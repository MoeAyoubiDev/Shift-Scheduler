<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseModel.php';

class ShiftType extends BaseModel
{
    protected string $table = 'shift_types';
    protected array $fillable = ['code', 'name', 'start_time', 'end_time', 'duration_hours'];
}
