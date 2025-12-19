<?php
declare(strict_types=1);

require_once __DIR__ . '/../Core/Auth.php';
require_once __DIR__ . '/../Core/CSRF.php';
require_once __DIR__ . '/../Models/ShiftRequest.php';
require_once __DIR__ . '/../Models/Schedule.php';
require_once __DIR__ . '/../Models/Break.php';

class EmployeeController
{
    public static function mySchedule(): void
    {
        Auth::requireRole('Employee');
        
        $user = Auth::user();
        $weekStart = self::getCurrentWeekStart();
        
        require_once __DIR__ . '/../Core/Database.php';
        $db = Database::getInstance();
        
        // Get shift definitions for request form
        $shiftDefs = $db->query("SELECT * FROM shift_definitions WHERE category != 'OFF' ORDER BY start_time")->fetchAll();
        $patterns = $db->query("SELECT * FROM schedule_patterns")->fetchAll();
        
        $schedule = new Schedule();
        $allSchedules = $schedule->getByWeek($weekStart, $user['section_id']);
        
        // Filter to show only this employee's schedule
        $mySchedule = array_filter($allSchedules, function($s) use ($user) {
            return isset($s['employee_id']) && $s['employee_id'] == $user['employee_id'];
        });
        
        // Get employee's shift requests
        $shiftRequest = new ShiftRequest();
        $requests = $shiftRequest->getByEmployee($user['employee_id'], $weekStart);
        
        // Get today's break status
        $currentDate = date('Y-m-d');
        $stmt = $db->prepare("SELECT * FROM employee_breaks WHERE employee_id = ? AND worked_date = ?");
        $stmt->execute([$user['employee_id'], $currentDate]);
        $todayBreak = $stmt->fetch();
        
        require __DIR__ . '/../Views/employee/my-schedule.php';
    }
    
    public static function submitRequest(): void
    {
        Auth::requireRole('Employee');
        
        $user = Auth::user();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /my-schedule.php');
            exit;
        }
        
        if (!CSRF::validateToken($_POST['csrf_token'] ?? null)) {
            $_SESSION['error'] = 'Invalid security token';
            header('Location: /my-schedule.php');
            exit;
        }
        
        $shiftRequest = new ShiftRequest();
        
        $result = $shiftRequest->submit([
            'employee_id' => $user['employee_id'],
            'submit_date' => $_POST['submit_date'],
            'shift_definition_id' => !empty($_POST['shift_definition_id']) ? (int) $_POST['shift_definition_id'] : null,
            'is_day_off' => isset($_POST['is_day_off']) ? 1 : 0,
            'schedule_pattern_id' => (int) $_POST['schedule_pattern_id'],
            'reason' => $_POST['reason'] ?? null,
            'importance_level' => $_POST['importance_level'] ?? 'NORMAL'
        ]);
        
        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
        } else {
            $_SESSION['error'] = $result['message'];
        }
        
        header('Location: /my-schedule.php');
        exit;
    }
    
    public static function manageBreak(): void
    {
        Auth::requireRole('Employee');
        
        $user = Auth::user();
        $currentDate = date('Y-m-d');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /my-schedule.php');
            exit;
        }
        
        if (!CSRF::validateToken($_POST['csrf_token'] ?? null)) {
            $_SESSION['error'] = 'Invalid security token';
            header('Location: /my-schedule.php');
            exit;
        }
        
        $break = new Break();
        $action = $_POST['action'] ?? '';
        
        if ($action === 'start') {
            $scheduleShiftId = !empty($_POST['schedule_shift_id']) ? (int) $_POST['schedule_shift_id'] : null;
            $result = $break->start($user['employee_id'], $currentDate, $scheduleShiftId);
        } elseif ($action === 'end') {
            $result = $break->end($user['employee_id'], $currentDate);
        } else {
            $_SESSION['error'] = 'Invalid action';
            header('Location: /my-schedule.php');
            exit;
        }
        
        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
        } else {
            $_SESSION['error'] = $result['message'];
        }
        
        header('Location: /my-schedule.php');
        exit;
    }
    
    private static function getCurrentWeekStart(): string
    {
        $monday = new DateTime('monday this week');
        return $monday->format('Y-m-d');
    }
}

