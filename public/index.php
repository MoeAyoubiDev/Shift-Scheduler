<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/Helpers/helpers.php';
require_once __DIR__ . '/../app/Helpers/schedule.php';
require_once __DIR__ . '/../app/Controllers/AuthController.php';
require_once __DIR__ . '/../app/Controllers/RequestController.php';
require_once __DIR__ . '/../app/Controllers/AdminController.php';

$pdo = db();
$weekStart = current_week_start();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'login':
            $username = trim($_POST['username'] ?? '');
            $password = trim($_POST['password'] ?? '');
            $message = AuthController::handleLogin($username, $password);
            break;
        case 'logout':
            AuthController::handleLogout();
            break;
        case 'submit_request':
            $result = RequestController::handleSubmitRequest($pdo, $_POST);
            $message = $result['message'];
            $weekStart = $result['week_start'];
            break;
        case 'update_request_status':
            $message = AdminController::handleUpdateRequestStatus($pdo, $_POST) ?? $message;
            break;
        case 'toggle_flag':
            $message = AdminController::handleToggleFlag($pdo, $_POST) ?? $message;
            break;
        case 'toggle_submission':
            $message = AdminController::handleToggleSubmission($weekStart, $_POST) ?? $message;
            break;
        case 'delete_employee':
            $message = AdminController::handleDeleteEmployee($pdo, $_POST) ?? $message;
            break;
        case 'save_requirements':
            $message = AdminController::handleSaveRequirements($weekStart, $_POST) ?? $message;
            break;
        case 'generate_schedule':
            $message = AdminController::handleGenerateSchedule($weekStart) ?? $message;
            break;
        case 'update_schedule_entry':
            $message = AdminController::handleUpdateScheduleEntry($_POST) ?? $message;
            break;
    }
}

$user = current_user();
$submissionLocked = is_submission_locked_for_week($weekStart);
$requirements = fetch_shift_requirements($weekStart);
$schedule = fetch_schedule($weekStart);

if ($user && isset($_GET['download']) && $_GET['download'] === 'schedule') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="schedule-' . $weekStart . '.csv"');

    $out = fopen('php://output', 'w');
    fputcsv($out, ['Employee', 'Day', 'Shift', 'Status', 'Notes']);
    foreach ($schedule as $entry) {
        fputcsv($out, [
            $entry['employee_name'],
            $entry['day'],
            $entry['shift_type'],
            $entry['status'],
            $entry['notes'],
        ]);
    }
    fclose($out);
    exit;
}

if (!$user) {
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
render_view('dashboard/overview', [
    'user' => $user,
    'weekStart' => $weekStart,
]);

if (is_employee($user)) {
    $filters = [
        'from' => $_GET['from'] ?? null,
        'to' => $_GET['to'] ?? null,
        'on' => $_GET['on'] ?? null,
    ];

    $history = fetch_request_history(
        $user['id'],
        $filters['from'],
        $filters['to'],
        $filters['on']
    );

    render_view('dashboard/employee', [
        'user' => $user,
        'weekStart' => $weekStart,
        'submissionLocked' => $submissionLocked,
        'schedule' => $schedule,
        'history' => $history,
        'filters' => $filters,
    ]);
} else {
    $requests = fetch_requests_with_details($weekStart);
    $employees = [];

    if (is_primary_admin($user)) {
        $employees = $pdo->query('SELECT id, name, employee_identifier, email FROM users WHERE role = "employee" ORDER BY name')->fetchAll();
    }

    render_view('dashboard/admin', [
        'user' => $user,
        'weekStart' => $weekStart,
        'submissionLocked' => $submissionLocked,
        'requests' => $requests,
        'requirements' => $requirements,
        'schedule' => $schedule,
        'employees' => $employees,
    ]);
}

render_view('partials/footer');
