<?php
declare(strict_types=1);

require_once __DIR__ . '/../Core/Auth.php';
require_once __DIR__ . '/../Models/Schedule.php';
require_once __DIR__ . '/../Models/Performance.php';
require_once __DIR__ . '/../Models/Break.php';

class SupervisorController
{
    public static function dashboard(): void
    {
        Auth::requireRole('Supervisor');
        
        $user = Auth::user();
        $weekStart = self::getCurrentWeekStart();
        
        $schedule = new Schedule();
        $schedules = $schedule->getByWeek($weekStart, $user['section_id']);
        
        require __DIR__ . '/../Views/supervisor/dashboard.php';
    }
    
    public static function schedule(): void
    {
        Auth::requireRole('Supervisor');
        
        $user = Auth::user();
        $weekStart = self::getCurrentWeekStart();
        
        $schedule = new Schedule();
        $schedules = $schedule->getByWeek($weekStart, $user['section_id']);
        
        require __DIR__ . '/../Views/supervisor/schedule.php';
    }
    
    public static function performance(): void
    {
        Auth::requireRole('Supervisor');
        
        $user = Auth::user();
        $performance = new Performance();
        
        $filters = [
            'section_id' => $user['section_id'],
            'employee_id' => !empty($_GET['employee_id']) ? (int) $_GET['employee_id'] : null,
            'start_date' => $_GET['start_date'] ?? null,
            'end_date' => $_GET['end_date'] ?? null,
            'month' => $_GET['month'] ?? null
        ];
        
        if ($filters['month']) {
            $monthDate = new DateTime($filters['month'] . '-01');
            $filters['start_date'] = $monthDate->format('Y-m-01');
            $filters['end_date'] = $monthDate->format('Y-m-t');
        }
        
        $report = $performance->getReport(
            $filters['section_id'],
            $filters['employee_id'],
            $filters['start_date'],
            $filters['end_date']
        );
        
        require __DIR__ . '/../Views/supervisor/performance.php';
    }
    
    public static function breaks(): void
    {
        Auth::requireRole('Supervisor');
        
        $user = Auth::user();
        $break = new Break();
        $currentDate = date('Y-m-d');
        
        $todayShift = $break->getTodayShift($user['section_id'], $currentDate);
        
        require __DIR__ . '/../Views/supervisor/breaks.php';
    }
    
    private static function getCurrentWeekStart(): string
    {
        $monday = new DateTime('monday this week');
        return $monday->format('Y-m-d');
    }
}

