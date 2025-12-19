<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseModel.php';

class ShiftRequirement extends BaseModel
{
    protected string $table = 'shift_requirements';
    protected array $fillable = ['week_id', 'date', 'shift_type_id', 'required_count'];
}
