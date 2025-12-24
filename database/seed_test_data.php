<?php
declare(strict_types=1);

/**
 * Seed Test Data Script
 * Creates a complete test company with employees, schedules, and data
 * Usage: php database/seed_test_data.php
 */

require_once __DIR__ . '/../app/Core/config.php';

$pdo = db();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "🌱 Seeding Test Data\n";
echo str_repeat("=", 50) . "\n\n";

try {
    $pdo->exec("USE ShiftSchedulerDB");
    
    // Check if test company already exists
    $checkStmt = $pdo->prepare("SELECT id FROM companies WHERE company_slug = 'demo-company' LIMIT 1");
    $checkStmt->execute();
    $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    $companyId = $existing ? (int)$existing['id'] : null;
    
    $companyName = "Demo Company";
    $adminEmail = "admin@demo.com";
    $adminPassword = "Demo123!";
    $passwordHash = password_hash($adminPassword, PASSWORD_BCRYPT);
    
    if (!$companyId) {
        echo "📦 Step 1: Creating test company...\n";
        
        // Create test company
        $companyStmt = $pdo->prepare("
            INSERT INTO companies (
                company_name, company_slug, admin_email, admin_password_hash,
                timezone, country, company_size, status, email_verified_at,
                payment_status, payment_completed_at, onboarding_completed_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, 'ACTIVE', NOW(), 'COMPLETED', NOW(), NOW())
        ");
        
        $companyStmt->execute([
            $companyName,
            'demo-company',
            $adminEmail,
            $passwordHash,
            'America/New_York',
            'United States',
            '11-50'
        ]);
        
        $companyId = (int)$pdo->lastInsertId();
        echo "   ✓ Company created (ID: $companyId)\n\n";
    } else {
        echo "📦 Step 1: Using existing company (ID: $companyId)...\n";
        echo "   ✓ Company found\n\n";
    }
    
    echo "👤 Step 2: Creating/checking admin user...\n";
    
    // Check if admin user exists
    $username = "democompany_admin";
    $checkAdminStmt = $pdo->prepare("
        SELECT id FROM users WHERE username = ? AND company_id = ? LIMIT 1
    ");
    $checkAdminStmt->execute([$username, $companyId]);
    $existingAdmin = $checkAdminStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existingAdmin) {
        $userId = (int)$existingAdmin['id'];
        echo "   ✓ Admin user already exists (ID: $userId)\n\n";
    } else {
        // Create admin user
        $userStmt = $pdo->prepare("
            INSERT INTO users (username, password_hash, email, company_id, is_active)
            VALUES (?, ?, ?, ?, 1)
        ");
        $userStmt->execute([$username, $passwordHash, $adminEmail, $companyId]);
        $userId = (int)$pdo->lastInsertId();
        echo "   ✓ Admin user created (ID: $userId)\n\n";
    }
    
    echo "📋 Step 3: Creating/checking sections...\n";
    
    // Get Director role
    $roleStmt = $pdo->prepare("SELECT id FROM roles WHERE role_name = 'Director' LIMIT 1");
    $roleStmt->execute();
    $directorRole = $roleStmt->fetch(PDO::FETCH_ASSOC);
    $directorRoleId = (int)$directorRole['id'];
    
    // Check for existing sections
    $checkSectionStmt = $pdo->prepare("
        SELECT id FROM sections WHERE company_id = ? ORDER BY id LIMIT 1
    ");
    $checkSectionStmt->execute([$companyId]);
    $existingSection = $checkSectionStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existingSection) {
        $mainSectionId = (int)$existingSection['id'];
        echo "   ✓ Main section already exists (ID: $mainSectionId)\n\n";
    } else {
        // Create sections
        $sections = [
            ['Main Section', $companyId],
            ['Secondary Section', $companyId]
        ];
        
        $sectionIds = [];
        $sectionStmt = $pdo->prepare("
            INSERT INTO sections (section_name, company_id)
            VALUES (?, ?)
        ");
        
        foreach ($sections as $section) {
            $sectionStmt->execute($section);
            $sectionIds[] = (int)$pdo->lastInsertId();
        }
        
        $mainSectionId = $sectionIds[0];
        echo "   ✓ Sections created (Main: $mainSectionId)\n\n";
    }
    
    echo "🔐 Step 4: Assigning Director role to admin...\n";
    
    // Check if role already assigned
    $checkRoleStmt = $pdo->prepare("
        SELECT id FROM user_roles 
        WHERE user_id = ? AND role_id = ? AND section_id = ? LIMIT 1
    ");
    $checkRoleStmt->execute([$userId, $directorRoleId, $mainSectionId]);
    $existingRole = $checkRoleStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existingRole) {
        echo "   ✓ Director role already assigned\n\n";
    } else {
        // Assign Director role to admin
        $userRoleStmt = $pdo->prepare("
            INSERT INTO user_roles (user_id, role_id, section_id)
            VALUES (?, ?, ?)
        ");
        $userRoleStmt->execute([$userId, $directorRoleId, $mainSectionId]);
        echo "   ✓ Director role assigned\n\n";
    }
    
    echo "👥 Step 5: Creating employees...\n";
    
    // Get roles
    $roleStmt = $pdo->prepare("SELECT id, role_name FROM roles");
    $roleStmt->execute();
    $roles = [];
    while ($row = $roleStmt->fetch(PDO::FETCH_ASSOC)) {
        $roles[$row['role_name']] = (int)$row['id'];
    }
    
    // Create employees
    $employees = [
        ['John Doe', 'john.doe@demo.com', 'Employee', 'EMP001', 0, 1],
        ['Jane Smith', 'jane.smith@demo.com', 'Employee', 'EMP002', 0, 2],
        ['Mike Johnson', 'mike.johnson@demo.com', 'Senior', 'EMP003', 1, 5],
        ['Sarah Williams', 'sarah.williams@demo.com', 'Employee', 'EMP004', 0, 3],
        ['Tom Brown', 'tom.brown@demo.com', 'Team Leader', 'TL001', 0, 4],
        ['Lisa Davis', 'lisa.davis@demo.com', 'Supervisor', 'SUP001', 0, 3],
    ];
    
    $employeeIds = [];
    $defaultPassword = password_hash('TempPass123!', PASSWORD_BCRYPT);
    
    // Check existing employees for this company
    $checkEmpStmt = $pdo->prepare("
        SELECT e.employee_code, e.full_name, u.username 
        FROM employees e
        INNER JOIN user_roles ur ON ur.id = e.user_role_id
        INNER JOIN users u ON u.id = ur.user_id
        INNER JOIN sections s ON s.id = ur.section_id
        WHERE s.company_id = ?
    ");
    $checkEmpStmt->execute([$companyId]);
    $existingEmployees = [];
    while ($row = $checkEmpStmt->fetch(PDO::FETCH_ASSOC)) {
        $existingEmployees[$row['employee_code']] = $row;
    }
    
    $empUserStmt = $pdo->prepare("
        INSERT INTO users (username, password_hash, email, company_id, is_active)
        VALUES (?, ?, ?, ?, 1)
    ");
    
    $checkUserStmt = $pdo->prepare("
        SELECT id FROM users WHERE username = ? AND company_id = ? LIMIT 1
    ");
    
    $empRoleStmt = $pdo->prepare("
        INSERT INTO user_roles (user_id, role_id, section_id)
        VALUES (?, ?, ?)
    ");
    
    $checkRoleStmt = $pdo->prepare("
        SELECT ur.id FROM user_roles ur
        INNER JOIN users u ON u.id = ur.user_id
        WHERE u.username = ? AND ur.section_id = ? LIMIT 1
    ");
    
    $empStmt = $pdo->prepare("
        INSERT INTO employees (user_role_id, employee_code, full_name, email, is_senior, seniority_level, is_active)
        VALUES (?, ?, ?, ?, ?, ?, 1)
    ");
    
    $getEmpIdStmt = $pdo->prepare("
        SELECT e.id FROM employees e
        INNER JOIN user_roles ur ON ur.id = e.user_role_id
        WHERE ur.id = ? LIMIT 1
    ");
    
    foreach ($employees as $idx => $emp) {
        [$fullName, $email, $roleName, $empCode, $isSenior, $seniority] = $emp;
        $empUsername = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '', $fullName)) . '_' . ($idx + 1);
        
        // Check if employee code already exists
        if (isset($existingEmployees[$empCode])) {
            echo "   ⚠ Skipped: $fullName ($roleName) - already exists\n";
            // Get existing employee ID
            $checkRoleStmt->execute([$empUsername, $mainSectionId]);
            $existingRole = $checkRoleStmt->fetch(PDO::FETCH_ASSOC);
            if ($existingRole) {
                $getEmpIdStmt->execute([$existingRole['id']]);
                $existingEmpId = $getEmpIdStmt->fetchColumn();
                if ($existingEmpId && in_array($roleName, ['Employee', 'Senior'])) {
                    $employeeIds[] = (int)$existingEmpId;
                }
            }
            continue;
        }
        
        // Check if user already exists
        $checkUserStmt->execute([$empUsername, $companyId]);
        $existingUser = $checkUserStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existingUser) {
            $empUserId = (int)$existingUser['id'];
        } else {
            // Create user
            $empUserStmt->execute([$empUsername, $defaultPassword, $email, $companyId]);
            $empUserId = (int)$pdo->lastInsertId();
        }
        
        // Check if role already assigned
        $checkRoleStmt->execute([$empUsername, $mainSectionId]);
        $existingRole = $checkRoleStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existingRole) {
            $userRoleId = (int)$existingRole['id'];
        } else {
            // Assign role
            $roleId = $roles[$roleName] ?? $roles['Employee'];
            $empRoleStmt->execute([$empUserId, $roleId, $mainSectionId]);
            $userRoleId = (int)$pdo->lastInsertId();
        }
        
        // Create employee (only for Employee and Senior roles)
        if (in_array($roleName, ['Employee', 'Senior'])) {
            // Check if employee already exists for this user_role_id
            $getEmpIdStmt->execute([$userRoleId]);
            $existingEmpId = $getEmpIdStmt->fetchColumn();
            
            if (!$existingEmpId) {
                $empStmt->execute([$userRoleId, $empCode, $fullName, $email, $isSenior, $seniority]);
                $employeeIds[] = (int)$pdo->lastInsertId();
            } else {
                $employeeIds[] = (int)$existingEmpId;
            }
        }
        
        echo "   ✓ Created: $fullName ($roleName)\n";
    }
    
    echo "\n";
    
    echo "📅 Step 6: Creating weeks and schedules...\n";
    
    // Create current week
    $today = new DateTimeImmutable();
    $weekStart = $today->modify('monday this week')->format('Y-m-d');
    $weekEnd = $today->modify('sunday this week')->format('Y-m-d');
    
    $weekStmt = $pdo->prepare("
        INSERT INTO weeks (week_start_date, week_end_date, company_id)
        VALUES (?, ?, ?)
    ");
    $weekStmt->execute([$weekStart, $weekEnd, $companyId]);
    $weekId = (int)$pdo->lastInsertId();
    echo "   ✓ Week created (ID: $weekId, $weekStart to $weekEnd)\n";
    
    // Get shift definitions
    $shiftDefStmt = $pdo->prepare("SELECT id, shift_name FROM shift_definitions WHERE category != 'OFF' LIMIT 3");
    $shiftDefStmt->execute();
    $shiftDefs = $shiftDefStmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($shiftDefs) && !empty($employeeIds)) {
        // Check if schedule already exists
        $checkScheduleStmt = $pdo->prepare("
            SELECT id FROM schedules 
            WHERE week_id = ? AND section_id = ? AND company_id = ? LIMIT 1
        ");
        $checkScheduleStmt->execute([$weekId, $mainSectionId, $companyId]);
        $existingSchedule = $checkScheduleStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existingSchedule) {
            $scheduleId = (int)$existingSchedule['id'];
            echo "   ✓ Schedule already exists (ID: $scheduleId)\n";
        } else {
            // Create a schedule
            $scheduleStmt = $pdo->prepare("
                INSERT INTO schedules (week_id, section_id, generated_by_admin_id, status, company_id)
                VALUES (?, ?, ?, 'FINAL', ?)
            ");
            $scheduleStmt->execute([$weekId, $mainSectionId, $employeeIds[0], $companyId]);
            $scheduleId = (int)$pdo->lastInsertId();
            echo "   ✓ Schedule created (ID: $scheduleId)\n";
        }
        
        // Create some schedule shifts
        $shiftStmt = $pdo->prepare("
            INSERT INTO schedule_shifts (schedule_id, shift_date, shift_definition_id, required_count)
            VALUES (?, ?, ?, 2)
        ");
        
        $startDate = new DateTimeImmutable($weekStart);
        for ($i = 0; $i < 5; $i++) {
            $date = $startDate->modify("+$i days")->format('Y-m-d');
            $shiftDef = $shiftDefs[$i % count($shiftDefs)];
            $shiftStmt->execute([$scheduleId, $date, $shiftDef['id']]);
        }
        
        // Assign employees to shifts
        $assignStmt = $pdo->prepare("
            INSERT INTO schedule_assignments (schedule_shift_id, employee_id, assignment_source, is_senior)
            VALUES (?, ?, 'AUTO_ASSIGNED', ?)
        ");
        
        $shiftIdsStmt = $pdo->prepare("SELECT id FROM schedule_shifts WHERE schedule_id = ?");
        $shiftIdsStmt->execute([$scheduleId]);
        $shiftIds = $shiftIdsStmt->fetchAll(PDO::FETCH_COLUMN);
        
        foreach ($shiftIds as $shiftIdx => $shiftId) {
            $empIdx = $shiftIdx % count($employeeIds);
            $employee = $employees[$empIdx];
            $isSenior = $employee[4]; // is_senior flag
            
            // Find employee ID for this employee
            $empFindStmt = $pdo->prepare("
                SELECT e.id FROM employees e
                WHERE e.employee_code = ?
            ");
            $empFindStmt->execute([$employee[3]]);
            $empId = $empFindStmt->fetchColumn();
            
            if ($empId) {
                $assignStmt->execute([$shiftId, $empId, $isSenior]);
            }
        }
        
        echo "   ✓ Schedule created with assignments\n";
    }
    
    echo "\n";
    
    echo "📝 Step 7: Creating shift requests...\n";
    
    if (!empty($employeeIds) && $weekId > 0) {
        // Get schedule pattern
        $patternStmt = $pdo->prepare("SELECT id FROM schedule_patterns LIMIT 1");
        $patternStmt->execute();
        $patternId = (int)$patternStmt->fetchColumn();
        
        if ($patternId > 0) {
            $requestStmt = $pdo->prepare("
                INSERT INTO shift_requests (
                    employee_id, week_id, request_date, shift_definition_id,
                    is_day_off, schedule_pattern_id, reason, importance_level, status
                ) VALUES (?, ?, ?, ?, 0, ?, ?, 'MEDIUM', 'PENDING')
            ");
            
            $nextWeekStart = $today->modify('monday next week')->format('Y-m-d');
            $nextWeekEnd = $today->modify('sunday next week')->format('Y-m-d');
            
            $nextWeekStmt = $pdo->prepare("
                INSERT INTO weeks (week_start_date, week_end_date, company_id)
                VALUES (?, ?, ?)
            ");
            // Check if next week exists
            $checkNextWeekStmt = $pdo->prepare("
                SELECT id FROM weeks 
                WHERE week_start_date = ? AND company_id = ? LIMIT 1
            ");
            $checkNextWeekStmt->execute([$nextWeekStart, $companyId]);
            $existingNextWeek = $checkNextWeekStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existingNextWeek) {
                $nextWeekId = (int)$existingNextWeek['id'];
            } else {
                $nextWeekStmt->execute([$nextWeekStart, $nextWeekEnd, $companyId]);
                $nextWeekId = (int)$pdo->lastInsertId();
            }
            
            // Check existing requests
            $checkRequestsStmt = $pdo->prepare("
                SELECT COUNT(*) FROM shift_requests 
                WHERE week_id = ? AND employee_id IN (" . implode(',', array_fill(0, count($employeeIds), '?')) . ")
            ");
            $checkRequestsStmt->execute(array_merge([$nextWeekId], $employeeIds));
            $existingRequestsCount = (int)$checkRequestsStmt->fetchColumn();
            
            if ($existingRequestsCount > 0) {
                echo "   ✓ Shift requests already exist ($existingRequestsCount requests)\n";
            } else {
                // Create a few requests
                for ($i = 0; $i < min(3, count($employeeIds)); $i++) {
                    $requestDate = $today->modify("+" . ($i + 1) . " days")->format('Y-m-d');
                    $shiftDef = $shiftDefs[$i % count($shiftDefs)];
                    $requestStmt->execute([
                        $employeeIds[$i],
                        $nextWeekId,
                        $requestDate,
                        $shiftDef['id'],
                        $patternId,
                        "Test request for " . $employees[$i][0]
                    ]);
                }
                echo "   ✓ Shift requests created (3 requests)\n";
            }
        }
    }
    
    echo "\n";
    
    echo "✅ Test data seeding complete!\n\n";
    
    echo "📋 Test Credentials:\n";
    echo str_repeat("-", 50) . "\n";
    echo "Company: $companyName\n";
    echo "Username: $username\n";
    echo "Password: $adminPassword\n";
    echo "Email: $adminEmail\n";
    echo "Status: ACTIVE (ready to use)\n\n";
    
    echo "👥 Employee Test Credentials:\n";
    echo str_repeat("-", 50) . "\n";
    foreach ($employees as $idx => $emp) {
        $empUsername = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '', $emp[0])) . '_' . ($idx + 1);
        echo "Name: {$emp[0]}\n";
        echo "Username: $empUsername\n";
        echo "Password: TempPass123!\n";
        echo "Role: {$emp[2]}\n";
        echo "Email: {$emp[1]}\n";
        echo str_repeat("-", 50) . "\n";
    }
    
    echo "\n";
    echo "🌐 Access URLs:\n";
    echo "   Login: /login.php\n";
    echo "   Dashboard: /index.php\n\n";
    
} catch (Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

