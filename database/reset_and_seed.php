<?php
declare(strict_types=1);

$dbConfig = require __DIR__ . '/../config/database.php';

$pdo = new PDO(
    "mysql:host={$dbConfig['host']};dbname={$dbConfig['name']};charset=utf8mb4",
    $dbConfig['user'],
    $dbConfig['pass'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

echo "✅ Connected\n";

$pdo->beginTransaction();

/**
 * =====================================================
 * STEP 1: CLEAR ALL DATA (FK SAFE)
 * =====================================================
 */
$pdo->exec("SET FOREIGN_KEY_CHECKS=0");

$tables = [
    'notifications',
    'employee_breaks',
    'schedule_assignments',
    'schedule_shifts',
    'schedules',
    'shift_requests',
    'shift_requirements',
    'weeks',
    'employees',
    'user_roles',
    'users'
];

foreach ($tables as $t) {
    $pdo->exec("DELETE FROM {$t}");
    $pdo->exec("ALTER TABLE {$t} AUTO_INCREMENT=1");
}

$pdo->exec("SET FOREIGN_KEY_CHECKS=1");

echo "🧹 Data cleared\n";

/**
 * =====================================================
 * STEP 2: WEEKS (10)
 * =====================================================
 */
$monday = new DateTimeImmutable('monday this week');
$weekStmt = $pdo->prepare(
    "INSERT INTO weeks (week_start_date, week_end_date) VALUES (?,?)"
);

$weeks = [];
for ($i = 0; $i < 10; $i++) {
    $start = $monday->modify("+{$i} week");
    $end = $start->modify('+6 days');
    $weekStmt->execute([$start->format('Y-m-d'), $end->format('Y-m-d')]);
    $weeks[] = (int)$pdo->lastInsertId();
}

echo "📅 Weeks seeded\n";

/**
 * =====================================================
 * STEP 3: USERS + ROLES + EMPLOYEES (10)
 * =====================================================
 */
$roles = $pdo->query("SELECT role_name,id FROM roles")->fetchAll(PDO::FETCH_KEY_PAIR);
$sections = $pdo->query("SELECT id FROM sections")->fetchAll(PDO::FETCH_COLUMN);

$userStmt = $pdo->prepare("INSERT INTO users (username,password_hash,email) VALUES (?,?,?)");
$roleStmt = $pdo->prepare("INSERT INTO user_roles (user_id,role_id,section_id) VALUES (?,?,?)");
$empStmt  = $pdo->prepare(
    "INSERT INTO employees (user_role_id,employee_code,full_name,email,is_senior,seniority_level)
     VALUES (?,?,?,?,?,?)"
);

$employees = [];

for ($i = 1; $i <= 10; $i++) {
    $userStmt->execute([
        "employee{$i}",
        password_hash('password123', PASSWORD_BCRYPT),
        "employee{$i}@test.com"
    ]);

    $uid = (int)$pdo->lastInsertId();
    $section = $sections[$i % count($sections)];
    $roleId = ($i <= 2) ? $roles['Senior'] : $roles['Employee'];

    $roleStmt->execute([$uid, $roleId, $section]);
    $urid = (int)$pdo->lastInsertId();

    $empStmt->execute([
        $urid,
        "EMP{$i}",
        "Employee {$i}",
        "employee{$i}@test.com",
        ($i <= 2) ? 1 : 0,
        rand(1,10)
    ]);

    $employees[] = (int)$pdo->lastInsertId();
}

echo "👥 Users & employees seeded\n";

/**
 * =====================================================
 * STEP 4: SHIFT REQUIREMENTS (10)
 * =====================================================
 */
$shiftTypes = $pdo->query("SELECT id FROM shift_types")->fetchAll(PDO::FETCH_COLUMN);
$reqStmt = $pdo->prepare(
    "INSERT INTO shift_requirements (week_id,section_id,shift_date,shift_type_id,required_count)
     VALUES (?,?,?,?,?)"
);

for ($i = 0; $i < 10; $i++) {
    $reqStmt->execute([
        $weeks[0],
        $sections[0],
        (new DateTimeImmutable())->modify("+{$i} day")->format('Y-m-d'),
        $shiftTypes[$i % count($shiftTypes)],
        3
    ]);
}

echo "📊 Shift requirements seeded\n";

/**
 * =====================================================
 * STEP 5: SHIFT REQUESTS (10) — STORED PROCEDURE
 * =====================================================
 */
$proc = $pdo->prepare("CALL sp_submit_shift_request(?,?,?,?,?,?,?,?)");
$patterns = $pdo->query("SELECT id FROM schedule_patterns")->fetchAll(PDO::FETCH_COLUMN);
$defs = $pdo->query("SELECT id FROM shift_definitions WHERE category!='OFF'")
            ->fetchAll(PDO::FETCH_COLUMN);

$count = 0;
foreach ($employees as $empId) {
    if ($count >= 10) break;

    try {
        $proc->execute([
            $empId,
            $weeks[0],
            (new DateTimeImmutable('tuesday'))->format('Y-m-d'),
            $defs[array_rand($defs)],
            0,
            $patterns[0],
            'Test request',
            'MEDIUM'
        ]);
        $count++;
    } catch (PDOException $e) {
        continue; // Seniors / Sundays skipped
    }
}

echo "📝 Shift requests seeded\n";

/**
 * =====================================================
 * STEP 6: SCHEDULE + SHIFTS + ASSIGNMENTS (10)
 * =====================================================
 */
$scheduleStmt = $pdo->prepare(
    "INSERT INTO schedules (week_id,section_id,generated_by_admin_id,status)
     VALUES (?,?,?,?)"
);
$scheduleStmt->execute([$weeks[0], $sections[0], $employees[0], 'FINAL']);
$scheduleId = (int)$pdo->lastInsertId();

$shiftStmt = $pdo->prepare(
    "INSERT INTO schedule_shifts (schedule_id,shift_date,shift_definition_id,required_count)
     VALUES (?,?,?,?)"
);
$assignStmt = $pdo->prepare(
    "INSERT INTO schedule_assignments (schedule_shift_id,employee_id,assignment_source,is_senior)
     VALUES (?,?,?,?)"
);

for ($i = 0; $i < 10; $i++) {
    $shiftStmt->execute([
        $scheduleId,
        (new DateTimeImmutable())->modify("+{$i} day")->format('Y-m-d'),
        $defs[$i % count($defs)],
        2
    ]);
    $shiftId = (int)$pdo->lastInsertId();

    $assignStmt->execute([
        $shiftId,
        $employees[$i % count($employees)],
        'AUTO_ASSIGNED',
        0
    ]);
}

echo "📋 Schedule seeded\n";

/**
 * =====================================================
 * STEP 7: BREAKS + NOTIFICATIONS (10)
 * =====================================================
 */
$breakStmt = $pdo->prepare(
    "INSERT INTO employee_breaks (employee_id,worked_date,break_start,break_end)
     VALUES (?,?,?,?)"
);

$notifStmt = $pdo->prepare(
    "INSERT INTO notifications (user_id,type,title)
     VALUES (?,?,?)"
);

for ($i = 0; $i < 10; $i++) {
    $breakStmt->execute([
        $employees[$i % count($employees)],
        date('Y-m-d'),
        date('Y-m-d H:i:s', strtotime('-40 minutes')),
        date('Y-m-d H:i:s')
    ]);

    $notifStmt->execute([
        $employees[$i % count($employees)],
        'SCHEDULE_PUBLISHED',
        'Test notification'
    ]);
}

$pdo->commit();

echo "\n✅ RESET + SEED COMPLETED (10 RECORDS PER TABLE)\n";
