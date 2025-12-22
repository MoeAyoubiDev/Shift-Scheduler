<?php
/**
 * Database Reset and Seed Script
 * 
 * Safely resets all data and seeds with large, realistic test data
 * Can be run multiple times (idempotent)
 * 
 * Usage:
 *   php database/reset_and_seed.php
 *   OR
 *   mysql -u user -p ShiftSchedulerDB < database/reset_and_seed.php
 */

declare(strict_types=1);

// Load database configuration
$dbConfig = require __DIR__ . '/../config/database.php';

// Database connection
$host = $dbConfig['host'] ?? 'localhost';
$port = $dbConfig['port'] ?? '3306';
$dbname = $dbConfig['name'] ?? 'ShiftSchedulerDB';
$username = $dbConfig['user'] ?? 'root';
$password = $dbConfig['pass'] ?? '';

try {
    $pdo = new PDO(
        "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
    
    echo "✅ Connected to database: {$dbname}\n";
    
    // Start transaction
    $pdo->beginTransaction();
    
    echo "\n🔄 Step 1: Removing all existing data...\n";
    
    // Disable foreign key checks
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    // Delete in reverse dependency order
    $tables = [
        'notifications',
        'employee_breaks',
        'schedule_assignments',
        'schedule_shifts',
        'schedules',
        'shift_requirements',
        'shift_requests',
        'employees',
        'user_roles',
        'users',
        'weeks',
    ];
    
    foreach ($tables as $table) {
        $pdo->exec("DELETE FROM `{$table}`");
        echo "  ✓ Cleared: {$table}\n";
    }
    
    // Reset AUTO_INCREMENT
    foreach ($tables as $table) {
        $pdo->exec("ALTER TABLE `{$table}` AUTO_INCREMENT = 1");
    }
    
    // Re-enable foreign key checks
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    echo "✅ All data removed successfully\n";
    
    echo "\n🌱 Step 2: Seeding weeks (12 weeks: 4 past, current, 7 future)...\n";
    
    $today = new DateTimeImmutable();
    $monday = $today->modify('monday this week');
    
    // Create 12 weeks: 4 weeks ago to 7 weeks ahead
    $weeks = [];
    for ($i = -4; $i <= 7; $i++) {
        $weekStart = $monday->modify("{$i} weeks");
        $weekEnd = $weekStart->modify('+6 days');
        $weeks[] = [
            'start' => $weekStart->format('Y-m-d'),
            'end' => $weekEnd->format('Y-m-d'),
        ];
    }
    
    $weekStmt = $pdo->prepare("INSERT INTO weeks (week_start_date, week_end_date, is_locked_for_requests) VALUES (?, ?, 0)");
    foreach ($weeks as $week) {
        $weekStmt->execute([$week['start'], $week['end']]);
    }
    
    $weekIds = $pdo->query("SELECT id, week_start_date FROM weeks ORDER BY week_start_date")->fetchAll(PDO::FETCH_ASSOC);
    echo "  ✓ Created " . count($weekIds) . " weeks\n";
    
    echo "\n👥 Step 3: Seeding users and employees...\n";
    
    // Get role and section IDs
    $roles = $pdo->query("SELECT id, role_name FROM roles ORDER BY id")->fetchAll(PDO::FETCH_KEY_PAIR);
    $sections = $pdo->query("SELECT id, section_name FROM sections ORDER BY id")->fetchAll(PDO::FETCH_KEY_PAIR);
    
    $roleIds = array_flip($roles);
    $sectionIds = array_flip($sections);
    
    // Password hashes
    $directorPassword = password_hash('password', PASSWORD_BCRYPT);
    $defaultPassword = password_hash('password123', PASSWORD_BCRYPT);
    
    // Create Director (can access both sections)
    $userStmt = $pdo->prepare("INSERT INTO users (username, password_hash, email, is_active) VALUES (?, ?, ?, 1)");
    $userRoleStmt = $pdo->prepare("INSERT INTO user_roles (user_id, role_id, section_id) VALUES (?, ?, ?)");
    $employeeStmt = $pdo->prepare("INSERT INTO employees (user_role_id, employee_code, full_name, email, is_senior, seniority_level, is_active) VALUES (?, ?, ?, ?, 0, 0, 1)");
    
    $userStmt->execute(['director', $directorPassword, 'director@company.com']);
    $directorUserId = (int)$pdo->lastInsertId();
    
    // Director has access to both sections
    foreach ($sectionIds as $sectionId) {
        $userRoleStmt->execute([$directorUserId, $roleIds['Director'], $sectionId]);
    }
    
    echo "  ✓ Created Director\n";
    
    // Create Team Leaders, Supervisors, Seniors, and Employees for each section
    $employeeCount = 0;
    $seniorCount = 0;
    
    foreach ($sectionIds as $sectionId => $sectionName) {
        $sectionPrefix = strtoupper(substr(str_replace([' ', '-'], '', $sectionName), 0, 3));
        
        // 2 Team Leaders per section
        for ($i = 1; $i <= 2; $i++) {
            $username = "tl_{$sectionPrefix}_{$i}";
            $userStmt->execute([$username, $defaultPassword, "{$username}@company.com"]);
            $userId = (int)$pdo->lastInsertId();
            $userRoleStmt->execute([$userId, $roleIds['Team Leader'], $sectionId]);
            $userRoleId = (int)$pdo->lastInsertId();
            $employeeStmt->execute([$userRoleId, "TL{$sectionPrefix}{$i}", "Team Leader {$i} - {$sectionName}", "{$username}@company.com"]);
        }
        
        // 1 Supervisor per section
        $username = "sup_{$sectionPrefix}_1";
        $userStmt->execute([$username, $defaultPassword, "{$username}@company.com"]);
        $userId = (int)$pdo->lastInsertId();
        $userRoleStmt->execute([$userId, $roleIds['Supervisor'], $sectionId]);
        $userRoleId = (int)$pdo->lastInsertId();
        $employeeStmt->execute([$userRoleId, "SUP{$sectionPrefix}1", "Supervisor - {$sectionName}", "{$username}@company.com"]);
        
        // 4 Seniors per section
        $seniorStmt = $pdo->prepare("INSERT INTO employees (user_role_id, employee_code, full_name, email, is_senior, seniority_level, is_active) VALUES (?, ?, ?, ?, 1, 5, 1)");
        for ($i = 1; $i <= 4; $i++) {
            $username = "sen_{$sectionPrefix}_{$i}";
            $userStmt->execute([$username, $defaultPassword, "{$username}@company.com"]);
            $userId = (int)$pdo->lastInsertId();
            $userRoleStmt->execute([$userId, $roleIds['Senior'], $sectionId]);
            $userRoleId = (int)$pdo->lastInsertId();
            $seniorStmt->execute([$userRoleId, "SEN{$sectionPrefix}{$i}", "Senior {$i} - {$sectionName}", "{$username}@company.com"]);
            $seniorCount++;
        }
        
        // 50 Employees per section
        for ($i = 1; $i <= 50; $i++) {
            $username = "emp_{$sectionPrefix}_" . str_pad((string)$i, 3, '0', STR_PAD_LEFT);
            $userStmt->execute([$username, $defaultPassword, "{$username}@company.com"]);
            $userId = (int)$pdo->lastInsertId();
            $userRoleStmt->execute([$userId, $roleIds['Employee'], $sectionId]);
            $userRoleId = (int)$pdo->lastInsertId();
            $employeeStmt->execute([$userRoleId, "EMP{$sectionPrefix}" . str_pad((string)$i, 3, '0', STR_PAD_LEFT), "Employee {$i} - {$sectionName}", "{$username}@company.com"]);
            $employeeCount++;
        }
    }
    
    echo "  ✓ Created " . (2 * count($sectionIds)) . " Team Leaders\n";
    echo "  ✓ Created " . count($sectionIds) . " Supervisors\n";
    echo "  ✓ Created {$seniorCount} Seniors\n";
    echo "  ✓ Created {$employeeCount} Employees\n";
    
    // Get all employee IDs (after employees are created)
    $allEmployees = $pdo->query("SELECT e.id, e.user_role_id, ur.section_id FROM employees e INNER JOIN user_roles ur ON e.user_role_id = ur.id ORDER BY e.id")->fetchAll();
    $employeeIds = array_column($allEmployees, 'id');
    $employeeSectionMap = [];
    foreach ($allEmployees as $emp) {
        $employeeSectionMap[$emp['id']] = $emp['section_id'];
    }
    
    // Get shift definitions
    $shiftDefs = $pdo->query("SELECT id, shift_name, category FROM shift_definitions ORDER BY id")->fetchAll();
    $shiftDefIds = array_column($shiftDefs, 'id');
    $shiftDefMap = [];
    foreach ($shiftDefs as $sd) {
        $shiftDefMap[$sd['id']] = $sd;
    }
    
    // Get shift types
    $shiftTypes = $pdo->query("SELECT id FROM shift_types ORDER BY id")->fetchAll(PDO::FETCH_COLUMN);
    
    // Get schedule patterns
    $patterns = $pdo->query("SELECT id FROM schedule_patterns ORDER BY id")->fetchAll(PDO::FETCH_COLUMN);
    
    echo "\n📅 Step 4: Seeding shift requirements...\n";
    
    $reqStmt = $pdo->prepare("INSERT INTO shift_requirements (week_id, section_id, shift_date, shift_type_id, required_count) VALUES (?, ?, ?, ?, ?)");
    
    $reqCount = 0;
    foreach ($weekIds as $week) {
        $weekStart = new DateTimeImmutable($week['week_start_date']);
        foreach ($sectionIds as $sectionId) {
            // Create requirements for each day (Mon-Sun) and each shift type
            for ($day = 0; $day < 7; $day++) {
                $date = $weekStart->modify("+{$day} days")->format('Y-m-d');
                foreach ($shiftTypes as $shiftTypeId) {
                    // Vary required count: 3-8 employees per shift
                    $required = rand(3, 8);
                    $reqStmt->execute([$week['id'], $sectionId, $date, $shiftTypeId, $required]);
                    $reqCount++;
                }
            }
        }
    }
    
    echo "  ✓ Created {$reqCount} shift requirements\n";
    
    echo "\n📝 Step 5: Seeding shift requests...\n";
    
    $requestStmt = $pdo->prepare("INSERT INTO shift_requests (employee_id, week_id, request_date, shift_definition_id, is_day_off, schedule_pattern_id, reason, importance_level, status, submitted_at, reviewed_by_admin_id, reviewed_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $requestCount = 0;
    $statuses = ['PENDING', 'APPROVED', 'DECLINED'];
    $importanceLevels = ['LOW', 'MEDIUM', 'HIGH', 'EMERGENCY'];
    $reasons = [
        'Medical appointment',
        'Family event',
        'Personal matter',
        'Vacation',
        'Training session',
        'Conference',
        'Emergency',
        'Holiday',
        null,
    ];
    
    // Get Team Leader employee IDs for reviewers
    $reviewers = $pdo->query("
        SELECT e.id 
        FROM employees e 
        INNER JOIN user_roles ur ON e.user_role_id = ur.id 
        INNER JOIN roles r ON ur.role_id = r.id 
        WHERE r.role_name = 'Team Leader'
    ")->fetchAll(PDO::FETCH_COLUMN);
    
    // Create requests for current week and next 2 weeks
    $requestWeeks = array_slice($weekIds, 4, 3); // Current week + next 2
    
    foreach ($requestWeeks as $week) {
        $weekStart = new DateTimeImmutable($week['week_start_date']);
        
        // Each employee makes 0-3 requests per week
        foreach ($allEmployees as $emp) {
            if ($emp['section_id'] == null) continue;
            
            $numRequests = rand(0, 3);
            $requestedDates = [];
            
            for ($r = 0; $r < $numRequests; $r++) {
                $day = rand(0, 6);
                $date = $weekStart->modify("+{$day} days")->format('Y-m-d');
                
                // Avoid duplicate dates for same employee
                if (in_array($date, $requestedDates)) continue;
                $requestedDates[] = $date;
                
                $isDayOff = rand(0, 1);
                $shiftDefId = $isDayOff ? null : $shiftDefIds[array_rand($shiftDefIds)];
                $patternId = $patterns[array_rand($patterns)];
                $importance = $importanceLevels[array_rand($importanceLevels)];
                $status = $statuses[array_rand($statuses)];
                $reason = $reasons[array_rand($reasons)];
                
                $submittedAt = (new DateTimeImmutable())->modify('-' . rand(0, 7) . ' days')->format('Y-m-d H:i:s');
                $reviewedBy = ($status !== 'PENDING' && !empty($reviewers)) ? $reviewers[array_rand($reviewers)] : null;
                $reviewedAt = $reviewedBy ? (new DateTimeImmutable())->modify('-' . rand(0, 3) . ' days')->format('Y-m-d H:i:s') : null;
                
                $requestStmt->execute([
                    $emp['id'],
                    $week['id'],
                    $date,
                    $shiftDefId,
                    $isDayOff ? 1 : 0,
                    $patternId,
                    $reason,
                    $importance,
                    $status,
                    $submittedAt,
                    $reviewedBy,
                    $reviewedAt,
                ]);
                $requestCount++;
            }
        }
    }
    
    echo "  ✓ Created {$requestCount} shift requests\n";
    
    echo "\n📋 Step 6: Seeding schedules...\n";
    
    $scheduleStmt = $pdo->prepare("INSERT INTO schedules (week_id, section_id, generated_by_admin_id, status, notes) VALUES (?, ?, ?, ?, ?)");
    $scheduleShiftStmt = $pdo->prepare("INSERT INTO schedule_shifts (schedule_id, shift_date, shift_definition_id, required_count) VALUES (?, ?, ?, ?)");
    $assignmentStmt = $pdo->prepare("INSERT INTO schedule_assignments (schedule_shift_id, employee_id, assignment_source, is_senior, notes) VALUES (?, ?, ?, ?, ?)");
    
    // Get Senior employee IDs for schedule generation
    $seniors = $pdo->query("
        SELECT e.id, ur.section_id 
        FROM employees e 
        INNER JOIN user_roles ur ON e.user_role_id = ur.id 
        INNER JOIN roles r ON ur.role_id = r.id 
        WHERE r.role_name = 'Senior'
    ")->fetchAll();
    
    $scheduleCount = 0;
    $shiftCount = 0;
    $assignmentCount = 0;
    
    // Create schedules for current week and past 2 weeks
    $scheduleWeeks = array_slice($weekIds, 2, 3);
    
    foreach ($scheduleWeeks as $week) {
        foreach ($sectionIds as $sectionId) {
            // Find a senior from this section to generate schedule
            $generator = null;
            foreach ($seniors as $senior) {
                if ($senior['section_id'] == $sectionId) {
                    $generator = $senior['id'];
                    break;
                }
            }
            
            if (!$generator) continue;
            
            $status = rand(0, 1) ? 'DRAFT' : 'FINAL';
            $scheduleStmt->execute([$week['id'], $sectionId, $generator, $status, "Generated schedule for week {$week['week_start_date']}"]);
            $scheduleId = (int)$pdo->lastInsertId();
            $scheduleCount++;
            
            // Create schedule shifts for each day
            $weekStart = new DateTimeImmutable($week['week_start_date']);
            $sectionEmployees = array_filter($allEmployees, fn($e) => $e['section_id'] == $sectionId);
            $sectionEmployeeIds = array_column($sectionEmployees, 'id');
            
            for ($day = 0; $day < 7; $day++) {
                $date = $weekStart->modify("+{$day} days")->format('Y-m-d');
                
                // Create shifts for each shift definition (except Day Off)
                foreach ($shiftDefs as $shiftDef) {
                    if ($shiftDef['category'] === 'OFF') continue;
                    
                    // Get requirement for this date/shift
                    $req = $pdo->prepare("
                        SELECT sr.required_count 
                        FROM shift_requirements sr
                        INNER JOIN shift_definitions sd ON sr.shift_type_id = sd.shift_type_id
                        WHERE sr.week_id = ? AND sr.section_id = ? AND sr.shift_date = ? AND sd.id = ?
                    ");
                    $req->execute([$week['id'], $sectionId, $date, $shiftDef['id']]);
                    $required = $req->fetchColumn();
                    if ($required === false || $required === null) {
                        $required = 3; // Default if no requirement found
                    }
                    
                    $scheduleShiftStmt->execute([$scheduleId, $date, $shiftDef['id'], $required]);
                    $shiftId = (int)$pdo->lastInsertId();
                    $shiftCount++;
                    
                    // Assign employees (50-100% of required)
                    if (count($sectionEmployeeIds) > 0) {
                        $assigned = min(rand((int)ceil($required * 0.5), $required), count($sectionEmployeeIds));
                        if ($assigned > 0) {
                            $selectedIndices = array_rand($sectionEmployeeIds, $assigned);
                            $assignedEmployees = is_array($selectedIndices) ? $selectedIndices : [$selectedIndices];
                        } else {
                            $assignedEmployees = [];
                        }
                    } else {
                        $assignedEmployees = [];
                    }
                    
                    $sources = ['MATCHED_REQUEST', 'AUTO_ASSIGNED', 'MANUALLY_ADJUSTED'];
                    
                    foreach ($assignedEmployees as $empIdx) {
                        $empId = $sectionEmployeeIds[$empIdx];
                        $isSenior = in_array($empId, array_column($seniors, 'id'));
                        $source = $sources[array_rand($sources)];
                        
                        $assignmentStmt->execute([$shiftId, $empId, $source, $isSenior ? 1 : 0, '']);
                        $assignmentCount++;
                    }
                }
            }
        }
    }
    
    echo "  ✓ Created {$scheduleCount} schedules\n";
    echo "  ✓ Created {$shiftCount} schedule shifts\n";
    echo "  ✓ Created {$assignmentCount} schedule assignments\n";
    
    echo "\n☕ Step 7: Seeding employee breaks...\n";
    
    $breakStmt = $pdo->prepare("INSERT INTO employee_breaks (employee_id, schedule_shift_id, worked_date, break_start, break_end, is_active) VALUES (?, ?, ?, ?, ?, ?)");
    
    // Get assignments for today and past 7 days
    $today = new DateTimeImmutable();
    $breakCount = 0;
    
    for ($daysAgo = 0; $daysAgo < 7; $daysAgo++) {
        $date = $today->modify("-{$daysAgo} days")->format('Y-m-d');
        
        // Get assignments for this date
        $assignments = $pdo->prepare("
            SELECT sa.employee_id, sa.schedule_shift_id, ss.shift_date 
            FROM schedule_assignments sa
            INNER JOIN schedule_shifts ss ON sa.schedule_shift_id = ss.id
            WHERE ss.shift_date = ?
        ");
        $assignments->execute([$date]);
        $dayAssignments = $assignments->fetchAll();
        
        // 60-80% of employees take breaks
        if (count($dayAssignments) > 0) {
            $breakCount = (int)ceil(count($dayAssignments) * rand(60, 80) / 100);
            $breakCount = min($breakCount, count($dayAssignments));
            if ($breakCount > 0) {
                $selectedIndices = array_rand($dayAssignments, $breakCount);
                $breakEmployees = is_array($selectedIndices) ? $selectedIndices : [$selectedIndices];
            } else {
                $breakEmployees = [];
            }
        } else {
            $breakEmployees = [];
        }
        
        foreach ($breakEmployees as $idx) {
            $assignment = $dayAssignments[$idx];
            $empId = $assignment['employee_id'];
            $shiftId = $assignment['schedule_shift_id'];
            
            // Break times (30-60 min break, some delayed)
            $breakStart = new DateTimeImmutable($date . ' ' . rand(10, 16) . ':' . str_pad((string)rand(0, 59), 2, '0', STR_PAD_LEFT));
            $delay = rand(0, 30); // 0-30 min delay
            if ($delay > 0) {
                $breakStart = $breakStart->modify("+{$delay} minutes");
            }
            $breakEnd = $breakStart->modify('+' . rand(30, 60) . ' minutes');
            
            $isActive = ($daysAgo === 0 && rand(0, 1)) ? 1 : 0;
            
            $breakStmt->execute([
                $empId,
                $shiftId,
                $date,
                $breakStart->format('Y-m-d H:i:s'),
                $isActive ? null : $breakEnd->format('Y-m-d H:i:s'),
                $isActive,
            ]);
            $breakCount++;
        }
    }
    
    echo "  ✓ Created {$breakCount} employee breaks\n";
    
    echo "\n🔔 Step 8: Seeding notifications...\n";
    
    $notifStmt = $pdo->prepare("INSERT INTO notifications (user_id, type, title, body, is_read, created_at, read_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    $allUsers = $pdo->query("SELECT id FROM users ORDER BY id")->fetchAll(PDO::FETCH_COLUMN);
    $notifTypes = ['SHIFT_REMINDER', 'SCHEDULE_PUBLISHED', 'REQUEST_STATUS'];
    $titles = [
        'SHIFT_REMINDER' => 'Shift Reminder',
        'SCHEDULE_PUBLISHED' => 'New Schedule Published',
        'REQUEST_STATUS' => 'Request Status Updated',
    ];
    $bodies = [
        'SHIFT_REMINDER' => 'You have a shift scheduled tomorrow',
        'SCHEDULE_PUBLISHED' => 'A new schedule has been published for next week',
        'REQUEST_STATUS' => 'Your shift request has been reviewed',
    ];
    
    $notifCount = 0;
    foreach ($allUsers as $userId) {
        // Each user gets 0-5 notifications
        $numNotifs = rand(0, 5);
        for ($i = 0; $i < $numNotifs; $i++) {
            $type = $notifTypes[array_rand($notifTypes)];
            $isRead = rand(0, 1);
            $createdAt = (new DateTimeImmutable())->modify('-' . rand(0, 14) . ' days')->format('Y-m-d H:i:s');
            $readAt = $isRead ? (new DateTimeImmutable($createdAt))->modify('+' . rand(1, 7) . ' days')->format('Y-m-d H:i:s') : null;
            
            $notifStmt->execute([
                $userId,
                $type,
                $titles[$type],
                $bodies[$type],
                $isRead,
                $createdAt,
                $readAt,
            ]);
            $notifCount++;
        }
    }
    
    echo "  ✓ Created {$notifCount} notifications\n";
    
    // Commit transaction
    $pdo->commit();
    
    echo "\n✅ Database reset and seeding completed successfully!\n";
    echo "\n📊 Summary:\n";
    echo "   - Weeks: " . count($weekIds) . "\n";
    echo "   - Users: " . count($allUsers) . "\n";
    echo "   - Employees: " . count($allEmployees) . "\n";
    echo "   - Shift Requirements: {$reqCount}\n";
    echo "   - Shift Requests: {$requestCount}\n";
    echo "   - Schedules: {$scheduleCount}\n";
    echo "   - Schedule Shifts: {$shiftCount}\n";
    echo "   - Schedule Assignments: {$assignmentCount}\n";
    echo "   - Employee Breaks: {$breakCount}\n";
    echo "   - Notifications: {$notifCount}\n";
    echo "\n🔑 Default Credentials:\n";
    echo "   Director: username='director', password='password'\n";
    echo "   All others: username='{role}_{section}_{number}', password='password123'\n";
    
} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    exit(1);
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

