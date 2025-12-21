<?php
declare(strict_types=1);

// Clear opcache only in development mode
// In production, opcache should be managed by PHP-FPM restart
$appEnv = getenv('APP_ENV') ?: 'production';
if ($appEnv === 'development' && function_exists('opcache_reset')) {
    opcache_reset();
    clearstatcache(true);
}

require_once __DIR__ . '/../app/Helpers/helpers.php';
require_once __DIR__ . '/../app/Controllers/AuthController.php';
require_once __DIR__ . '/../app/Controllers/DirectorController.php';
require_once __DIR__ . '/../app/Controllers/TeamLeaderController.php';
require_once __DIR__ . '/../app/Controllers/SupervisorController.php';
require_once __DIR__ . '/../app/Controllers/SeniorController.php';
require_once __DIR__ . '/../app/Controllers/EmployeeController.php';
require_once __DIR__ . '/../app/Models/Schedule.php';
require_once __DIR__ . '/../app/Models/Employee.php';
require_once __DIR__ . '/../app/Models/ShiftRequest.php';
require_once __DIR__ . '/../app/Models/Performance.php';
require_once __DIR__ . '/../app/Models/Break.php';
require_once __DIR__ . '/../app/Models/Role.php';

$message = '';
$today = new DateTimeImmutable();
$weekStart = $today->modify('monday this week')->format('Y-m-d');
$weekEnd = $today->modify('sunday this week')->format('Y-m-d');
$weekId = Schedule::upsertWeek($weekStart, $weekEnd);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'login':
            $username = trim($_POST['username'] ?? '');
            $password = trim($_POST['password'] ?? '');
            $message = AuthController::handleLogin($username, $password, $_POST) ?? $message;
            break;
        case 'logout':
            AuthController::handleLogout($_POST);
            break;
        case 'select_section':
            DirectorController::handleSelectSection($_POST);
            break;
        case 'create_employee':
            $message = TeamLeaderController::handleCreateEmployee($_POST) ?? $message;
            break;
        case 'submit_request':
            $message = EmployeeController::handleSubmitRequest($_POST, $weekId);
            break;
        case 'update_request_status':
            $message = TeamLeaderController::handleUpdateRequestStatus($_POST) ?? $message;
            break;
        case 'save_requirements':
            $sectionId = current_section_id();
            if ($sectionId) {
                $message = TeamLeaderController::handleSaveRequirements($_POST, $weekId, $sectionId);
            }
            break;
        case 'generate_schedule':
            $sectionId = current_section_id();
            if ($sectionId) {
                $message = TeamLeaderController::handleGenerateSchedule($weekId, $sectionId);
            }
            break;
        case 'update_assignment':
            $message = TeamLeaderController::handleUpdateAssignment($_POST);
            break;
        case 'delete_assignment':
            $message = TeamLeaderController::handleDeleteAssignment($_POST);
            break;
        case 'update_employee':
            $message = TeamLeaderController::handleUpdateEmployee($_POST) ?? $message;
            break;
        case 'delete_employee':
            $message = TeamLeaderController::handleDeleteEmployee($_POST) ?? $message;
            break;
        case 'create_leader':
            $message = DirectorController::handleCreateLeader($_POST) ?? $message;
            break;
        case 'start_break':
            if (current_role() === 'Senior') {
                $message = SeniorController::handleBreakAction($_POST, 'start');
            } else {
                $message = EmployeeController::handleBreakAction($_POST, 'start');
            }
            break;
        case 'end_break':
            if (current_role() === 'Senior') {
                $message = SeniorController::handleBreakAction($_POST, 'end');
            } else {
                $message = EmployeeController::handleBreakAction($_POST, 'end');
            }
            break;
    }
}

$user = current_user();
$role = $user['role'] ?? null;
$sectionId = current_section_id();

if (isset($_GET['reset_section']) && $role === 'Director') {
    set_current_section(0);
    header('Location: /index.php');
    exit;
}

if ($user && isset($_GET['download']) && $_GET['download'] === 'schedule' && $role === 'Team Leader') {
    $scheduleRows = $sectionId ? Schedule::getWeeklySchedule($weekId, $sectionId) : [];
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="schedule-' . $weekStart . '.csv"');

    $out = fopen('php://output', 'w');
    fputcsv($out, ['Date', 'Shift', 'Employee', 'Assignment Source', 'Notes']);
    foreach ($scheduleRows as $entry) {
        fputcsv($out, [
            $entry['shift_date'],
            $entry['shift_name'],
            $entry['employee_name'],
            $entry['assignment_source'],
            $entry['notes'],
        ]);
    }
    fclose($out);
    exit;
}

if (!$user) {
    // Force no cache for login page
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    render_view('partials/header', [
        'title' => 'Shift Scheduler Login',
        'message' => $message,
    ]);
    render_view('auth/login');
    render_view('partials/footer');
    exit;
}

