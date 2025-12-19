<?php
declare(strict_types=1);

require_once __DIR__ . '/../Core/Database.php';

class Schedule
{
    private PDO $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    public function generate(string $weekStart, int $sectionId, int $generatedByEmployeeId): array
    {
        $result = ['success' => false, 'message' => '', 'schedule_id' => null];
        
        try {
            Database::callProcedure('sp_generate_weekly_schedule', [
                $weekStart,
                $sectionId,
                $generatedByEmployeeId
            ]);
            
            if (!empty($procResult)) {
                $row = $procResult[0];
                if ($row['result'] === 'SUCCESS' || $row['result'] === 'SCHEDULE_EXISTS') {
                    $result['success'] = true;
                    $result['schedule_id'] = (int) $row['schedule_id'];
                    $result['message'] = $row['result'] === 'SCHEDULE_EXISTS' 
                        ? 'Schedule already exists' 
                        : 'Schedule generated successfully';
                } else {
                    $result['message'] = $row['result'] ?? 'Failed to generate schedule';
                }
            }
        } catch (Exception $e) {
            $result['message'] = $e->getMessage();
        }
        
        return $result;
    }
    
    public function getByWeek(string $weekStart, ?int $sectionId = null): array
    {
        return Database::callProcedure('sp_get_schedules', [$weekStart, $sectionId]);
    }
    
    public function updateAssignment(int $scheduleShiftId, int $employeeId, string $action): array
    {
        $result = ['success' => false, 'message' => ''];
        
        try {
            Database::callProcedure('sp_update_schedule_assignment', [
                $scheduleShiftId,
                $employeeId,
                $action
            ]);
            
            if (!empty($procResult)) {
                $row = $procResult[0];
                if ($row['result'] === 'SUCCESS') {
                    $result['success'] = true;
                    $result['message'] = 'Schedule updated successfully';
                } else {
                    $result['message'] = $row['result'] ?? 'Failed to update schedule';
                }
            }
        } catch (Exception $e) {
            $result['message'] = $e->getMessage();
        }
        
        return $result;
    }
    
    public function exportToCSV(string $weekStart, int $sectionId): void
    {
        $schedules = $this->getByWeek($weekStart, $sectionId);
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="schedule-' . $weekStart . '.csv"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, ['Date', 'Shift', 'Start Time', 'End Time', 'Employee Code', 'Employee Name', 'Status']);
        
        foreach ($schedules as $schedule) {
            fputcsv($output, [
                $schedule['date'],
                $schedule['shift_name'],
                $schedule['start_time'],
                $schedule['end_time'],
                $schedule['employee_code'] ?? '',
                $schedule['full_name'] ?? '',
                $schedule['status'] ?? 'DRAFT'
            ]);
        }
        
        fclose($output);
        exit;
    }
}
