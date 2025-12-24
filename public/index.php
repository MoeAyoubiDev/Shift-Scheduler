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

try {
    $weekId = Schedule::upsertWeek($weekStart, $weekEnd);
} catch (Exception $e) {
    error_log("Error in Schedule::upsertWeek: " . $e->getMessage());
    if ($appEnv === 'development') {
        die("Database Error: " . $e->getMessage() . "\n\nStack trace:\n" . $e->getTraceAsString());
    }
    die("Database error. Please check server logs.");
}

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

// Render landing page or login if not authenticated
if (!$user) {
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Check if this is a login request
    if (isset($_GET['login']) || (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/login') !== false)) {
        header('Location: /login.php');
        exit;
    }
    
    // Show landing page
    require_once __DIR__ . '/../includes/header.php';
    render_view('public/landing');
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
    
    // Calculate metrics for overview
    $pendingRequests = array_filter($requests, fn($r) => $r['status'] === 'PENDING');
    $highPriorityRequests = array_filter($pendingRequests, fn($r) => ($r['importance_level'] ?? 'MEDIUM') === 'HIGH');
    $activeBreaks = array_filter($breaks, fn($b) => $b['status'] === 'ON_BREAK');
    $assignedEmployeeIds = array_unique(array_column($schedule, 'employee_id'));
    $unassignedEmployees = array_filter($employees, fn($e) => !in_array($e['id'], $assignedEmployeeIds));
    
    // Calculate coverage gaps
    $coverageGaps = [];
    foreach ($requirements as $req) {
        $assigned = count(array_filter($schedule, fn($s) => $s['shift_date'] === $req['date'] && $s['shift_name'] === $req['shift_name']));
        if ($assigned < $req['required_count']) {
            $coverageGaps[] = [
                'date' => $req['date'],
                'shift_name' => $req['shift_name'],
                'assigned' => $assigned,
                'required' => $req['required_count'],
            ];
        }
    }

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
        'metrics' => [
            'pending_requests' => count($pendingRequests),
            'high_priority' => count($highPriorityRequests),
            'active_breaks' => count($activeBreaks),
            'unassigned' => count($unassignedEmployees),
            'coverage_gaps' => count($coverageGaps),
        ],
        'pendingRequestsList' => array_slice($pendingRequests, 0, 3),
        'coverageGapsList' => array_slice($coverageGaps, 0, 3),
        'activeBreaksList' => array_slice($activeBreaks, 0, 3),
        'unassignedList' => array_slice($unassignedEmployees, 0, 3),
    ]);
} elseif ($role === 'Supervisor') {
    require_once __DIR__ . '/../app/Models/Employee.php';
    require_once __DIR__ . '/../app/Models/Performance.php';
    require_once __DIR__ . '/../app/Models/Break.php';
    require_once __DIR__ . '/../app/Models/ShiftRequest.php';
    
    $schedule = $sectionId ? Schedule::getWeeklySchedule($weekId, $sectionId) : [];
    $employees = $sectionId ? Employee::listBySection($sectionId) : [];
    $performance = $sectionId ? Performance::report($weekStart, $weekEnd, $sectionId, null) : [];
    $breaks = $sectionId ? BreakModel::currentBreaks($sectionId, $today->format('Y-m-d')) : [];
    $requests = $sectionId ? ShiftRequest::listByWeek($weekId, $sectionId) : [];
    $requirements = $sectionId ? Schedule::getShiftRequirements($weekId, $sectionId) : [];
    
    // Calculate tracking metrics
    $totalEmployees = count($employees);
    $totalShifts = count($schedule);
    $pendingRequests = count(array_filter($requests, fn($r) => $r['status'] === 'PENDING'));
    $activeBreaks = count(array_filter($breaks, fn($b) => $b['status'] === 'ON_BREAK'));
    $totalBreakDelays = array_sum(array_column($breaks, 'delay_minutes'));
    $avgDelay = count($breaks) > 0 ? round($totalBreakDelays / count($breaks), 1) : 0;
    $coverageGaps = 0;
    foreach ($requirements as $req) {
        $assigned = count(array_filter($schedule, fn($s) => $s['shift_date'] === $req['date'] && $s['shift_name'] === $req['shift_name']));
        if ($assigned < $req['required_count']) {
            $coverageGaps += ($req['required_count'] - $assigned);
        }
    }

    render_view('supervisor/dashboard', [
        'user' => $user,
        'weekStart' => $weekStart,
        'weekEnd' => $weekEnd,
        'weekId' => $weekId,
        'schedule' => $schedule,
        'employees' => $employees,
        'performance' => $performance,
        'breaks' => $breaks,
        'requests' => $requests,
        'requirements' => $requirements,
        'metrics' => [
            'total_employees' => $totalEmployees,
            'total_shifts' => $totalShifts,
            'pending_requests' => $pendingRequests,
            'active_breaks' => $activeBreaks,
            'avg_delay' => $avgDelay,
            'coverage_gaps' => $coverageGaps,
        ],
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
