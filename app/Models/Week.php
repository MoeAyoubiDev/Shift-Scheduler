<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseModel.php';

class Week extends BaseModel
{
    protected string $table = 'weeks';
    protected array $fillable = [
        'week_start_date',
        'week_end_date',
        'is_locked_for_requests',
        'lock_reason',
        'created_at',
    ];
}
