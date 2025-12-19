<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseModel.php';

class SystemSetting extends BaseModel
{
    protected string $table = 'system_settings';
    protected array $fillable = ['Systemkey', 'Svalue', 'descriptions'];
}