render_view('partials/header', [
    'title' => 'Shift Scheduler',
    'message' => $message,
]);

if ($role === 'Director') {
    if (!$sectionId) {
        render_view('director/choose-section', [
            'user' => $user,
        ]);
    } else {
        require_once __DIR__ . '/../app/Models/Section.php';
        require_once __DIR__ . '/../app/Models/Role.php';
        
        $dashboard = Performance::directorDashboard($sectionId, $weekId);
        $schedule = Schedule::getWeeklySchedule($weekId, $sectionId);
        $requests = ShiftRequest::listByWeek($weekId, $sectionId);
        $performance = Performance::report($weekStart, $weekEnd, $sectionId, null);
        $sections = Section::getAll();
        $roles = Role::listRoles();

        render_view('director/dashboard', [
            'user' => $user,
            'weekStart' => $weekStart,
            'weekEnd' => $weekEnd,
            'dashboard' => $dashboard,
            'schedule' => $schedule,
            'requests' => $requests,
            'performance' => $performance,
            'sections' => $sections,
            'roles' => $roles,
        ]);
    }
} elseif ($role === 'Team Leader') {
    $shiftDefinitions = Schedule::getShiftDefinitions();
    $shiftTypes = Schedule::getShiftTypes();
    $roles = Role::listRoles();
    $requirements = $sectionId ? Schedule::getShiftRequirements($weekId, $sectionId) : [];
    $requests = $sectionId ? ShiftRequest::listByWeek($weekId, $sectionId) : [];
    $schedule = $sectionId ? Schedule::getWeeklySchedule($weekId, $sectionId) : [];
    $employees = $sectionId ? Employee::listBySection($sectionId) : [];
    $patterns = Schedule::getSchedulePatterns();
    $performance = $sectionId ? Performance::report($weekStart, $weekEnd, $sectionId, null) : [];
    $breaks = $sectionId ? BreakModel::currentBreaks($sectionId, $today->format('Y-m-d')) : [];

    render_view('teamleader/dashboard', [
        'user' => $user,
        'weekStart' => $weekStart,
        'weekEnd' => $weekEnd,
        'weekId' => $weekId,
        'shiftTypes' => $shiftTypes,
        'shiftDefinitions' => $shiftDefinitions,
        'roles' => $roles,
        'requirements' => $requirements,
        'requests' => $requests,
        'schedule' => $schedule,
        'employees' => $employees,
        'patterns' => $patterns,
        'performance' => $performance,
        'breaks' => $breaks,
    ]);
} elseif ($role === 'Supervisor') {
    $schedule = $sectionId ? Schedule::getWeeklySchedule($weekId, $sectionId) : [];
    $employees = $sectionId ? Employee::listBySection($sectionId) : [];
    $performance = $sectionId ? Performance::report($weekStart, $weekEnd, $sectionId, null) : [];
    $breaks = $sectionId ? BreakModel::currentBreaks($sectionId, $today->format('Y-m-d')) : [];

    render_view('supervisor/dashboard', [
        'user' => $user,
        'weekStart' => $weekStart,
        'weekEnd' => $weekEnd,
        'schedule' => $schedule,
        'employees' => $employees,
        'performance' => $performance,
        'breaks' => $breaks,
    ]);
} elseif ($role === 'Senior') {
    $todaySchedule = $sectionId ? Schedule::getTodaySchedule($sectionId, $today->format('Y-m-d')) : [];
    $breaks = $sectionId ? BreakModel::currentBreaks($sectionId, $today->format('Y-m-d')) : [];
    $weekly = $sectionId ? Schedule::getWeeklySchedule($weekId, $sectionId) : [];

    render_view('senior/dashboard', [
        'user' => $user,
        'today' => $today->format('Y-m-d'),
        'todaySchedule' => $todaySchedule,
        'breaks' => $breaks,
        'weekly' => $weekly,
    ]);
} else {
    // Employee role
    $schedule = $sectionId ? Schedule::getWeeklySchedule($weekId, $sectionId) : [];
    $patterns = Schedule::getSchedulePatterns();
    $shiftDefinitions = Schedule::getShiftDefinitions();
    $myRequests = $sectionId && isset($user['employee_id']) 
        ? ShiftRequest::listByWeek($weekId, $sectionId, (int) $user['employee_id']) 
        : [];
    $myBreak = isset($user['employee_id']) 
        ? BreakModel::getEmployeeBreak((int) $user['employee_id'], $today->format('Y-m-d'))
        : null;

    render_view('employee/dashboard', [
        'user' => $user,
        'weekStart' => $weekStart,
        'weekEnd' => $weekEnd,
        'weekId' => $weekId,
        'schedule' => $schedule,
        'patterns' => $patterns,
        'shiftDefinitions' => $shiftDefinitions,
        'myRequests' => $myRequests,
        'myBreak' => $myBreak,
        'today' => $today->format('Y-m-d'),
    ]);
}

render_view('partials/footer');
