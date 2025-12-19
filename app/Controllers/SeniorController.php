<?php
declare(strict_types=1);

require_once __DIR__ . '/../Core/Auth.php';
require_once __DIR__ . '/../Core/CSRF.php';
require_once __DIR__ . '/../Models/Break.php';
require_once __DIR__ . '/../Models/Schedule.php';

class SeniorController
{
    public static function todayShift(): void
    {
        Auth::requireRole('Senior');
        
        $user = Auth::user();
        $currentDate = date('Y-m-d');
        
        $break = new Break();
        $todayShift = $break->getTodayShift($user['section_id'], $currentDate);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!CSRF::validateToken($_POST['csrf_token'] ?? null)) {
                $_SESSION['error'] = 'Invalid security token';
                header('Location: /today-shift.php');
                exit;
            }
            
            $action = $_POST['action'] ?? '';
            $employeeId = (int) ($_POST['employee_id'] ?? 0);
            
            if ($action === 'start_break') {
                $scheduleShiftId = !empty($_POST['schedule_shift_id']) ? (int) $_POST['schedule_shift_id'] : null;
                $result = $break->start($employeeId, $currentDate, $scheduleShiftId);
                
                if ($result['success']) {
                    $_SESSION['success'] = $result['message'];
                } else {
                    $_SESSION['error'] = $result['message'];
                }
            } elseif ($action === 'end_break') {
                $result = $break->end($employeeId, $currentDate);
                
                if ($result['success']) {
                    $_SESSION['success'] = $result['message'];
                } else {
                    $_SESSION['error'] = $result['message'];
                }
            }
            
            header('Location: /today-shift.php');
            exit;
        }
        
        require __DIR__ . '/../Views/senior/today-shift.php';
    }
    
    public static function weeklySchedule(): void
    {
        Auth::requireRole('Senior');
        
        $user = Auth::user();
        $weekStart = self::getCurrentWeekStart();
        
        $schedule = new Schedule();
        $schedules = $schedule->getByWeek($weekStart, $user['section_id']);
        
        require __DIR__ . '/../Views/senior/weekly-schedule.php';
    }
    
    private static function getCurrentWeekStart(): string
    {
        $monday = new DateTime('monday this week');
        return $monday->format('Y-m-d');
    }
}

