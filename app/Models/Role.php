<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseModel.php';

class Role extends BaseModel
{
    protected string $table = 'roles';
    protected array $fillable = ['role_name', 'description'];

    public static function listRoles(): array
    {
        $model = new self();
        return $model->callProcedure('sp_get_roles');
    }
}
