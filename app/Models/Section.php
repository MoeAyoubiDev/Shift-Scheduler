<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseModel.php';

class Section extends BaseModel
{
    protected string $table = 'sections';
    protected array $fillable = ['section_name'];
}
