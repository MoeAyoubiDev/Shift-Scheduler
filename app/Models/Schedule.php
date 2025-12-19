<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseModel.php';

class Schedule extends BaseModel
{
    protected string $table = 'schedules';

    public static function upsertWeek(string $weekStart, string $weekEnd): int
    {
        $model = new self();
        $rows = $model->callProcedure('sp_upsert_week', [
            'p_week_start' => $weekStart,
            'p_week_end' => $weekEnd,
        ]);

        return (int) ($rows[0]['week_id'] ?? 0);
    }

    public static function getShiftTypes(): array
    {
        $model = new self();
        return $model->callProcedure('sp_get_shift_types');
    }

    public static function getShiftDefinitions(): array
    {
        $model = new self();
        return $model->callProcedure('sp_get_shift_definitions');
    }

    public static function getSchedulePatterns(): array
    {
        $model = new self();
        return $model->callProcedure('sp_get_schedule_patterns');
    }

    public static function getShiftRequirements(int $weekId, int $sectionId): array
    {
        $model = new self();
        return $model->callProcedure('sp_get_shift_requirements', [
            'p_week_id' => $weekId,
            'p_section_id' => $sectionId,
        ]);
    }

    public static function saveShiftRequirement(int $weekId, int $sectionId, string $date, int $shiftTypeId, int $requiredCount): void
    {
        $model = new self();
        $model->callProcedure('sp_set_shift_requirement', [
            'p_week_id' => $weekId,
            'p_section_id' => $sectionId,
            'p_date' => $date,
            'p_shift_type_id' => $shiftTypeId,
            'p_required_count' => $requiredCount,
        ]);
    }

    public static function generateWeekly(int $weekId, int $sectionId, int $generatedByEmployeeId): void
    {
        $model = new self();
        $model->callProcedure('sp_generate_weekly_schedule', [
            'p_week_id' => $weekId,
            'p_section_id' => $sectionId,
            'p_generated_by_employee_id' => $generatedByEmployeeId,
        ]);
    }

    public static function getWeeklySchedule(int $weekId, int $sectionId): array
    {
        $model = new self();
        return $model->callProcedure('sp_get_weekly_schedule', [
            'p_week_id' => $weekId,
            'p_section_id' => $sectionId,
        ]);
    }

    public static function getTodaySchedule(int $sectionId, string $date): array
    {
        $model = new self();
        return $model->callProcedure('sp_get_today_shift', [
            'p_section_id' => $sectionId,
            'p_today' => $date,
        ]);
    }

    public static function updateAssignment(int $assignmentId, int $shiftDefinitionId): void
    {
        $model = new self();
        $model->callProcedure('sp_update_schedule_assignment', [
            'p_assignment_id' => $assignmentId,
            'p_shift_definition_id' => $shiftDefinitionId,
        ]);
    }
}
