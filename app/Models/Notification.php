<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseModel.php';

class Notification extends BaseModel
{
    protected string $table = 'notifications';
    protected array $fillable = [
        'user_id',
        'type',
        'title',
        'body',
        'is_read',
        'created_at',
        'read_at',
    ];
}
