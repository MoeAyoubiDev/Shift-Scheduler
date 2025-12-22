<?php
declare(strict_types=1);

/**
 * Main Application Entry Point
 * Clean, robust routing and action handling system
 */

// Clear opcache only in development mode
$appEnv = getenv('APP_ENV') ?: 'production';
if ($appEnv === 'development' && function_exists('opcache_reset')) {
    opcache_reset();
    clearstatcache(true);
}

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load core dependencies
require_once __DIR__ . '/../includes/middleware.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../app/Helpers/helpers.php';
require_once __DIR__ . '/../app/Core/Router.php';
require_once __DIR__ . '/../app/Core/ActionHandler.php';
require_once __DIR__ . '/../app/Models/Schedule.php';

// Initialize week data
$today = new DateTimeImmutable();
$weekStart = $today->modify('monday this week')->format('Y-m-d');
$weekEnd = $today->modify('sunday this week')->format('Y-m-d');
$weekId = Schedule::upsertWeek($weekStart, $weekEnd);

// Initialize action handlers
ActionHandler::initialize($weekId);

// Process POST requests
$message = '';
$redirectUrl = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = ActionHandler::process($_POST, [
        'weekId' => $weekId,
        'weekStart' => $weekStart,
        'weekEnd' => $weekEnd,
    ]);
    
    // Handle redirects
    if (!empty($result['redirect'])) {
        $redirectUrl = $result['redirect'];
        if (!empty($result['message'])) {
            $redirectUrl .= (strpos($redirectUrl, '?') !== false ? '&' : '?') . 'message=' . urlencode($result['message']);
        }
        header('Location: ' . $redirectUrl);
        exit;
    }
    
    // Handle AJAX requests
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
             strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }
    
    // Store message for display
    $message = $result['message'] ?? '';
}

// Get message from URL if present (for redirects after form submissions)
if (empty($message) && isset($_GET['message'])) {
    $message = urldecode($_GET['message']);
}

// Handle CSV export
$user = current_user();
$role = $user['role'] ?? null;
$sectionId = current_section_id();

