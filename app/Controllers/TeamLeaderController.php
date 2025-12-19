<?php
declare(strict_types=1);

require_once __DIR__ . '/../Core/Auth.php';
require_once __DIR__ . '/../Core/CSRF.php';
require_once __DIR__ . '/../Models/Employee.php';
require_once __DIR__ . '/../Models/ShiftRequest.php';
require_once __DIR__ . '/../Models/Schedule.php';
require_once __DIR__ . '/../Models/Performance.php';
require_once __DIR__ . '/../Models/Break.php';

class TeamLeaderController
{
    public static function dashboard(): void
    {
        Auth::requireRole('Team Leader');
        
        $user = Auth::user();
        $weekStart = self::getCurrentWeekStart();
        
        $shiftRequest = new ShiftRequest();
        $pendingRequests = $shiftRequest->getPendingBySection($user['section_id'], $weekStart);
        
        $schedule = new Schedule();
        $schedules = $schedule->getByWeek($weekStart, $user['section_id']);
        
        require __DIR__ . '/../Views/teamleader/dashboard.php';
    }
    
    public static function employees(): void
    {
        Auth::requireRole('Team Leader');
        
        $user = Auth::user();
        $employee = new Employee();
        $employees = $employee->getAllBySection($user['section_id']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!CSRF::validateToken($_POST['csrf_token'] ?? null)) {
                $_SESSION['error'] = 'Invalid security token';
                header('Location: /employees.php');
                exit;
            }
            
            $action = $_POST['action'] ?? '';
            
            if ($action === 'create') {
                $result = $employee->create([
                    'user_id' => (int) $_POST['user_id'],
                    'role_id' => (int) $_POST['role_id'],
                    'section_id' => $user['section_id'],
                    'employee_code' => $_POST['employee_code'],
                    'full_name' => $_POST['full_name'],
                    'email' => $_POST['email'] ?? null,
                    'is_senior' => isset($_POST['is_senior']) ? 1 : 0,
                    'seniority_level' => (int) ($_POST['seniority_level'] ?? 0)
                ]);
                
                if ($result['success']) {
                    $_SESSION['success'] = 'Employee created successfully';
                } else {
                    $_SESSION['error'] = $result['message'];
                }
            } elseif ($action === 'delete') {
                $employee->delete((int) $_POST['employee_id']);
                $_SESSION['success'] = 'Employee deleted successfully';
            }
            
            header('Location: /employees.php');
            exit;
        }
        
        require __DIR__ . '/../Views/teamleader/employees.php';
    }
    
    public static function shiftRequests(): void
    {
        Auth::requireRole('Team Leader');
        
        $user = Auth::user();
        $weekStart = self::getCurrentWeekStart();
        
        $shiftRequest = new ShiftRequest();
        $requests = $shiftRequest->getPendingBySection($user['section_id'], $weekStart);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!CSRF::validateToken($_POST['csrf_token'] ?? null)) {
                $_SESSION['error'] = 'Invalid security token';
                header('Location: /shift-requests.php');
                exit;
            }
            
            $requestId = (int) ($_POST['request_id'] ?? 0);
            $status = $_POST['status'] ?? '';
            
            if (in_array($status, ['APPROVED', 'DECLINED'])) {
                $result = $shiftRequest->review($requestId, $user['employee_id'], $status);
                
                if ($result['success']) {
                    $_SESSION['success'] = $result['message'];
                } else {
                    $_SESSION['error'] = $result['message'];
                }
            }
            
            header('Location: /shift-requests.php');
            exit;
        }
        
        require __DIR__ . '/../Views/teamleader/shift-requests.php';
    }
    
    public static function schedule(): void
    {
        Auth::requireRole('Team Leader');
        
        $user = Auth::user();
        $weekStart = self::getCurrentWeekStart();
        
        $schedule = new Schedule();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!CSRF::validateToken($_POST['csrf_token'] ?? null)) {
                $_SESSION['error'] = 'Invalid security token';
                header('Location: /schedule.php');
                exit;
            }
            
            $action = $_POST['action'] ?? '';
            
            if ($action === 'generate') {
                $result = $schedule->generate($weekStart, $user['section_id'], $user['employee_id']);
                
                if ($result['success']) {
                    $_SESSION['success'] = $result['message'];
                } else {
                    $_SESSION['error'] = $result['message'];
                }
            } elseif ($action === 'update') {
                $result = $schedule->updateAssignment(
                    (int) $_POST['schedule_shift_id'],
                    (int) $_POST['employee_id'],
                    $_POST['update_action']
                );
                
                if ($result['success']) {
                    $_SESSION['success'] = $result['message'];
                } else {
                    $_SESSION['error'] = $result['message'];
                }
            } elseif ($action === 'export') {
                $schedule->exportToCSV($weekStart, $user['section_id']);
                exit;
            }
            
            header('Location: /schedule.php');
            exit;
        }
        
        $schedules = $schedule->getByWeek($weekStart, $user['section_id']);
        $employee = new Employee();
        $employees = $employee->getAllBySection($user['section_id']);
        
        require __DIR__ . '/../Views/teamleader/schedule.php';
    }
    
    public static function performance(): void
    {
        Auth::requireRole('Team Leader');
        
        $user = Auth::user();
        $performance = new Performance();
        
        $filters = [
            'section_id' => $user['section_id'],
            'employee_id' => !empty($_GET['employee_id']) ? (int) $_GET['employee_id'] : null,
            'start_date' => $_GET['start_date'] ?? null,
            'end_date' => $_GET['end_date'] ?? null,
            'month' => $_GET['month'] ?? null
        ];
        
        // If month is provided, calculate date range
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
        
        $employee = new Employee();
        $employees = $employee->getAllBySection($user['section_id']);
        
        require __DIR__ . '/../Views/teamleader/performance.php';
    }
    
    public static function breaks(): void
    {
        Auth::requireRole('Team Leader');
        
        $user = Auth::user();
        $break = new Break();
        $currentDate = date('Y-m-d');
        
        $todayShift = $break->getTodayShift($user['section_id'], $currentDate);
        
        require __DIR__ . '/../Views/teamleader/breaks.php';
    }
    
    private static function getCurrentWeekStart(): string
    {
        $monday = new DateTime('monday this week');
        return $monday->format('Y-m-d');
    }
}

