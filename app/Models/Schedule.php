<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseModel.php';

class Schedule extends BaseModel
{
    protected string $table = 'schedules';
    protected array $fillable = [
        'week_id',
        'generated_by_admin_id',
        'generated_at',
        'status',
        'excel_file_path',
        'notes',
    ];
}
