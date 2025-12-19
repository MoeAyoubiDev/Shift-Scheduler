<?php
declare(strict_types=1);

require_once __DIR__ . '/../Core/config.php';
require_once __DIR__ . '/view.php';

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
    $stmt = db()->prepare('SELECT * FROM users WHERE username = :username LIMIT 1');
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch();

    return $user ?: null;
}

function is_primary_admin(array $user): bool
{
    return $user['role'] === 'primary_admin';
}

function is_secondary_admin(array $user): bool
{
    return $user['role'] === 'secondary_admin';
}

function is_employee(array $user): bool
{
    return $user['role'] === 'employee';
}

function submission_window_open(): bool
{
    $today = new DateTimeImmutable();
    $dayOfWeek = (int) $today->format('N'); // 1 (Mon) - 7 (Sun)

    if ($dayOfWeek > 5) {
        return false;
    }

    if (is_submission_locked_for_week(current_week_start())) {
        return false;
    }

    return true;
}

function is_submission_locked_for_week(string $weekStart): bool
{
    $stmt = db()->prepare('SELECT is_locked FROM submission_controls WHERE week_start = :week_start LIMIT 1');
    $stmt->execute(['week_start' => $weekStart]);
    $row = $stmt->fetch();

    return $row ? (bool) $row['is_locked'] : false;
}

function set_submission_lock(string $weekStart, bool $locked): void
{
    $stmt = db()->prepare('INSERT INTO submission_controls (week_start, is_locked, updated_at)
        VALUES (:week_start, :is_locked, NOW())
        ON DUPLICATE KEY UPDATE is_locked = VALUES(is_locked), updated_at = VALUES(updated_at)');
    $stmt->execute([
        'week_start' => $weekStart,
        'is_locked' => $locked ? 1 : 0,
    ]);
}

function fetch_request_history(int $userId, ?string $fromDate, ?string $toDate, ?string $specificDate): array
{
    $conditions = ['user_id = :user_id'];
    $params = ['user_id' => $userId];

    if ($specificDate) {
        $conditions[] = 'DATE(submission_date) = :specific_date';
        $params['specific_date'] = $specificDate;
    } else {
        if ($fromDate) {
            $conditions[] = 'DATE(submission_date) >= :from_date';
            $params['from_date'] = $fromDate;
        }
        if ($toDate) {
            $conditions[] = 'DATE(submission_date) <= :to_date';
            $params['to_date'] = $toDate;
        }
    }

    $sql = 'SELECT r.*, u.name AS employee_name, u.employee_identifier
            FROM requests r
            INNER JOIN users u ON u.id = r.user_id
            WHERE ' . implode(' AND ', $conditions) . '
            ORDER BY submission_date DESC';

    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function fetch_requests_with_details(string $weekStart): array
{
    $stmt = db()->prepare(
        'SELECT r.*, u.name AS employee_name, u.employee_identifier, u.email
         FROM requests r
         INNER JOIN users u ON u.id = r.user_id
         WHERE r.week_start = :week_start
         ORDER BY r.submission_date DESC'
    );
    $stmt->execute(['week_start' => $weekStart]);
    return $stmt->fetchAll();
}

function label_for_importance(string $importance): string
{
    $levels = config('schedule', 'importance_levels', []);

    return $levels[$importance] ?? 'Low';
}

function schedule_option_label(string $option): string
{
    $options = config('schedule', 'schedule_options', []);

    if ($options === []) {
        $options = [
            '5x2' => '5 days on / 2 days off (9 hours)',
            '6x1' => '6 days on / 1 day off (7.5 hours)',
        ];
    }

    return $options[$option] ?? '6 days on / 1 day off (7.5 hours)';
}

function importance_badge(string $importance): string
{
    return match ($importance) {
        'high' => 'style="color:#c0392b;font-weight:700;"',
        'medium' => 'style="color:#d35400;font-weight:600;"',
        default => 'style="color:#2c3e50;"',
    };
}
