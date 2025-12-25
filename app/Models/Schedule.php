<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseModel.php';

class Schedule extends BaseModel
{
    protected string $table = 'schedules';

    public static function upsertWeek(string $weekStart, string $weekEnd, ?int $companyId = null): int
    {
        $model = new self();
        
        // Get company_id from session if not provided
        if ($companyId === null) {
            require_once __DIR__ . '/../Helpers/helpers.php';
            $companyId = current_company_id();
            if ($companyId === null) {
                throw new RuntimeException('Company ID is required for upsertWeek');
            }
        }
        
        try {
            $rows = $model->callProcedure('sp_upsert_week', [
                'p_company_id' => $companyId,
                'p_week_start' => $weekStart,
                'p_week_end' => $weekEnd,
            ]);

            return (int) ($rows[0]['week_id'] ?? 0);
        } catch (PDOException $e) {
            $errorMsg = $e->getMessage();
            throw new RuntimeException(
                "Stored procedure failure in sp_upsert_week. Please run: mysql < database/shift_scheduler.sql. Error: " . $errorMsg
            );
        }
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

    public static function getShiftRequirements(int $weekId, ?int $companyId = null): array
    {
        $model = new self();
        if ($companyId === null) {
            require_once __DIR__ . '/../Helpers/helpers.php';
            $companyId = current_company_id();
            if ($companyId === null) {
                throw new RuntimeException('Company ID is required for getShiftRequirements');
            }
        }
        return $model->callProcedure('sp_get_shift_requirements', [
            'p_week_id' => $weekId,
            'p_company_id' => $companyId,
        ]);
    }

    public static function saveShiftRequirement(int $weekId, string $date, int $shiftTypeId, int $requiredCount, ?int $companyId = null): void
    {
        $model = new self();
        if ($companyId === null) {
            require_once __DIR__ . '/../Helpers/helpers.php';
            $companyId = current_company_id();
            if ($companyId === null) {
                throw new RuntimeException('Company ID is required for saveShiftRequirement');
            }
        }
        $model->callProcedure('sp_set_shift_requirement', [
            'p_week_id' => $weekId,
            'p_company_id' => $companyId,
            'p_date' => $date,
            'p_shift_type_id' => $shiftTypeId,
            'p_required_count' => $requiredCount,
        ]);
    }

    public static function generateWeekly(int $weekId, int $generatedByEmployeeId, ?int $companyId = null): void
    {
        $model = new self();
        if ($companyId === null) {
            require_once __DIR__ . '/../Helpers/helpers.php';
            $companyId = current_company_id();
            if ($companyId === null) {
                throw new RuntimeException('Company ID is required for generateWeekly');
            }
        }
        $model->callProcedure('sp_generate_weekly_schedule', [
            'p_week_id' => $weekId,
            'p_company_id' => $companyId,
            'p_generated_by_employee_id' => $generatedByEmployeeId,
        ]);
    }

    public static function getWeeklySchedule(int $weekId, ?int $companyId = null): array
    {
        $model = new self();
        if ($companyId === null) {
            require_once __DIR__ . '/../Helpers/helpers.php';
            $companyId = current_company_id();
            if ($companyId === null) {
                throw new RuntimeException('Company ID is required for getWeeklySchedule');
            }
        }
        return $model->callProcedure('sp_get_weekly_schedule', [
            'p_week_id' => $weekId,
            'p_company_id' => $companyId,
        ]);
    }

    public static function getTodaySchedule(string $date, ?int $companyId = null): array
    {
        $model = new self();
        if ($companyId === null) {
            require_once __DIR__ . '/../Helpers/helpers.php';
            $companyId = current_company_id();
            if ($companyId === null) {
                throw new RuntimeException('Company ID is required for getTodaySchedule');
            }
        }
        return $model->callProcedure('sp_get_today_shift', [
            'p_company_id' => $companyId,
            'p_today' => $date,
        ]);
    }

    public static function getCoverageGaps(int $weekId, ?int $companyId = null): array
    {
        $model = new self();
        if ($companyId === null) {
            require_once __DIR__ . '/../Helpers/helpers.php';
            $companyId = current_company_id();
            if ($companyId === null) {
                throw new RuntimeException('Company ID is required for getCoverageGaps');
            }
        }
        return $model->callProcedure('sp_get_coverage_gaps', [
            'p_week_id' => $weekId,
            'p_company_id' => $companyId,
        ]);
    }

    public static function updateAssignment(int $assignmentId, int $shiftDefinitionId, ?int $employeeId = null): void
    {
        $model = new self();
        $model->callProcedure('sp_update_schedule_assignment', [
            'p_assignment_id' => $assignmentId,
            'p_shift_definition_id' => $shiftDefinitionId,
            'p_employee_id' => $employeeId,
        ]);
    }

    public static function deleteAssignment(int $assignmentId): void
    {
        $model = new self();
        $model->callProcedure('sp_delete_schedule_assignment', [
            'p_assignment_id' => $assignmentId,
        ]);
    }
}
