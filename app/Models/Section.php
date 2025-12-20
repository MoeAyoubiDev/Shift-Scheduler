<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseModel.php';

class Section extends BaseModel
{
    protected string $table = 'sections';
    protected array $fillable = ['section_name'];

    public static function getAll(): array
    {
        $model = new self();
        return $model->callProcedure('sp_get_all_sections');
    }
}
