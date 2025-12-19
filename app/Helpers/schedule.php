<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

function fetch_shift_requirements(string $weekStart): array
{
    $stmt = db()->prepare('SELECT * FROM shift_requirements WHERE week_start = :week_start LIMIT 1');
    $stmt->execute(['week_start' => $weekStart]);
    $row = $stmt->fetch();

    return $row ?: [
        'week_start' => $weekStart,
        'am_required' => 0,
        'pm_required' => 0,
        'mid_required' => 0,
        'senior_staff' => '',
    ];
}

function save_shift_requirements(string $weekStart, int $am, int $pm, int $mid, string $seniorStaffNotes = ''): void
{
    $stmt = db()->prepare(
        'INSERT INTO shift_requirements (week_start, am_required, pm_required, mid_required, senior_staff, updated_at)
         VALUES (:week_start, :am_required, :pm_required, :mid_required, :senior_staff, NOW())
         ON DUPLICATE KEY UPDATE am_required = VALUES(am_required), pm_required = VALUES(pm_required),
             mid_required = VALUES(mid_required), senior_staff = VALUES(senior_staff), updated_at = VALUES(updated_at)'
    );

    $stmt->execute([
        'week_start' => $weekStart,
        'am_required' => $am,
        'pm_required' => $pm,
        'mid_required' => $mid,
        'senior_staff' => $seniorStaffNotes,
    ]);
}

function generate_schedule(string $weekStart): void
{
    $pdo = db();
    $requirements = fetch_shift_requirements($weekStart);

    $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    $capacity = [];
    foreach ($days as $day) {
        $capacity[$day] = [
            'AM' => (int) $requirements['am_required'],
            'PM' => (int) $requirements['pm_required'],
            'MID' => (int) $requirements['mid_required'],
        ];
    }

    $assignedCounts = [];
    foreach ($days as $day) {
        $assignedCounts[$day] = ['AM' => 0, 'PM' => 0, 'MID' => 0];
    }

    $pdo->beginTransaction();

    $pdo->prepare('DELETE FROM schedule_entries WHERE week_start = :week_start')
        ->execute(['week_start' => $weekStart]);

    $approvedStmt = $pdo->prepare(
        'SELECT r.*, u.name AS employee_name, u.id AS user_id
         FROM requests r
         INNER JOIN users u ON u.id = r.user_id
         WHERE r.week_start = :week_start AND r.status = "accepted"'
    );
    $approvedStmt->execute(['week_start' => $weekStart]);
    $approved = $approvedStmt->fetchAll();

    $insert = $pdo->prepare(
        'INSERT INTO schedule_entries (user_id, employee_name, week_start, day, shift_type, status, notes)
         VALUES (:user_id, :employee_name, :week_start, :day, :shift_type, :status, :notes)'
    );

    $unmatchedUsers = [];

    foreach ($approved as $request) {
        $day = $request['requested_day'];
        $shift = $request['day_off'] ? 'OFF' : $request['shift_type'];
        $status = 'assigned';
        $notes = '';

        if ($request['day_off']) {
            $notes = 'Day off requested';
        } elseif ($shift !== 'OFF' && $assignedCounts[$day][$shift] >= $capacity[$day][$shift]) {
            $status = 'unmatched';
            $notes = 'Requested shift was full';
            $unmatchedUsers[] = $request['user_id'];
        } else {
            $assignedCounts[$day][$shift]++;
        }

        $insert->execute([
            'user_id' => $request['user_id'],
            'employee_name' => $request['employee_name'],
            'week_start' => $weekStart,
            'day' => $day,
            'shift_type' => $shift,
            'status' => $status,
            'notes' => $notes,
        ]);
    }

    $employeesStmt = $pdo->query('SELECT id, name FROM users WHERE role = "employee"');
    $employees = $employeesStmt->fetchAll();

    $requestedUserIds = array_unique(array_column($approved, 'user_id'));

    foreach ($employees as $employee) {
        if (!in_array($employee['id'], $requestedUserIds, true)) {
            $insert->execute([
                'user_id' => $employee['id'],
                'employee_name' => $employee['name'],
                'week_start' => $weekStart,
                'day' => 'N/A',
                'shift_type' => 'UNASSIGNED',
                'status' => 'no_request',
                'notes' => 'No requests sent',
            ]);
        }
    }

    $pdo->prepare(
        'INSERT INTO schedule_generations (week_start, generated_at)
         VALUES (:week_start, NOW())
         ON DUPLICATE KEY UPDATE generated_at = VALUES(generated_at)'
    )->execute(['week_start' => $weekStart]);

    $pdo->commit();
}

function fetch_schedule(string $weekStart): array
{
    $stmt = db()->prepare(
        'SELECT * FROM schedule_entries WHERE week_start = :week_start ORDER BY day, shift_type, employee_name'
    );
    $stmt->execute(['week_start' => $weekStart]);
    return $stmt->fetchAll();
}

function update_schedule_entry(int $entryId, string $day, string $shiftType, string $notes): void
{
    $stmt = db()->prepare(
        'UPDATE schedule_entries SET day = :day, shift_type = :shift_type, notes = :notes WHERE id = :id'
    );

    $stmt->execute([
        'day' => $day,
        'shift_type' => $shiftType,
        'notes' => $notes,
        'id' => $entryId,
    ]);
}
