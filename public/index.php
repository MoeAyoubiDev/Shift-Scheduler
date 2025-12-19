<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';

// Simple routing
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = rtrim($path, '/') ?: '/';

// Redirect to login if not authenticated
if (!Auth::check() && $path !== '/login.php') {
    header('Location: /login.php');
    exit;
}

// Route based on path
switch ($path) {
    case '/':
    case '/login.php':
        require_once __DIR__ . '/../app/Controllers/AuthController.php';
        AuthController::login();
        break;
        
    case '/logout.php':
        require_once __DIR__ . '/../app/Controllers/AuthController.php';
        AuthController::logout();
        break;
        
    case '/choose-section.php':
        require_once __DIR__ . '/../app/Controllers/AuthController.php';
        AuthController::chooseSection();
        break;
        
    case '/dashboard.php':
        $user = Auth::user();
        $role = $user['role'];
        
        if ($role === 'Director') {
            require_once __DIR__ . '/../app/Controllers/DirectorController.php';
            DirectorController::dashboard();
        } elseif ($role === 'Team Leader') {
            require_once __DIR__ . '/../app/Controllers/TeamLeaderController.php';
            TeamLeaderController::dashboard();
        } elseif ($role === 'Supervisor') {
            require_once __DIR__ . '/../app/Controllers/SupervisorController.php';
            SupervisorController::dashboard();
        } else {
            header('Location: /my-schedule.php');
            exit;
        }
        break;
        
    case '/employees.php':
        require_once __DIR__ . '/../app/Controllers/TeamLeaderController.php';
        TeamLeaderController::employees();
        break;
        
    case '/shift-requests.php':
        require_once __DIR__ . '/../app/Controllers/TeamLeaderController.php';
        TeamLeaderController::shiftRequests();
        break;
        
    case '/schedule.php':
        require_once __DIR__ . '/../app/Controllers/TeamLeaderController.php';
        TeamLeaderController::schedule();
        break;
        
    case '/performance.php':
        require_once __DIR__ . '/../app/Controllers/TeamLeaderController.php';
        TeamLeaderController::performance();
        break;
        
    case '/breaks.php':
        require_once __DIR__ . '/../app/Controllers/TeamLeaderController.php';
        TeamLeaderController::breaks();
        break;
        
    case '/today-shift.php':
        require_once __DIR__ . '/../app/Controllers/SeniorController.php';
        SeniorController::todayShift();
        break;
        
    case '/weekly-schedule.php':
        require_once __DIR__ . '/../app/Controllers/SeniorController.php';
        SeniorController::weeklySchedule();
        break;
        
    case '/my-schedule.php':
        require_once __DIR__ . '/../app/Controllers/EmployeeController.php';
        EmployeeController::mySchedule();
        break;
        
    case '/submit-request.php':
        require_once __DIR__ . '/../app/Controllers/EmployeeController.php';
        EmployeeController::submitRequest();
        break;
        
    case '/manage-break.php':
        require_once __DIR__ . '/../app/Controllers/EmployeeController.php';
        EmployeeController::manageBreak();
        break;
        
    default:
        // Handle dynamic routes
        if (preg_match('#^/director/section/(\d+)$#', $path, $matches)) {
            require_once __DIR__ . '/../app/Controllers/DirectorController.php';
            DirectorController::viewSection((int) $matches[1]);
            break;
        }
        
        // Handle supervisor routes
        if (preg_match('#^/supervisor/(schedule|performance|breaks)\.php$#', $path, $matches)) {
            require_once __DIR__ . '/../app/Controllers/SupervisorController.php';
            $method = $matches[1];
            SupervisorController::$method();
            break;
        }
        
        http_response_code(404);
        echo "404 Not Found";
        break;
}
