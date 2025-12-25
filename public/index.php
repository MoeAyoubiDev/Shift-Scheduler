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

// Only create week if user is logged in (has company_id)
$weekId = 0;
$user = $_SESSION['user'] ?? null;
if ($user && isset($user['company_id']) && $user['company_id']) {
    try {
        $weekId = Schedule::upsertWeek($weekStart, $weekEnd, (int) $user['company_id']);
        if ($weekId <= 0) {
            throw new RuntimeException("Failed to create or retrieve week. week_id is 0.");
        }
    } catch (PDOException $e) {
        error_log("Database error in Schedule::upsertWeek: " . $e->getMessage() . " | Code: " . $e->getCode());
        $errorMsg = htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        if ($appEnv === 'development') {
            die("<pre style='background:#f0f0f0;padding:20px;border:1px solid #ccc;font-family:monospace;'>" .
                "<strong>Database Error (PDOException):</strong>\n" .
                "Message: " . $errorMsg . "\n" .
                "SQL State: " . $e->getCode() . "\n" .
                "File: " . $e->getFile() . ":" . $e->getLine() . "\n\n" .
                "<strong>Stack trace:</strong>\n" . htmlspecialchars($e->getTraceAsString(), ENT_QUOTES, 'UTF-8') .
                "</pre>");
        }
        die("Database error. Please check server logs. Error: " . $errorMsg);
    } catch (RuntimeException $e) {
        error_log("Runtime error in Schedule::upsertWeek: " . $e->getMessage());
        $errorMsg = htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        if ($appEnv === 'development') {
            die("<pre style='background:#f0f0f0;padding:20px;border:1px solid #ccc;font-family:monospace;'>" .
                "<strong>Runtime Error:</strong>\n" .
                "Message: " . $errorMsg . "\n" .
                "File: " . $e->getFile() . ":" . $e->getLine() . "\n\n" .
                "<strong>Stack trace:</strong>\n" . htmlspecialchars($e->getTraceAsString(), ENT_QUOTES, 'UTF-8') .
                "</pre>");
        }
        die("Database error. Please check server logs. Error: " . $errorMsg);
    } catch (Exception $e) {
        error_log("Error in Schedule::upsertWeek: " . $e->getMessage());
        $errorMsg = htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        if ($appEnv === 'development') {
            die("<pre style='background:#f0f0f0;padding:20px;border:1px solid #ccc;font-family:monospace;'>" .
                "<strong>Error:</strong>\n" .
                "Message: " . $errorMsg . "\n" .
                "Type: " . get_class($e) . "\n" .
                "File: " . $e->getFile() . ":" . $e->getLine() . "\n\n" .
                "<strong>Stack trace:</strong>\n" . htmlspecialchars($e->getTraceAsString(), ENT_QUOTES, 'UTF-8') .
                "</pre>");
        }
        die("Database error. Please check server logs. Error: " . $errorMsg);
    }
}

// Initialize action handlers (only if weekId is available)
if ($weekId > 0) {
    ActionHandler::initialize($weekId);
}

// Process POST requests
$message = '';
$redirectUrl = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
              strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

    $result = ActionHandler::process($_POST, [
        'weekId' => $weekId,
        'weekStart' => $weekStart,
        'weekEnd' => $weekEnd,
    ]);

    // ✅ If AJAX: always return JSON (even if redirect exists)
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }

    // ✅ Normal request: redirect if needed
    if (!empty($result['redirect'])) {
        $redirectUrl = $result['redirect'];
        if (!empty($result['message'])) {
            $redirectUrl .= (strpos($redirectUrl, '?') !== false ? '&' : '?')
                         . 'message=' . urlencode($result['message']);
        }
        header('Location: ' . $redirectUrl);
        exit;
    }

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

if ($user && $role === 'Supervisor' && !$sectionId) {
    $sections = $user['sections'] ?? [];
    if (count($sections) === 1) {
        $sectionId = (int) $sections[0]['section_id'];
        set_current_section($sectionId);
    }
}

if ($user && isset($_GET['download']) && $_GET['download'] === 'schedule' && $role === 'Team Leader') {
    $exportWeekStart = $_GET['week_start'] ?? $weekStart;
    $exportWeekEnd = $_GET['week_end'] ?? $weekEnd;
    $companyId = $user['company_id'] ?? null;
    if (!$companyId) {
        http_response_code(400);
        die('Company ID is required');
    }
    $exportWeekId = Schedule::upsertWeek($exportWeekStart, $exportWeekEnd, (int) $companyId);
    $scheduleRows = Schedule::getWeeklySchedule($exportWeekId, (int) $companyId);
    
    require_once __DIR__ . '/../app/Models/Employee.php';
    $allEmployees = Employee::listByCompany((int) $companyId);
    
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

// Handle section reset for Supervisor
if (isset($_GET['reset_section']) && $role === 'Supervisor') {
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

if ($role === 'Supervisor') {
    if (!$sectionId) {
        render_view('director/choose-section', [
            'user' => $user,
        ]);
    } else {
        require_once __DIR__ . '/../app/Models/Section.php';
        require_once __DIR__ . '/../app/Models/Role.php';
        require_once __DIR__ . '/../app/Models/Performance.php';
        require_once __DIR__ . '/../app/Models/ShiftRequest.php';
        require_once __DIR__ . '/../app/Models/Employee.php';
        
        $dashboard = Performance::directorDashboard($sectionId, $weekId);
        $schedule = Schedule::getWeeklySchedule($weekId, $sectionId);
        $requests = ShiftRequest::listByWeek($weekId, $sectionId);
        $performance = Performance::report($weekStart, $weekEnd, $sectionId, null);
        $sections = Section::getAll();
        $roles = Role::listRoles();
        $employees = Employee::listBySection($sectionId);
        $admins = Employee::listAdmins();

        $directorPage = $_GET['page'] ?? 'overview';
        $allowedDirectorPages = ['overview', 'employees', 'departments', 'attendance', 'reports', 'settings'];
        if (!in_array($directorPage, $allowedDirectorPages, true)) {
            $directorPage = 'overview';
        }

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
            'employees' => $employees,
            'admins' => $admins,
            'directorPage' => $directorPage,
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

    $teamleaderPage = $_GET['page'] ?? 'overview';
    $allowedTeamleaderPages = [
        'overview',
        'create-employee',
        'manage-employees',
        'shift-requirements',
        'shift-requests',
        'weekly-schedule',
        'break-monitoring',
        'performance',
    ];
    if (!in_array($teamleaderPage, $allowedTeamleaderPages, true)) {
        $teamleaderPage = 'overview';
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
        'teamleaderPage' => $teamleaderPage,
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
