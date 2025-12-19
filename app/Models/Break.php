<?php
declare(strict_types=1);

require_once __DIR__ . '/../Core/Database.php';

class Break
{
    private PDO $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    public function start(int $employeeId, string $workedDate, ?int $scheduleShiftId = null): array
    {
        $result = ['success' => false, 'message' => '', 'break_id' => null];
        
        try {
            Database::callProcedure('sp_start_break', [
                $employeeId,
                $workedDate,
                $scheduleShiftId
            ]);
            
            if (!empty($procResult)) {
                $row = $procResult[0];
                if ($row['result'] === 'SUCCESS') {
                    $result['success'] = true;
                    $result['break_id'] = (int) $row['break_id'];
                    $result['message'] = 'Break started successfully';
                } else {
                    $result['message'] = $row['result'] ?? 'Failed to start break';
                }
            }
        } catch (Exception $e) {
            $result['message'] = $e->getMessage();
        }
        
        return $result;
    }
    
    public function end(int $employeeId, string $workedDate): array
    {
        $result = ['success' => false, 'message' => '', 'break_id' => null];
        
        try {
            Database::callProcedure('sp_end_break', [
                $employeeId,
                $workedDate
            ]);
            
            if (!empty($procResult)) {
                $row = $procResult[0];
                if ($row['result'] === 'SUCCESS') {
                    $result['success'] = true;
                    $result['break_id'] = (int) $row['break_id'];
                    $result['message'] = 'Break ended successfully';
                } else {
                    $result['message'] = $row['result'] ?? 'Failed to end break';
                }
            }
        } catch (Exception $e) {
            $result['message'] = $e->getMessage();
        }
        
        return $result;
    }
    
    public function getTodayShift(int $sectionId, string $currentDate): array
    {
        return Database::callProcedure('sp_get_today_shift', [$sectionId, $currentDate]);
    }
    
    public function getDelays(?int $employeeId = null, ?string $startDate = null, ?string $endDate = null): array
    {
        return Database::callProcedure('sp_calculate_delays', [
            $employeeId,
            $startDate,
            $endDate
        ]);
    }
}

