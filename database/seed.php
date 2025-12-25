<?php
declare(strict_types=1);

/**
 * Big Test Data Seeder
 * Usage: php database/seed.php
 */

require_once __DIR__ . '/../app/Core/config.php';

$pdo = db();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec("USE ShiftSchedulerDB");

$email = 'mouhamad.ayoubi.dev@gmail.com';
$passwordHash = password_hash('SeededPass123!', PASSWORD_BCRYPT);

$pdo->beginTransaction();

try {
    // ---------------------------------------------------------------------
    // Roles
    // ---------------------------------------------------------------------
    $roles = [
        ['Director', 'Read-only access to both sections'],
        ['Team Leader', 'Full CRUD access for assigned section'],
        ['Supervisor', 'Read-only access for assigned section'],
        ['Senior', 'Shift leader for today operations'],
        ['Employee', 'Shift request and schedule access'],
    ];
    $roleInsert = $pdo->prepare("INSERT INTO roles (role_name, description) VALUES (?, ?) ON DUPLICATE KEY UPDATE description = VALUES(description)");
    foreach ($roles as $role) {
        $roleInsert->execute($role);
    }

    $roleRows = $pdo->query("SELECT id, role_name FROM roles")->fetchAll(PDO::FETCH_ASSOC);
    $roleIds = [];
    foreach ($roleRows as $row) {
        $roleIds[$row['role_name']] = (int)$row['id'];
    }

    // ---------------------------------------------------------------------
    // Shift types
    // ---------------------------------------------------------------------
    $shiftTypes = [
        ['AM', 'Morning', '06:00:00', '14:00:00', 8.00],
        ['MID', 'Mid', '10:00:00', '18:00:00', 8.00],
        ['PM', 'Evening', '14:00:00', '22:00:00', 8.00],
        ['MIDNIGHT', 'Midnight', '18:00:00', '02:00:00', 8.00],
        ['OVERNIGHT', 'Overnight', '22:00:00', '06:00:00', 8.00],
    ];
    $shiftTypeInsert = $pdo->prepare("INSERT INTO shift_types (code, name, start_time, end_time, duration_hours) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE name = VALUES(name), start_time = VALUES(start_time), end_time = VALUES(end_time), duration_hours = VALUES(duration_hours)");
    foreach ($shiftTypes as $shiftType) {
        $shiftTypeInsert->execute($shiftType);
    }

    $shiftTypeRows = $pdo->query("SELECT id, code FROM shift_types")->fetchAll(PDO::FETCH_ASSOC);
    $shiftTypeIds = [];
    foreach ($shiftTypeRows as $row) {
        $shiftTypeIds[$row['code']] = (int)$row['id'];
    }

    // ---------------------------------------------------------------------
    // Shift definitions (10+)
    // ---------------------------------------------------------------------
    $shiftDefinitions = [
        ['AM Shift', '06:00:00', '14:00:00', 8.00, 'AM', '#38bdf8', $shiftTypeIds['AM'] ?? null],
        ['Mid Shift', '10:00:00', '18:00:00', 8.00, 'MID', '#6366f1', $shiftTypeIds['MID'] ?? null],
        ['PM Shift', '14:00:00', '22:00:00', 8.00, 'PM', '#f97316', $shiftTypeIds['PM'] ?? null],
        ['Overnight Shift', '22:00:00', '06:00:00', 8.00, 'OVERNIGHT', '#334155', $shiftTypeIds['OVERNIGHT'] ?? null],
        ['Midnight Shift', '18:00:00', '02:00:00', 8.00, 'MIDNIGHT', '#0f172a', $shiftTypeIds['MIDNIGHT'] ?? null],
        ['Day Off', null, null, 0.00, 'OFF', '#94a3b8', null],
        ['Short AM', '07:00:00', '13:00:00', 6.00, 'AM', '#7dd3fc', $shiftTypeIds['AM'] ?? null],
        ['Short PM', '15:00:00', '21:00:00', 6.00, 'PM', '#fb923c', $shiftTypeIds['PM'] ?? null],
        ['Training Mid', '11:00:00', '15:00:00', 4.00, 'MID', '#a855f7', $shiftTypeIds['MID'] ?? null],
        ['Support Overnight', '21:00:00', '07:00:00', 10.00, 'OVERNIGHT', '#1e293b', $shiftTypeIds['OVERNIGHT'] ?? null],
    ];
    $shiftDefinitionSelect = $pdo->prepare("SELECT id FROM shift_definitions WHERE shift_name = ? LIMIT 1");
    $shiftDefinitionInsert = $pdo->prepare("INSERT INTO shift_definitions (shift_name, start_time, end_time, duration_hours, category, color_code, shift_type_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $shiftDefinitionIds = [];
    foreach ($shiftDefinitions as $definition) {
        $shiftDefinitionSelect->execute([$definition[0]]);
        $existing = $shiftDefinitionSelect->fetch(PDO::FETCH_ASSOC);
        if (!$existing) {
            $shiftDefinitionInsert->execute($definition);
            $definitionId = (int)$pdo->lastInsertId();
        } else {
            $definitionId = (int)$existing['id'];
        }
        $shiftDefinitionIds[$definition[0]] = $definitionId;
    }

    // ---------------------------------------------------------------------
    // Schedule patterns (6)
    // ---------------------------------------------------------------------
    $schedulePatterns = [
        ['5x2', 5, 2, 8.00, '5 days on / 2 days off'],
        ['6x1', 6, 1, 8.00, '6 days on / 1 day off'],
        ['4x3', 4, 3, 8.00, '4 days on / 3 days off'],
        ['3x4', 3, 4, 8.00, '3 days on / 4 days off'],
        ['12h-3x4', 3, 4, 12.00, '3 days on / 4 days off with 12-hour shifts'],
        ['Rotating', 5, 2, 8.00, 'Rotating weekdays and weekends'],
    ];
    $patternSelect = $pdo->prepare("SELECT id FROM schedule_patterns WHERE name = ? LIMIT 1");
    $patternInsert = $pdo->prepare("INSERT INTO schedule_patterns (name, work_days_per_week, off_days_per_week, default_shift_duration_hours, description) VALUES (?, ?, ?, ?, ?)");
    foreach ($schedulePatterns as $pattern) {
        $patternSelect->execute([$pattern[0]]);
        if (!$patternSelect->fetch(PDO::FETCH_ASSOC)) {
            $patternInsert->execute($pattern);
        }
    }
    $patternRows = $pdo->query("SELECT id, name FROM schedule_patterns")->fetchAll(PDO::FETCH_ASSOC);
    $patternIds = [];
    foreach ($patternRows as $row) {
        $patternIds[$row['name']] = (int)$row['id'];
    }

    // ---------------------------------------------------------------------
    // Companies and sections
    // ---------------------------------------------------------------------
    $companies = [
        ['Atlas Retail', 'atlas-retail', 'America/New_York', 'United States', '51-200'],
        ['Nova Logistics', 'nova-logistics', 'Europe/London', 'United Kingdom', '11-50'],
        ['Horizon Care', 'horizon-care', 'Asia/Dubai', 'United Arab Emirates', '201-500'],
    ];

    $companyInsert = $pdo->prepare("INSERT INTO companies (company_name, company_slug, admin_email, admin_password_hash, timezone, country, company_size, status, email_verified_at, payment_status, payment_completed_at, onboarding_completed_at) VALUES (?, ?, ?, ?, ?, ?, ?, 'ACTIVE', NOW(), 'COMPLETED', NOW(), NOW()) ON DUPLICATE KEY UPDATE company_name = VALUES(company_name), admin_email = VALUES(admin_email), admin_password_hash = VALUES(admin_password_hash), timezone = VALUES(timezone), country = VALUES(country), company_size = VALUES(company_size), status = 'ACTIVE'");
    $companySelect = $pdo->prepare("SELECT id FROM companies WHERE company_slug = ? LIMIT 1");

    $sectionInsert = $pdo->prepare("INSERT IGNORE INTO sections (section_name, company_id) VALUES (?, ?)");
    $sectionSelect = $pdo->prepare("SELECT id FROM sections WHERE section_name = ? AND company_id = ? LIMIT 1");

    $userInsert = $pdo->prepare("INSERT IGNORE INTO users (username, password_hash, email, company_id, is_active) VALUES (?, ?, ?, ?, 1)");
    $userSelect = $pdo->prepare("SELECT id FROM users WHERE username = ? AND company_id = ? LIMIT 1");

    $userRoleInsert = $pdo->prepare("INSERT IGNORE INTO user_roles (user_id, role_id, section_id) VALUES (?, ?, ?)");
    $userRoleSelect = $pdo->prepare("SELECT id FROM user_roles WHERE user_id = ? AND role_id = ? AND section_id = ? LIMIT 1");

    $employeeUpsert = $pdo->prepare("INSERT INTO employees (user_role_id, employee_code, full_name, email, is_senior, seniority_level, is_active) VALUES (?, ?, ?, ?, ?, ?, 1) ON DUPLICATE KEY UPDATE user_role_id = VALUES(user_role_id), full_name = VALUES(full_name), email = VALUES(email), is_senior = VALUES(is_senior), seniority_level = VALUES(seniority_level), is_active = 1");
    $employeeSelect = $pdo->prepare("SELECT id FROM employees WHERE employee_code = ? LIMIT 1");

    $companiesData = [];

    foreach ($companies as $companyIndex => $company) {
        $companyInsert->execute([$company[0], $company[1], $email, $passwordHash, $company[2], $company[3], $company[4]]);
        $companySelect->execute([$company[1]]);
        $companyId = (int)$companySelect->fetch(PDO::FETCH_ASSOC)['id'];

        $sections = ['Operations', 'Support'];
        $sectionIds = [];
        foreach ($sections as $sectionName) {
            $sectionInsert->execute([$sectionName, $companyId]);
            $sectionSelect->execute([$sectionName, $companyId]);
            $sectionIds[$sectionName] = (int)$sectionSelect->fetch(PDO::FETCH_ASSOC)['id'];
        }

        $directorUsername = $company[1] . '-director';
        $userInsert->execute([$directorUsername, $passwordHash, $email, $companyId]);
        $userSelect->execute([$directorUsername, $companyId]);
        $directorUserId = (int)$userSelect->fetch(PDO::FETCH_ASSOC)['id'];

        foreach ($sectionIds as $sectionId) {
            $userRoleInsert->execute([$directorUserId, $roleIds['Director'], $sectionId]);
        }

        $sectionEmployees = [];
        $sectionLeaders = [];
        $employeeCounter = 1;

        foreach ($sectionIds as $sectionName => $sectionId) {
            $slugPrefix = strtoupper(substr($company[1], 0, 3));
            $sectionCode = strtoupper(substr($sectionName, 0, 3));

            $teamLeaderUsername = $company[1] . '-tl-' . strtolower($sectionCode);
            $userInsert->execute([$teamLeaderUsername, $passwordHash, $email, $companyId]);
            $userSelect->execute([$teamLeaderUsername, $companyId]);
            $teamLeaderUserId = (int)$userSelect->fetch(PDO::FETCH_ASSOC)['id'];

            $userRoleInsert->execute([$teamLeaderUserId, $roleIds['Team Leader'], $sectionId]);
            $userRoleSelect->execute([$teamLeaderUserId, $roleIds['Team Leader'], $sectionId]);
            $teamLeaderRoleId = (int)$userRoleSelect->fetch(PDO::FETCH_ASSOC)['id'];

            $teamLeaderCode = $slugPrefix . '-' . $sectionCode . '-TL01';
            $teamLeaderName = ($sectionName === 'Operations' ? 'Alex' : 'Taylor') . ' ' . ($companyIndex + 1) . ' Lead';
            $employeeUpsert->execute([$teamLeaderRoleId, $teamLeaderCode, $teamLeaderName, $email, 0, 4]);
            $employeeSelect->execute([$teamLeaderCode]);
            $teamLeaderEmployeeId = (int)$employeeSelect->fetch(PDO::FETCH_ASSOC)['id'];
            $sectionLeaders[$sectionId] = $teamLeaderEmployeeId;

            $supervisorUsername = $company[1] . '-sup-' . strtolower($sectionCode);
            $userInsert->execute([$supervisorUsername, $passwordHash, $email, $companyId]);
            $userSelect->execute([$supervisorUsername, $companyId]);
            $supervisorUserId = (int)$userSelect->fetch(PDO::FETCH_ASSOC)['id'];

            $userRoleInsert->execute([$supervisorUserId, $roleIds['Supervisor'], $sectionId]);
            $userRoleSelect->execute([$supervisorUserId, $roleIds['Supervisor'], $sectionId]);
            $supervisorRoleId = (int)$userRoleSelect->fetch(PDO::FETCH_ASSOC)['id'];

            $supervisorCode = $slugPrefix . '-' . $sectionCode . '-SUP01';
            $supervisorName = ($sectionName === 'Operations' ? 'Jordan' : 'Casey') . ' ' . ($companyIndex + 1) . ' Supervisor';
            $employeeUpsert->execute([$supervisorRoleId, $supervisorCode, $supervisorName, $email, 0, 3]);
            $employeeSelect->execute([$supervisorCode]);
            $supervisorEmployeeId = (int)$employeeSelect->fetch(PDO::FETCH_ASSOC)['id'];

            $seniorUsername = $company[1] . '-senior-' . strtolower($sectionCode);
            $userInsert->execute([$seniorUsername, $passwordHash, $email, $companyId]);
            $userSelect->execute([$seniorUsername, $companyId]);
            $seniorUserId = (int)$userSelect->fetch(PDO::FETCH_ASSOC)['id'];

            $userRoleInsert->execute([$seniorUserId, $roleIds['Senior'], $sectionId]);
            $userRoleSelect->execute([$seniorUserId, $roleIds['Senior'], $sectionId]);
            $seniorRoleId = (int)$userRoleSelect->fetch(PDO::FETCH_ASSOC)['id'];

            $seniorCode = $slugPrefix . '-' . $sectionCode . '-SNR01';
            $seniorName = ($sectionName === 'Operations' ? 'Morgan' : 'Riley') . ' ' . ($companyIndex + 1) . ' Senior';
            $employeeUpsert->execute([$seniorRoleId, $seniorCode, $seniorName, $email, 1, 5]);
            $employeeSelect->execute([$seniorCode]);
            $seniorEmployeeId = (int)$employeeSelect->fetch(PDO::FETCH_ASSOC)['id'];

            $sectionEmployees[$sectionId] = [$seniorEmployeeId];

            $firstNames = ['Sam', 'Jamie', 'Robin', 'Kris', 'Jules', 'Pat', 'Sky', 'Avery', 'Reese', 'Quinn'];
            $lastNames = ['Smith', 'Johnson', 'Lee', 'Patel', 'Garcia', 'Martin', 'Lewis', 'Walker', 'Young', 'King'];

            for ($i = 0; $i < 5; $i++) {
                $username = $company[1] . '-emp-' . strtolower($sectionCode) . '-' . ($employeeCounter + $i);
                $userInsert->execute([$username, $passwordHash, $email, $companyId]);
                $userSelect->execute([$username, $companyId]);
                $employeeUserId = (int)$userSelect->fetch(PDO::FETCH_ASSOC)['id'];

                $userRoleInsert->execute([$employeeUserId, $roleIds['Employee'], $sectionId]);
                $userRoleSelect->execute([$employeeUserId, $roleIds['Employee'], $sectionId]);
                $employeeRoleId = (int)$userRoleSelect->fetch(PDO::FETCH_ASSOC)['id'];

                $employeeCode = $slugPrefix . '-' . $sectionCode . '-E' . str_pad((string)($employeeCounter + $i), 3, '0', STR_PAD_LEFT);
                $fullName = $firstNames[($employeeCounter + $i) % count($firstNames)] . ' ' . $lastNames[($employeeCounter + $i) % count($lastNames)] . ' ' . ($companyIndex + 1) . $sectionCode;
                $employeeUpsert->execute([$employeeRoleId, $employeeCode, $fullName, $email, 0, 1 + (($employeeCounter + $i) % 3)]);
                $employeeSelect->execute([$employeeCode]);
                $employeeId = (int)$employeeSelect->fetch(PDO::FETCH_ASSOC)['id'];

                $sectionEmployees[$sectionId][] = $employeeId;
            }

            $employeeCounter += 5;
        }

        $companiesData[] = [
            'id' => $companyId,
            'sections' => $sectionIds,
            'leaders' => $sectionLeaders,
            'employees' => $sectionEmployees,
        ];
    }

    // ---------------------------------------------------------------------
    // Weeks (8 per company)
    // ---------------------------------------------------------------------
    $weekInsert = $pdo->prepare("INSERT INTO weeks (company_id, week_start_date, week_end_date, is_locked_for_requests, lock_reason) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE week_end_date = VALUES(week_end_date), is_locked_for_requests = VALUES(is_locked_for_requests), lock_reason = VALUES(lock_reason)");
    $weekSelect = $pdo->prepare("SELECT id FROM weeks WHERE company_id = ? AND week_start_date = ? LIMIT 1");

    $weekStartBase = new DateTime('monday this week');
    $weekIdsByCompany = [];

    foreach ($companiesData as $companyData) {
        $weekIdsByCompany[$companyData['id']] = [];
        for ($i = -4; $i < 4; $i++) {
            $start = (clone $weekStartBase)->modify(($i >= 0 ? '+' : '') . $i . ' week');
            $end = (clone $start)->modify('+6 days');
            $isLocked = $i < -1 ? 1 : 0;
            $lockReason = $isLocked ? 'Archived week' : null;
            $weekInsert->execute([$companyData['id'], $start->format('Y-m-d'), $end->format('Y-m-d'), $isLocked, $lockReason]);
            $weekSelect->execute([$companyData['id'], $start->format('Y-m-d')]);
            $weekId = (int)$weekSelect->fetch(PDO::FETCH_ASSOC)['id'];
            $weekIdsByCompany[$companyData['id']][] = [
                'id' => $weekId,
                'start' => $start,
                'end' => $end,
                'index' => $i,
            ];
        }
    }

    // ---------------------------------------------------------------------
    // Shift requirements
    // ---------------------------------------------------------------------
    $requirementInsert = $pdo->prepare("INSERT INTO shift_requirements (company_id, week_id, section_id, shift_date, shift_type_id, required_count) VALUES (?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE required_count = VALUES(required_count)");

    $shiftTypeOrder = ['AM', 'MID', 'PM', 'OVERNIGHT'];

    foreach ($companiesData as $companyData) {
        foreach ($weekIdsByCompany[$companyData['id']] as $weekData) {
            foreach ($companyData['sections'] as $sectionId) {
                for ($day = 0; $day < 7; $day++) {
                    $shiftDate = (clone $weekData['start'])->modify('+' . $day . ' day')->format('Y-m-d');
                    foreach ($shiftTypeOrder as $index => $shiftTypeCode) {
                        $required = 2 + (($day + $index) % 2);
                        if ($shiftTypeCode === 'OVERNIGHT') {
                            $required = 1 + (($day + $index) % 2);
                        }
                        $requirementInsert->execute([
                            $companyData['id'],
                            $weekData['id'],
                            $sectionId,
                            $shiftDate,
                            $shiftTypeIds[$shiftTypeCode] ?? null,
                            $required,
                        ]);
                    }
                }
            }
        }
    }

    // ---------------------------------------------------------------------
    // Schedules and schedule shifts
    // ---------------------------------------------------------------------
    $scheduleInsert = $pdo->prepare("INSERT INTO schedules (company_id, week_id, section_id, generated_by_admin_id, status, notes) VALUES (?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE generated_by_admin_id = VALUES(generated_by_admin_id), status = VALUES(status), notes = VALUES(notes)");
    $scheduleSelect = $pdo->prepare("SELECT id FROM schedules WHERE company_id = ? AND week_id = ? AND section_id = ? LIMIT 1");

    $scheduleShiftSelect = $pdo->prepare("SELECT id FROM schedule_shifts WHERE schedule_id = ? AND shift_date = ? AND shift_definition_id = ? LIMIT 1");
    $scheduleShiftInsert = $pdo->prepare("INSERT INTO schedule_shifts (schedule_id, shift_date, shift_definition_id, required_count) VALUES (?, ?, ?, ?)");

    $assignmentInsert = $pdo->prepare("INSERT IGNORE INTO schedule_assignments (schedule_shift_id, employee_id, assignment_source, is_senior, notes) VALUES (?, ?, ?, ?, ?)");

    $definitionMap = [
        'AM' => $shiftDefinitionIds['AM Shift'],
        'MID' => $shiftDefinitionIds['Mid Shift'],
        'PM' => $shiftDefinitionIds['PM Shift'],
        'OVERNIGHT' => $shiftDefinitionIds['Overnight Shift'],
        'OFF' => $shiftDefinitionIds['Day Off'],
    ];

    $scheduleShiftCount = 0;
    $assignmentCount = 0;

    foreach ($companiesData as $companyData) {
        $companyId = $companyData['id'];
        foreach ($weekIdsByCompany[$companyId] as $weekData) {
            $status = $weekData['index'] < 0 ? 'FINAL' : 'DRAFT';
            foreach ($companyData['sections'] as $sectionId) {
                $generatedBy = $companyData['leaders'][$sectionId] ?? array_values($companyData['employees'][$sectionId])[0];
                $scheduleInsert->execute([$companyId, $weekData['id'], $sectionId, $generatedBy, $status, 'Seeded schedule data']);
                $scheduleSelect->execute([$companyId, $weekData['id'], $sectionId]);
                $scheduleId = (int)$scheduleSelect->fetch(PDO::FETCH_ASSOC)['id'];

                $employeePool = $companyData['employees'][$sectionId];
                $employeeCount = count($employeePool);

                for ($day = 0; $day < 7; $day++) {
                    $shiftDate = (clone $weekData['start'])->modify('+' . $day . ' day')->format('Y-m-d');
                    foreach ($shiftTypeOrder as $shiftIndex => $shiftTypeCode) {
                        $definitionId = $definitionMap[$shiftTypeCode];
                        $requiredCount = 2 + (($day + $shiftIndex) % 2);
                        if ($shiftTypeCode === 'OVERNIGHT') {
                            $requiredCount = 1 + (($day + $shiftIndex) % 2);
                        }

                        $scheduleShiftSelect->execute([$scheduleId, $shiftDate, $definitionId]);
                        $scheduleShiftRow = $scheduleShiftSelect->fetch(PDO::FETCH_ASSOC);
                        if (!$scheduleShiftRow) {
                            $scheduleShiftInsert->execute([$scheduleId, $shiftDate, $definitionId, $requiredCount]);
                            $scheduleShiftId = (int)$pdo->lastInsertId();
                        } else {
                            $scheduleShiftId = (int)$scheduleShiftRow['id'];
                        }

                        $scheduleShiftCount++;

                        $assignCount = $requiredCount;
                        if (($scheduleShiftCount % 5) === 0) {
                            $assignCount = max(0, $requiredCount - 1);
                        }

                        for ($a = 0; $a < $assignCount; $a++) {
                            $employeeId = $employeePool[($scheduleShiftCount + $a) % $employeeCount];
                            $isSenior = ($a === 0 && $shiftTypeCode !== 'OVERNIGHT') ? 1 : 0;
                            $assignmentInsert->execute([
                                $scheduleShiftId,
                                $employeeId,
                                $assignCount === $requiredCount ? 'AUTO_ASSIGNED' : 'MANUALLY_ADJUSTED',
                                $isSenior,
                                $assignCount === $requiredCount ? 'Auto-assigned by seed' : 'Coverage gap (seeded)',
                            ]);
                            $assignmentCount++;
                        }
                    }

                    if ($day === 6) {
                        $scheduleShiftSelect->execute([$scheduleId, $shiftDate, $definitionMap['OFF']]);
                        if (!$scheduleShiftSelect->fetch(PDO::FETCH_ASSOC)) {
                            $scheduleShiftInsert->execute([$scheduleId, $shiftDate, $definitionMap['OFF'], 0]);
                        }
                    }
                }
            }
        }
    }

    // ---------------------------------------------------------------------
    // Shift requests (40+)
    // ---------------------------------------------------------------------
    $requestInsert = $pdo->prepare(
        "INSERT INTO shift_requests (employee_id, week_id, request_date, shift_definition_id, is_day_off, schedule_pattern_id, reason, importance_level, status, submitted_at, reviewed_by_admin_id, reviewed_at)
         SELECT ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
         WHERE NOT EXISTS (
             SELECT 1 FROM shift_requests
             WHERE employee_id = ? AND week_id = ? AND request_date = ? AND COALESCE(shift_definition_id, 0) = COALESCE(?, 0) AND is_day_off = ? AND reason = ?
         )"
    );

    $requestTypes = [
        ['Vacation', 1, 'HIGH'],
        ['Medical', 1, 'EMERGENCY'],
        ['Swap', 0, 'MEDIUM'],
        ['Overtime', 0, 'LOW'],
    ];

    $requestCount = 0;
    foreach ($companiesData as $companyData) {
        $companyId = $companyData['id'];
        $weeks = $weekIdsByCompany[$companyId];
        foreach ($companyData['employees'] as $sectionId => $employees) {
            $reviewerId = $companyData['leaders'][$sectionId];
            for ($i = 0; $i < 10; $i++) {
                $employeeId = $employees[$i % count($employees)];
                $weekData = $weeks[$i % count($weeks)];
                $requestDate = (clone $weekData['start'])->modify('+' . (($i % 6) + 1) . ' day')->format('Y-m-d');
                $requestType = $requestTypes[$i % count($requestTypes)];
                $patternId = $patternIds[array_keys($patternIds)[$i % count($patternIds)]];
                $statusOptions = ['PENDING', 'APPROVED', 'DECLINED'];
                $status = $statusOptions[$i % count($statusOptions)];
                $reviewedAt = $status === 'PENDING' ? null : (clone $weekData['start'])->modify('+2 days')->format('Y-m-d H:i:s');
                $shiftDefinitionId = $requestType[0] === 'Swap' ? $shiftDefinitionIds['PM Shift'] : ($requestType[0] === 'Overtime' ? $shiftDefinitionIds['Mid Shift'] : null);
                $reason = $requestType[0] . ' request for ' . $requestDate;
                $submittedAt = (clone $weekData['start'])->modify('-1 day')->format('Y-m-d H:i:s');

                $requestInsert->execute([
                    $employeeId,
                    $weekData['id'],
                    $requestDate,
                    $shiftDefinitionId,
                    $requestType[1],
                    $patternId,
                    $reason,
                    $requestType[2],
                    $status,
                    $submittedAt,
                    $status === 'PENDING' ? null : $reviewerId,
                    $reviewedAt,
                    $employeeId,
                    $weekData['id'],
                    $requestDate,
                    $shiftDefinitionId,
                    $requestType[1],
                    $reason,
                ]);
                $requestCount++;
            }
        }
    }

    // ---------------------------------------------------------------------
    // Breaks (50+)
    // ---------------------------------------------------------------------
    $breakInsert = $pdo->prepare("INSERT INTO employee_breaks (employee_id, schedule_shift_id, worked_date, break_start, break_end, is_active) VALUES (?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE schedule_shift_id = VALUES(schedule_shift_id), break_start = VALUES(break_start), break_end = VALUES(break_end), is_active = VALUES(is_active)");

    $assignmentRows = $pdo->query(
        "SELECT sa.employee_id, sa.schedule_shift_id, ss.shift_date
         FROM schedule_assignments sa
         INNER JOIN schedule_shifts ss ON ss.id = sa.schedule_shift_id
         ORDER BY ss.shift_date
         LIMIT 70"
    )->fetchAll(PDO::FETCH_ASSOC);

    foreach ($assignmentRows as $index => $row) {
        $workedDate = $row['shift_date'];
        $startTime = $index % 2 === 0 ? '11:15:00' : '15:30:00';
        $breakStart = $workedDate . ' ' . $startTime;
        $breakEnd = ($index % 7 === 0) ? null : date('Y-m-d H:i:s', strtotime($breakStart . ' + ' . (30 + (($index % 3) * 15)) . ' minutes'));
        $isActive = $breakEnd === null ? 1 : 0;

        if ($index % 9 === 0 && $breakEnd !== null) {
            $breakEnd = date('Y-m-d H:i:s', strtotime($breakStart . ' + 75 minutes'));
        }

        $breakInsert->execute([
            $row['employee_id'],
            $row['schedule_shift_id'],
            $workedDate,
            $breakStart,
            $breakEnd,
            $isActive,
        ]);
    }

    // ---------------------------------------------------------------------
    // Notifications
    // ---------------------------------------------------------------------
    $notificationInsert = $pdo->prepare(
        "INSERT INTO notifications (company_id, user_id, type, title, body, is_read, created_at, read_at)
         SELECT ?, ?, ?, ?, ?, ?, ?, ?
         WHERE NOT EXISTS (
             SELECT 1 FROM notifications WHERE user_id = ? AND type = ? AND title = ? AND created_at = ?
         )"
    );

    $userRows = $pdo->query("SELECT id, company_id FROM users ORDER BY id LIMIT 20")->fetchAll(PDO::FETCH_ASSOC);
    $notificationTypes = ['SHIFT_REMINDER', 'SCHEDULE_PUBLISHED', 'REQUEST_STATUS'];

    foreach ($userRows as $index => $userRow) {
        $type = $notificationTypes[$index % count($notificationTypes)];
        $createdAt = (new DateTime('now'))->modify('-' . ($index % 5) . ' days')->format('Y-m-d 09:00:00');
        $title = $type === 'SHIFT_REMINDER' ? 'Upcoming shift reminder' : ($type === 'SCHEDULE_PUBLISHED' ? 'Schedule published' : 'Request status update');
        $body = $type === 'SHIFT_REMINDER' ? 'Your next shift starts soon. Check the schedule for details.' : ($type === 'SCHEDULE_PUBLISHED' ? 'Your weekly schedule is now available.' : 'Your shift request has been reviewed.');
        $isRead = $index % 3 === 0 ? 1 : 0;
        $readAt = $isRead ? $createdAt : null;

        $notificationInsert->execute([
            $userRow['company_id'],
            $userRow['id'],
            $type,
            $title,
            $body,
            $isRead,
            $createdAt,
            $readAt,
            $userRow['id'],
            $type,
            $title,
            $createdAt,
        ]);
    }

    // ---------------------------------------------------------------------
    // FCM tokens
    // ---------------------------------------------------------------------
    $tokenInsert = $pdo->prepare("INSERT IGNORE INTO fcm_tokens (user_id, employee_id, role, token, device_type, last_seen) VALUES (?, ?, ?, ?, 'desktop_web', NOW())");

    $fcmTargets = $pdo->query(
        "SELECT u.id AS user_id, e.id AS employee_id, r.role_name
         FROM users u
         INNER JOIN user_roles ur ON ur.user_id = u.id
         INNER JOIN roles r ON r.id = ur.role_id
         LEFT JOIN employees e ON e.user_role_id = ur.id
         ORDER BY u.id
         LIMIT 5"
    )->fetchAll(PDO::FETCH_ASSOC);

    foreach ($fcmTargets as $index => $target) {
        $tokenInsert->execute([
            $target['user_id'],
            $target['employee_id'],
            $target['role_name'],
            'seed-token-' . $target['user_id'] . '-' . ($index + 1),
        ]);
    }

    $pdo->commit();
    echo "✅ Seeded big test dataset successfully.\n";
} catch (Throwable $e) {
    $pdo->rollBack();
    throw $e;
}
