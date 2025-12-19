<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

function fetch_shift_requirements(int $weekId): array
{
    $stmt = db()->prepare(
        'SELECT sr.*, st.code AS shift_code, st.name AS shift_name
         FROM shift_requirements sr
         INNER JOIN shift_types st ON st.id = sr.shift_type_id
         WHERE sr.week_id = :week_id
         ORDER BY sr.date, st.code'
    );
    $stmt->execute(['week_id' => $weekId]);
    return $stmt->fetchAll();
}

function save_shift_requirement(int $weekId, string $date, int $shiftTypeId, int $requiredCount): void
{
    $pdo = db();
    $stmt = $pdo->prepare(
        'SELECT id FROM shift_requirements WHERE week_id = :week_id AND date = :date AND shift_type_id = :shift_type_id'
    );
    $stmt->execute([
        'week_id' => $weekId,
        'date' => $date,
        'shift_type_id' => $shiftTypeId,
    ]);
    $existing = $stmt->fetch();

    if ($existing) {
        $update = $pdo->prepare('UPDATE shift_requirements SET required_count = :required_count WHERE id = :id');
        $update->execute([
            'required_count' => $requiredCount,
            'id' => $existing['id'],
        ]);
        return;
    }

    $insert = $pdo->prepare(
        'INSERT INTO shift_requirements (week_id, date, shift_type_id, required_count)
         VALUES (:week_id, :date, :shift_type_id, :required_count)'
    );
    $insert->execute([
        'week_id' => $weekId,
        'date' => $date,
        'shift_type_id' => $shiftTypeId,
        'required_count' => $requiredCount,
    ]);
}

function create_schedule_if_missing(int $weekId, int $adminEmployeeId): int
{
    $pdo = db();
    $stmt = $pdo->prepare('SELECT id FROM schedules WHERE week_id = :week_id LIMIT 1');
    $stmt->execute(['week_id' => $weekId]);
    $schedule = $stmt->fetch();

    if ($schedule) {
        return (int) $schedule['id'];
    }

    $insert = $pdo->prepare(
        'INSERT INTO schedules (week_id, generated_by_admin_id, generated_at, status)
         VALUES (:week_id, :admin_id, CURRENT_TIMESTAMP, :status)'
    );
    $insert->execute([
        'week_id' => $weekId,
        'admin_id' => $adminEmployeeId,
        'status' => 'DRAFT',
    ]);

    return (int) $pdo->lastInsertId();
}

function fetch_schedule_assignments(int $weekId): array
{
    $stmt = db()->prepare(
        'SELECT ss.date, sd.shiftName, sd.category, sd.color_code, ss.required_count,
                sa.assignment_source, sa.is_senior,
                e.full_name, e.employee_code
         FROM schedules s
         INNER JOIN schedule_shifts ss ON ss.schedule_id = s.id
         INNER JOIN shift_definitions sd ON sd.id = ss.shift_definition_id
         LEFT JOIN schedule_assignments sa ON sa.schedule_shift_id = ss.id
         LEFT JOIN employees e ON e.id = sa.employee_id
         WHERE s.week_id = :week_id
         ORDER BY ss.date, sd.shiftName, e.full_name'
    );
    $stmt->execute(['week_id' => $weekId]);
    return $stmt->fetchAll();
}

function fetch_schedule_summary(int $weekId): ?array
{
    $stmt = db()->prepare(
        'SELECT s.*, w.week_start_date, w.week_end_date, e.full_name AS generated_by
         FROM schedules s
         INNER JOIN weeks w ON w.id = s.week_id
         LEFT JOIN employees e ON e.id = s.generated_by_admin_id
         WHERE s.week_id = :week_id
         ORDER BY s.generated_at DESC'
    );
    $stmt->execute(['week_id' => $weekId]);
    $row = $stmt->fetch();
    return $row ?: null;
}
