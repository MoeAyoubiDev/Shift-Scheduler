<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseModel.php';

class Employee extends BaseModel
{
    protected string $table = 'employees';
    protected array $fillable = [
        'user_rolesId',
        'employee_code',
        'full_name',
        'email',
        'is_senior',
        'seniority_level',
        'is_active',
    ];
}
