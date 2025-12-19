<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseModel.php';

class Employee extends BaseModel
{
    protected string $table = 'employees';

    public static function listBySection(int $sectionId): array
    {
        $model = new self();
        return $model->callProcedure('sp_get_employees_by_section', [
            'p_section_id' => $sectionId,
        ]);
    }

    public static function availableForDate(int $sectionId, string $date): array
    {
        $model = new self();
        return $model->callProcedure('sp_get_available_employees', [
            'p_section_id' => $sectionId,
            'p_date' => $date,
        ]);
    }
}