if ($user && isset($_GET['download']) && $_GET['download'] === 'schedule' && $role === 'Team Leader') {
    $exportWeekStart = $_GET['week_start'] ?? $weekStart;
    $exportWeekEnd = $_GET['week_end'] ?? $weekEnd;
    $exportWeekId = Schedule::upsertWeek($exportWeekStart, $exportWeekEnd);
    $scheduleRows = $sectionId ? Schedule::getWeeklySchedule($exportWeekId, $sectionId) : [];
    
    require_once __DIR__ . '/../app/Models/Employee.php';
    $allEmployees = $sectionId ? Employee::listBySection($sectionId) : [];
    
    // Transform to employee-based format
    $employeeSchedule = [];
    $employeeHours = [];
    
    foreach ($scheduleRows as $entry) {
        if (empty($entry['employee_id']) || empty($entry['shift_date'])) {
            continue;
        }
        $empId = (int) $entry['employee_id'];
        $date = $entry['shift_date'];
        
        if (!isset($employeeSchedule[$empId])) {
            $employeeSchedule[$empId] = [];
        }
        if (!isset($employeeSchedule[$empId][$date])) {
            $employeeSchedule[$empId][$date] = [];
        }
        
        $employeeSchedule[$empId][$date][] = [
            'shift_name' => $entry['shift_name'] ?? '',
            'start_time' => $entry['start_time'] ?? '',
            'end_time' => $entry['end_time'] ?? '',
            'notes' => $entry['notes'] ?? '',
        ];
        
        if (!isset($employeeHours[$empId])) {
            $employeeHours[$empId] = 0;
        }
        $duration = $entry['duration_hours'] ?? 8.0;
        $employeeHours[$empId] += (float) $duration;
    }
    
    // Generate week dates
    $weekDates = [];
    $startDate = new DateTimeImmutable($exportWeekStart);
    for ($i = 0; $i < 7; $i++) {
        $date = $startDate->modify('+' . $i . ' day');
        $weekDates[] = $date->format('Y-m-d');
    }
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="schedule-' . $exportWeekStart . '-to-' . $exportWeekEnd . '.csv"');

    $out = fopen('php://output', 'w');
    
    // Header row
    $header = ['Employee', 'Hours This Week'];
    foreach ($weekDates as $date) {
        $dateObj = new DateTimeImmutable($date);
        $header[] = $dateObj->format('D j M');
    }
    fputcsv($out, $header);
    
    // Employee rows
    foreach ($allEmployees as $employee) {
        $empId = (int) $employee['id'];
        $row = [
            $employee['full_name'],
            number_format($employeeHours[$empId] ?? 0, 1),
        ];
        
        foreach ($weekDates as $date) {
            $dayShifts = $employeeSchedule[$empId][$date] ?? [];
            if (empty($dayShifts)) {
                $row[] = '';
            } else {
                $shiftStrings = [];
                foreach ($dayShifts as $shift) {
                    if (!empty($shift['notes'])) {
                        $notes = strtolower($shift['notes']);
                        if (strpos($notes, 'vacation') !== false) {
                            $shiftStrings[] = 'Vacation';
                        } elseif (strpos($notes, 'medical') !== false || strpos($notes, 'leave') !== false) {
                            $shiftStrings[] = 'Medical Leave';
                        } elseif (strpos($notes, 'moving') !== false) {
                            $shiftStrings[] = 'Moving';
                        } else {
                            $timeStr = '';
                            if (!empty($shift['start_time']) && !empty($shift['end_time'])) {
                                $timeStr = date('H:i', strtotime($shift['start_time'])) . '-' . date('H:i', strtotime($shift['end_time']));
                            }
                            $shiftStrings[] = $shift['shift_name'] . ($timeStr ? ' (' . $timeStr . ')' : '');
                        }
                    } else {
                        $timeStr = '';
                        if (!empty($shift['start_time']) && !empty($shift['end_time'])) {
                            $timeStr = date('H:i', strtotime($shift['start_time'])) . '-' . date('H:i', strtotime($shift['end_time']));
                        }
                        $shiftStrings[] = $shift['shift_name'] . ($timeStr ? ' (' . $timeStr . ')' : '');
                    }
                }
                $row[] = implode(' / ', $shiftStrings);
            }
        }
        
        fputcsv($out, $row);
    }
    
    fclose($out);
    exit;
}

// Handle section reset for Director
if (isset($_GET['reset_section']) && $role === 'Director') {
    set_current_section(0);
    header('Location: /index.php');
    exit;
}

// Render login page if not authenticated
if (!$user) {
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    $title = 'Shift Scheduler Login';
    require_once __DIR__ . '/../includes/header.php';
    render_view('auth/login');
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

// Render dashboard based on role
require_once __DIR__ . '/../includes/header.php';

if ($role === 'Director') {
    if (!$sectionId) {
        render_view('director/choose-section', [
            'user' => $user,
        ]);
    } else {
        require_once __DIR__ . '/../app/Models/Section.php';
        require_once __DIR__ . '/../app/Models/Role.php';
        require_once __DIR__ . '/../app/Models/Performance.php';
        require_once __DIR__ . '/../app/Models/ShiftRequest.php';
        
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
    require_once __DIR__ . '/../app/Models/Employee.php';
    require_once __DIR__ . '/../app/Models/ShiftRequest.php';
    require_once __DIR__ . '/../app/Models/Performance.php';
    require_once __DIR__ . '/../app/Models/Break.php';
    require_once __DIR__ . '/../app/Models/Role.php';
    
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
    require_once __DIR__ . '/../app/Models/Employee.php';
    require_once __DIR__ . '/../app/Models/Performance.php';
    require_once __DIR__ . '/../app/Models/Break.php';
    
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
    require_once __DIR__ . '/../app/Models/Break.php';
    
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
    require_once __DIR__ . '/../app/Models/ShiftRequest.php';
    require_once __DIR__ . '/../app/Models/Break.php';
    
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

require_once __DIR__ . '/../includes/footer.php';
