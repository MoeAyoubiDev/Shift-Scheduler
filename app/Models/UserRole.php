<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseModel.php';

class UserRole extends BaseModel
{
    protected string $table = 'user_roles';
    protected array $fillable = ['userId', 'role_id', 'section_id'];
}
