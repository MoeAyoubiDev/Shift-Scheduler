<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function login(string $username, string $password): bool
{
    $user = find_user_by_username($username);

    if (!$user || !password_verify($password, $user['password_hash'])) {
        return false;
    }

    if (!(bool) $user['is_active']) {
        return false;
    }

    $_SESSION['user'] = $user;
    return true;
}

function logout(): void
{
    unset($_SESSION['user']);
}

function require_login(): void
{
    if (!current_user()) {
        header('Location: /index.php');
        exit;
    }
}

function find_user_by_username(string $username): ?array
{
    $stmt = db()->prepare(
        'SELECT u.*, ur.id AS user_role_id, r.RoleName AS role_name, s.section_name,
                e.id AS employee_id, e.full_name, e.employee_code, e.is_senior,
                e.seniority_level, e.is_active AS employee_active
         FROM users u
         LEFT JOIN user_roles ur ON ur.userId = u.id
         LEFT JOIN roles r ON r.id = ur.role_id
         LEFT JOIN sections s ON s.id = ur.section_id
         LEFT JOIN employees e ON e.user_rolesId = ur.id
         WHERE u.username = :username'
    );
    $stmt->execute(['username' => $username]);
    $row = $stmt->fetch();

    if (!$row) {
        return null;
    }

    return [
        'id' => (int) $row['id'],
        'username' => $row['username'],
        'email' => $row['email'],
        'password_hash' => $row['password_hash'],
        'is_active' => (int) $row['is_active'],
        'role' => $row['role_name'] ?? 'Employee',
        'section' => $row['section_name'] ?? 'Unassigned',
        'user_role_id' => $row['user_role_id'],
        'employee_id' => $row['employee_id'],
        'name' => $row['full_name'] ?? $row['username'],
        'employee_code' => $row['employee_code'] ?? 'N/A',
        'is_senior' => (bool) $row['is_senior'],
        'seniority_level' => (int) $row['seniority_level'],
        'employee_active' => (bool) $row['employee_active'],
    ];
}

function is_admin(array $user): bool
{
    $role = strtolower($user['role'] ?? '');
    return str_contains($role, 'admin')
        || str_contains($role, 'leader')
        || str_contains($role, 'manager')
        || str_contains($role, 'supervisor');
}

function is_employee(array $user): bool
{
    return !empty($user['employee_id']);
}

function ensure_week(string $weekStart): array
{
    $pdo = db();
    $stmt = $pdo->prepare('SELECT * FROM weeks WHERE week_start_date = :week_start LIMIT 1');
    $stmt->execute(['week_start' => $weekStart]);
    $week = $stmt->fetch();

    if ($week) {
        return $week;
    }

    $weekEnd = week_end_from_start($weekStart);
    $insert = $pdo->prepare(
        'INSERT INTO weeks (week_start_date, week_end_date, is_locked_for_requests, created_at)
         VALUES (:week_start, :week_end, 0, CURRENT_TIMESTAMP)'
    );
    $insert->execute([
        'week_start' => $weekStart,
        'week_end' => $weekEnd,
    ]);

    $stmt->execute(['week_start' => $weekStart]);
    return $stmt->fetch();
}

function submission_window_open(string $weekStart): bool
{
    $today = new DateTimeImmutable();
    $dayOfWeek = (int) $today->format('N');

    if ($dayOfWeek > 5) {
        return false;
    }

    if (is_submission_locked_for_week($weekStart)) {
        return false;
    }

    return true;
}

function is_submission_locked_for_week(string $weekStart): bool
{
    $week = ensure_week($weekStart);
    return (bool) $week['is_locked_for_requests'];
}

function set_submission_lock(string $weekStart, bool $locked): void
{
    $week = ensure_week($weekStart);
    $stmt = db()->prepare('UPDATE weeks SET is_locked_for_requests = :locked, lock_reason = :reason WHERE id = :id');
    $stmt->execute([
        'locked' => $locked ? 1 : 0,
        'reason' => $locked ? 'Locked by admin' : null,
        'id' => $week['id'],
    ]);
}

function fetch_shift_definitions(): array
{
    $stmt = db()->query('SELECT * FROM shift_definitions ORDER BY shiftName');
    return $stmt->fetchAll();
}

function fetch_shift_types(): array
{
    $stmt = db()->query('SELECT * FROM shift_types ORDER BY code');
    return $stmt->fetchAll();
}

function fetch_schedule_patterns(): array
{
    $stmt = db()->query('SELECT * FROM schedule_patterns ORDER BY work_days_per_week DESC');
    return $stmt->fetchAll();
}

function fetch_request_history(int $employeeId, ?string $fromDate, ?string $toDate, ?string $specificDate): array
{
    $conditions = ['r.employee_id = :employee_id'];
    $params = ['employee_id' => $employeeId];

    if ($specificDate) {
        $conditions[] = 'r.SubmitDate = :specific_date';
        $params['specific_date'] = $specificDate;
    } else {
        if ($fromDate) {
            $conditions[] = 'r.SubmitDate >= :from_date';
            $params['from_date'] = $fromDate;
        }
        if ($toDate) {
            $conditions[] = 'r.SubmitDate <= :to_date';
            $params['to_date'] = $toDate;
        }
    }

    $sql = 'SELECT r.*, sd.shiftName, sd.category, sp.names AS pattern_name
            FROM shift_requests r
            LEFT JOIN shift_definitions sd ON sd.id = r.shift_definition_id
            LEFT JOIN schedule_patterns sp ON sp.id = r.schedule_pattern_id
            WHERE ' . implode(' AND ', $conditions) . '
            ORDER BY r.SubmitDate DESC';

    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function fetch_requests_with_details(int $weekId): array
{
    $stmt = db()->prepare(
        'SELECT r.*, e.full_name AS employee_name, e.employee_code, e.email,
                sd.shiftName, sd.category, sp.names AS pattern_name
         FROM shift_requests r
         INNER JOIN employees e ON e.id = r.employee_id
         LEFT JOIN shift_definitions sd ON sd.id = r.shift_definition_id
         LEFT JOIN schedule_patterns sp ON sp.id = r.schedule_pattern_id
         WHERE r.week_id = :week_id
         ORDER BY r.submitted_at DESC'
    );
    $stmt->execute(['week_id' => $weekId]);
    return $stmt->fetchAll();
}

function fetch_notifications(int $userId): array
{
    $stmt = db()->prepare(
        'SELECT * FROM notifications WHERE user_id = :user_id ORDER BY created_at DESC'
    );
    $stmt->execute(['user_id' => $userId]);
    return $stmt->fetchAll();
}

function mark_notification_read(int $notificationId, int $userId): void
{
    $stmt = db()->prepare(
        'UPDATE notifications SET is_read = 1, read_at = CURRENT_TIMESTAMP WHERE id = :id AND user_id = :user_id'
    );
    $stmt->execute([
        'id' => $notificationId,
        'user_id' => $userId,
    ]);
}

function importance_badge(string $importance): string
{
    return match (strtoupper($importance)) {
        'HIGH' => 'badge badge-high',
        'NORMAL' => 'badge badge-normal',
        default => 'badge badge-low',
    };
}

function status_badge(string $status): string
{
    return match (strtoupper($status)) {
        'APPROVED' => 'badge badge-success',
        'DECLINED' => 'badge badge-danger',
        default => 'badge badge-warning',
    };
}
