<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseModel.php';

class Schedule extends BaseModel
{
    protected string $table = 'schedules';

    public static function upsertWeek(string $weekStart, string $weekEnd): int
    {
        $model = new self();
        
        try {
            $rows = $model->callProcedure('sp_upsert_week', [
                'p_week_start' => $weekStart,
                'p_week_end' => $weekEnd,
            ]);

            return (int) ($rows[0]['week_id'] ?? 0);
        } catch (PDOException $e) {
            // Fallback: If stored procedure doesn't exist or has an error, use direct SQL
            $errorMsg = $e->getMessage();
            $isProcedureError = (
                strpos($errorMsg, 'does not exist') !== false || 
                strpos($errorMsg, 'Unknown procedure') !== false ||
                strpos($errorMsg, 'PROCEDURE') !== false ||
                strpos($errorMsg, 'routine') !== false ||
                $e->getCode() == '42000' // SQL syntax error/access violation
            );
            
            if ($isProcedureError) {
                try {
                    $pdo = db();
                    
                    // Check if weeks table exists
                    $stmt = $pdo->query("SHOW TABLES LIKE 'weeks'");
                    if ($stmt->rowCount() === 0) {
                        throw new RuntimeException("The 'weeks' table does not exist. Please run the database setup script: php database/setup.php");
                    }
                    
                    // Try to find existing week
                    $stmt = $pdo->prepare('SELECT id FROM weeks WHERE week_start_date = ? LIMIT 1');
                    $stmt->execute([$weekStart]);
                    $week = $stmt->fetch();
                    
                    if ($week) {
                        return (int) $week['id'];
                    }
                    
                    // Insert new week (company_id can be NULL for now)
                    $stmt = $pdo->prepare('INSERT INTO weeks (week_start_date, week_end_date, company_id) VALUES (?, ?, NULL)');
                    $stmt->execute([$weekStart, $weekEnd]);
                    $weekId = (int) $pdo->lastInsertId();
                    
                    if ($weekId <= 0) {
                        throw new RuntimeException("Failed to insert week record. lastInsertId returned 0.");
                    }
                    
                    return $weekId;
                } catch (PDOException $fallbackError) {
                    // If fallback also fails, provide a helpful error message
                    throw new RuntimeException(
                        "Database error in fallback: " . $fallbackError->getMessage() . 
                        " (Original error: " . $errorMsg . ")"
                    );
                }
            }
            
            // Re-throw if it's a different error
            throw $e;
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

    public static function getCoverageGaps(int $weekId, int $sectionId): array
    {
        $stmt = db()->prepare(
            'SELECT ss.shift_date, sd.shift_name, ss.required_count, COUNT(sa.id) AS assigned_count
             FROM schedules s
             INNER JOIN schedule_shifts ss ON ss.schedule_id = s.id
             INNER JOIN shift_definitions sd ON sd.id = ss.shift_definition_id
             LEFT JOIN schedule_assignments sa ON sa.schedule_shift_id = ss.id
             WHERE s.week_id = :week_id AND s.section_id = :section_id
             GROUP BY ss.id
             HAVING assigned_count < ss.required_count'
        );
        $stmt->execute([
            'week_id' => $weekId,
            'section_id' => $sectionId,
        ]);

        return $stmt->fetchAll();
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
