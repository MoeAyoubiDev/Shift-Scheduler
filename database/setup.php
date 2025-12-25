<?php
declare(strict_types=1);

/**
 * Complete Database Setup Script
 * Ensures the database schema exists with multi-tenant support
 * Usage: php database/setup.php
 */

require_once __DIR__ . '/../app/Core/config.php';

$databaseConfig = config('database') ?? [];
$dbHost = getenv('DB_HOST') ?: ($databaseConfig['host'] ?? 'localhost');
$dbPort = getenv('DB_PORT') ?: ($databaseConfig['port'] ?? '3306');
$dbName = getenv('DB_NAME') ?: ($databaseConfig['name'] ?? 'ShiftSchedulerDB');
$dbUser = getenv('DB_USER') ?: ($databaseConfig['user'] ?? 'shift_user');
$dbPass = getenv('DB_PASSWORD') ?: ($databaseConfig['pass'] ?? 'StrongPassword123!');

$pdo = new PDO(
    sprintf('mysql:host=%s;port=%s;charset=utf8mb4', $dbHost, $dbPort),
    $dbUser,
    $dbPass,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

echo "🚀 Complete Database Setup\n";
echo str_repeat("=", 50) . "\n\n";

try {
    // Step 1: Ensure database exists
    echo "📋 Step 1: Ensuring database exists...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS {$dbName} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE {$dbName}");
    echo "   ✓ Database ready\n\n";
    
    // Step 2: Create tables in correct dependency order
    echo "📊 Step 2: Creating tables in dependency order...\n";
    
    // Level 1: Tables with no dependencies
    $pdo->exec("CREATE TABLE IF NOT EXISTS companies (id INT AUTO_INCREMENT PRIMARY KEY, company_name VARCHAR(255) NOT NULL, company_slug VARCHAR(191) NOT NULL UNIQUE, admin_email VARCHAR(191) NOT NULL, admin_password_hash VARCHAR(255) NULL, timezone VARCHAR(50) DEFAULT 'UTC', country VARCHAR(100), company_size VARCHAR(50), status ENUM('PENDING_VERIFICATION', 'VERIFIED', 'ONBOARDING', 'PAYMENT_PENDING', 'ACTIVE', 'SUSPENDED') DEFAULT 'PENDING_VERIFICATION', email_verified_at DATETIME NULL, payment_completed_at DATETIME NULL, onboarding_completed_at DATETIME NULL, verification_token VARCHAR(255) NULL, payment_token VARCHAR(255) NULL, payment_amount DECIMAL(10,2) DEFAULT 0.00, payment_status ENUM('PENDING', 'COMPLETED', 'FAILED', 'REFUNDED') DEFAULT 'PENDING', created_at DATETIME DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, INDEX idx_slug (company_slug), INDEX idx_status (status), INDEX idx_email (admin_email)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS roles (id INT AUTO_INCREMENT PRIMARY KEY, role_name VARCHAR(50) NOT NULL UNIQUE, description VARCHAR(255), created_at DATETIME DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS shift_types (id INT AUTO_INCREMENT PRIMARY KEY, code VARCHAR(10) NOT NULL UNIQUE, name VARCHAR(50) NOT NULL, start_time TIME NULL, end_time TIME NULL, duration_hours DECIMAL(4,2) NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS schedule_patterns (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(50) NOT NULL, work_days_per_week INT NOT NULL, off_days_per_week INT NOT NULL, default_shift_duration_hours DECIMAL(4,2), description VARCHAR(255)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS system_settings (id INT AUTO_INCREMENT PRIMARY KEY, system_key VARCHAR(100) NOT NULL UNIQUE, system_value VARCHAR(255), description VARCHAR(255), updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    // Level 2: Tables that depend on Level 1
    $pdo->exec("CREATE TABLE IF NOT EXISTS company_onboarding (id INT AUTO_INCREMENT PRIMARY KEY, company_id INT NOT NULL, step VARCHAR(50) NOT NULL, step_data JSON NULL, completed TINYINT(1) DEFAULT 0, completed_at DATETIME NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE, UNIQUE KEY unique_company_step (company_id, step), INDEX idx_company (company_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS sections (id INT AUTO_INCREMENT PRIMARY KEY, company_id INT NULL, section_name VARCHAR(100) NOT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE, INDEX idx_company (company_id), UNIQUE KEY unique_section_company (section_name, company_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS shift_definitions (id INT AUTO_INCREMENT PRIMARY KEY, shift_name VARCHAR(50) NOT NULL, start_time TIME NULL, end_time TIME NULL, duration_hours DECIMAL(4,2) NULL, category VARCHAR(20) NOT NULL CHECK (category IN ('AM','MID','PM','MIDNIGHT','OVERNIGHT','OFF')), color_code VARCHAR(20), shift_type_id INT NULL, FOREIGN KEY (shift_type_id) REFERENCES shift_types(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (id INT AUTO_INCREMENT PRIMARY KEY, company_id INT NULL, username VARCHAR(100) NOT NULL, password_hash VARCHAR(255) NULL, email VARCHAR(150), role VARCHAR(50) NULL, onboarding_completed TINYINT(1) DEFAULT 0, is_active TINYINT(1) DEFAULT 1, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE, INDEX idx_company (company_id), UNIQUE KEY unique_username_company (username, company_id), UNIQUE KEY unique_email (email)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS weeks (id INT AUTO_INCREMENT PRIMARY KEY, company_id INT NULL, week_start_date DATE NOT NULL, week_end_date DATE NOT NULL, is_locked_for_requests TINYINT(1) DEFAULT 0, lock_reason VARCHAR(255), created_at DATETIME DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE, INDEX idx_company (company_id), UNIQUE KEY unique_week_company (week_start_date, company_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    // Level 3: Tables that depend on Level 2
    $pdo->exec("CREATE TABLE IF NOT EXISTS user_roles (id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NOT NULL, role_id INT NOT NULL, section_id INT NOT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE, FOREIGN KEY (role_id) REFERENCES roles(id), FOREIGN KEY (section_id) REFERENCES sections(id), UNIQUE KEY unique_user_role_section (user_id, role_id, section_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS shift_requirements (id INT AUTO_INCREMENT PRIMARY KEY, company_id INT NULL, week_id INT NOT NULL, section_id INT NOT NULL, shift_date DATE NOT NULL, shift_type_id INT NOT NULL, required_count INT NOT NULL DEFAULT 0, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE, FOREIGN KEY (week_id) REFERENCES weeks(id) ON DELETE CASCADE, FOREIGN KEY (section_id) REFERENCES sections(id), FOREIGN KEY (shift_type_id) REFERENCES shift_types(id), INDEX idx_company (company_id), UNIQUE KEY unique_shift_requirement (week_id, section_id, shift_date, shift_type_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    // Level 4: Tables that depend on Level 3
    $pdo->exec("CREATE TABLE IF NOT EXISTS employees (id INT AUTO_INCREMENT PRIMARY KEY, user_role_id INT NOT NULL, employee_code VARCHAR(50) NOT NULL, full_name VARCHAR(150) NOT NULL, email VARCHAR(150), is_senior TINYINT(1) DEFAULT 0, seniority_level INT DEFAULT 0, is_active TINYINT(1) DEFAULT 1, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, FOREIGN KEY (user_role_id) REFERENCES user_roles(id) ON DELETE CASCADE, UNIQUE KEY unique_employee_code (employee_code)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS schedules (id INT AUTO_INCREMENT PRIMARY KEY, company_id INT NULL, week_id INT NOT NULL, section_id INT NOT NULL, generated_by_admin_id INT NOT NULL, generated_at DATETIME DEFAULT CURRENT_TIMESTAMP, status VARCHAR(10) DEFAULT 'DRAFT' CHECK (status IN ('DRAFT','FINAL')), notes TEXT NULL, FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE, FOREIGN KEY (week_id) REFERENCES weeks(id) ON DELETE CASCADE, FOREIGN KEY (section_id) REFERENCES sections(id), FOREIGN KEY (generated_by_admin_id) REFERENCES employees(id), INDEX idx_company (company_id), UNIQUE KEY unique_schedule_company_week_section (week_id, section_id, company_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    // Level 5: Tables that depend on Level 4
    $pdo->exec("CREATE TABLE IF NOT EXISTS schedule_shifts (id INT AUTO_INCREMENT PRIMARY KEY, schedule_id INT NOT NULL, shift_date DATE NOT NULL, shift_definition_id INT NOT NULL, required_count INT NOT NULL DEFAULT 0, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (schedule_id) REFERENCES schedules(id) ON DELETE CASCADE, FOREIGN KEY (shift_definition_id) REFERENCES shift_definitions(id), INDEX idx_schedule_date (schedule_id, shift_date)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS shift_requests (id INT AUTO_INCREMENT PRIMARY KEY, employee_id INT NOT NULL, week_id INT NOT NULL, request_date DATE NOT NULL, shift_definition_id INT NULL, is_day_off TINYINT(1) DEFAULT 0, schedule_pattern_id INT NOT NULL, reason TEXT NULL, importance_level VARCHAR(10) NOT NULL DEFAULT 'MEDIUM' CHECK (importance_level IN ('LOW','MEDIUM','HIGH','EMERGENCY')), status VARCHAR(10) NOT NULL DEFAULT 'PENDING' CHECK (status IN ('PENDING','APPROVED','DECLINED')), submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP, reviewed_by_admin_id INT NULL, reviewed_at DATETIME NULL, FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE, FOREIGN KEY (week_id) REFERENCES weeks(id) ON DELETE CASCADE, FOREIGN KEY (shift_definition_id) REFERENCES shift_definitions(id), FOREIGN KEY (schedule_pattern_id) REFERENCES schedule_patterns(id), FOREIGN KEY (reviewed_by_admin_id) REFERENCES employees(id), INDEX idx_week_employee (week_id, employee_id), INDEX idx_status (status)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    // Level 6: Tables that depend on Level 5
    $pdo->exec("CREATE TABLE IF NOT EXISTS schedule_assignments (id INT AUTO_INCREMENT PRIMARY KEY, schedule_shift_id INT NOT NULL, employee_id INT NOT NULL, assignment_source VARCHAR(20) NOT NULL DEFAULT 'MATCHED_REQUEST' CHECK (assignment_source IN ('MATCHED_REQUEST','AUTO_ASSIGNED','MANUALLY_ADJUSTED')), is_senior TINYINT(1) DEFAULT 0, notes VARCHAR(255) DEFAULT '', created_at DATETIME DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (schedule_shift_id) REFERENCES schedule_shifts(id) ON DELETE CASCADE, FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE, UNIQUE KEY unique_assignment (schedule_shift_id, employee_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS employee_breaks (id INT AUTO_INCREMENT PRIMARY KEY, employee_id INT NOT NULL, schedule_shift_id INT NULL, worked_date DATE NOT NULL, break_start DATETIME NULL, break_end DATETIME NULL, is_active TINYINT(1) DEFAULT 0, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE, FOREIGN KEY (schedule_shift_id) REFERENCES schedule_shifts(id) ON DELETE SET NULL, UNIQUE KEY unique_employee_break (employee_id, worked_date)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS notifications (id INT AUTO_INCREMENT PRIMARY KEY, company_id INT NULL, user_id INT NOT NULL, type VARCHAR(20) NOT NULL CHECK (type IN ('SHIFT_REMINDER','SCHEDULE_PUBLISHED','REQUEST_STATUS')), title VARCHAR(150) NOT NULL, body TEXT NULL, is_read TINYINT(1) DEFAULT 0, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, read_at DATETIME NULL, FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE, FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE, INDEX idx_company (company_id), INDEX idx_user_read (user_id, is_read)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");


    $columnExists = function (string $table, string $column) use ($pdo, $dbName): bool {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) AS count
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?
        ");
        $stmt->execute([$dbName, $table, $column]);
        return (int) $stmt->fetchColumn() > 0;
    };

    $indexExists = function (string $table, string $index) use ($pdo, $dbName): bool {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) AS count
            FROM INFORMATION_SCHEMA.STATISTICS
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND INDEX_NAME = ?
        ");
        $stmt->execute([$dbName, $table, $index]);
        return (int) $stmt->fetchColumn() > 0;
    };

    if ($columnExists('companies', 'admin_password_hash')) {
        $pdo->exec("ALTER TABLE companies MODIFY admin_password_hash VARCHAR(255) NULL");
    }


    if (!$columnExists('users', 'role')) {
        $pdo->exec("ALTER TABLE users ADD COLUMN role VARCHAR(50) NULL AFTER provider");
    }

    if (!$columnExists('users', 'onboarding_completed')) {
        $pdo->exec("ALTER TABLE users ADD COLUMN onboarding_completed TINYINT(1) DEFAULT 0 AFTER role");
    }

    if ($columnExists('users', 'provider')) {
        $pdo->exec("ALTER TABLE users MODIFY provider ENUM('email') NULL");
    }


    echo "   ✓ All tables created\n\n";
    
    // Step 3: Seed reference data
    echo "🌱 Step 3: Seeding reference data...\n";
    
    $pdo->exec("INSERT IGNORE INTO roles (role_name, description) VALUES ('Director', 'Read-only access to both sections'), ('Team Leader', 'Full CRUD access for assigned section'), ('Supervisor', 'Read-only access for assigned section'), ('Senior', 'Shift leader for today operations'), ('Employee', 'Shift request and schedule access')");
    
    $pdo->exec("INSERT IGNORE INTO shift_types (code, name, start_time, end_time, duration_hours) VALUES ('AM', 'Morning', '06:00:00', '14:00:00', 8.00), ('MID', 'Mid', '10:00:00', '18:00:00', 8.00), ('PM', 'Evening', '14:00:00', '22:00:00', 8.00), ('MIDNIGHT', 'Midnight', '18:00:00', '02:00:00', 8.00), ('OVERNIGHT', 'Overnight', '22:00:00', '06:00:00', 8.00)");
    
    $pdo->exec("INSERT IGNORE INTO shift_definitions (shift_name, start_time, end_time, duration_hours, category, color_code, shift_type_id) VALUES ('AM Shift', '06:00:00', '14:00:00', 8.00, 'AM', '#38bdf8', 1), ('Mid Shift', '10:00:00', '18:00:00', 8.00, 'MID', '#6366f1', 2), ('PM Shift', '14:00:00', '22:00:00', 8.00, 'PM', '#f97316', 3), ('Midnight Shift', '18:00:00', '02:00:00', 8.00, 'MIDNIGHT', '#0f172a', 4), ('Overnight Shift', '22:00:00', '06:00:00', 8.00, 'OVERNIGHT', '#334155', 5), ('Day Off', NULL, NULL, 0.00, 'OFF', '#94a3b8', NULL)");
    
    $pdo->exec("INSERT IGNORE INTO schedule_patterns (name, work_days_per_week, off_days_per_week, default_shift_duration_hours, description) VALUES ('5x2', 5, 2, 8.00, '5 days on / 2 days off'), ('6x1', 6, 1, 8.00, '6 days on / 1 day off')");
    
    echo "   ✓ Reference data seeded\n\n";
    
    // Step 4: Create stored procedures
    echo "⚙️  Step 4: Creating stored procedures...\n";
    
    // Multi-tenant authentication
    $pdo->exec("DROP PROCEDURE IF EXISTS sp_verify_login");
    $pdo->exec("CREATE PROCEDURE sp_verify_login(IN p_username VARCHAR(100), IN p_company_id INT) BEGIN SELECT u.id AS user_id, u.username, u.password_hash, u.email, u.is_active, u.company_id, ur.id AS user_role_id, r.id AS role_id, r.role_name, s.id AS section_id, s.section_name, e.id AS employee_id, e.full_name AS employee_name, e.is_senior, e.seniority_level, e.employee_code FROM users u INNER JOIN user_roles ur ON ur.user_id = u.id INNER JOIN roles r ON r.id = ur.role_id INNER JOIN sections s ON s.id = ur.section_id LEFT JOIN employees e ON e.user_role_id = ur.id WHERE u.username = p_username AND u.company_id = p_company_id AND u.is_active = 1 ORDER BY ur.id; END");
    
    // Company management
    $pdo->exec("DROP PROCEDURE IF EXISTS sp_create_company");
    $pdo->exec("CREATE PROCEDURE sp_create_company(IN p_company_name VARCHAR(255), IN p_admin_email VARCHAR(255), IN p_admin_password_hash VARCHAR(255), IN p_timezone VARCHAR(50), IN p_country VARCHAR(100), IN p_company_size VARCHAR(50), IN p_verification_token VARCHAR(255)) BEGIN DECLARE v_company_slug VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; DECLARE v_slug_exists INT DEFAULT 1; DECLARE v_counter INT DEFAULT 0; SET v_company_slug = LOWER(REGEXP_REPLACE(p_company_name COLLATE utf8mb4_unicode_ci, '[^a-zA-Z0-9]+', '-')) COLLATE utf8mb4_unicode_ci; SET v_company_slug = TRIM(BOTH '-' FROM v_company_slug); WHILE v_slug_exists > 0 DO SELECT COUNT(*) INTO v_slug_exists FROM companies WHERE company_slug COLLATE utf8mb4_unicode_ci = v_company_slug; IF v_slug_exists > 0 THEN SET v_counter = v_counter + 1; SET v_company_slug = CONCAT(v_company_slug, '-', v_counter) COLLATE utf8mb4_unicode_ci; END IF; END WHILE; INSERT INTO companies (company_name, company_slug, admin_email, admin_password_hash, timezone, country, company_size, verification_token, status, email_verified_at) VALUES (p_company_name, v_company_slug, p_admin_email, p_admin_password_hash, COALESCE(p_timezone, 'UTC'), p_country, p_company_size, NULL, 'VERIFIED', NOW()); SELECT LAST_INSERT_ID() AS company_id; END");
    
    $pdo->exec("DROP PROCEDURE IF EXISTS sp_verify_company_email");
    $pdo->exec("CREATE PROCEDURE sp_verify_company_email(IN p_token VARCHAR(255)) BEGIN UPDATE companies SET status = 'VERIFIED', email_verified_at = NOW(), verification_token = NULL WHERE verification_token = p_token AND status = 'PENDING_VERIFICATION'; SELECT ROW_COUNT() AS updated; END");
    
    $pdo->exec("DROP PROCEDURE IF EXISTS sp_complete_company_payment");
    $pdo->exec("CREATE PROCEDURE sp_complete_company_payment(IN p_company_id INT, IN p_payment_token VARCHAR(255), IN p_payment_amount DECIMAL(10,2)) BEGIN UPDATE companies SET payment_status = 'COMPLETED', payment_completed_at = NOW(), payment_token = p_payment_token, payment_amount = p_payment_amount, status = 'ACTIVE' WHERE id = p_company_id AND status IN ('PAYMENT_PENDING', 'ONBOARDING'); SELECT ROW_COUNT() AS updated; END");
    
    // Week management
    $pdo->exec("DROP PROCEDURE IF EXISTS sp_upsert_week");
    $pdo->exec("CREATE PROCEDURE sp_upsert_week(IN p_week_start DATE, IN p_week_end DATE) BEGIN DECLARE v_week_id INT; SELECT id INTO v_week_id FROM weeks WHERE week_start_date = p_week_start LIMIT 1; IF v_week_id IS NULL THEN INSERT INTO weeks (week_start_date, week_end_date) VALUES (p_week_start, p_week_end); SET v_week_id = LAST_INSERT_ID(); END IF; SELECT v_week_id AS week_id; END");
    
    // Shift types and definitions
    $pdo->exec("DROP PROCEDURE IF EXISTS sp_get_shift_types");
    $pdo->exec("CREATE PROCEDURE sp_get_shift_types() BEGIN SELECT id AS shift_type_id, code, name AS shift_type_name, start_time, end_time FROM shift_types ORDER BY id; END");
    
    $pdo->exec("DROP PROCEDURE IF EXISTS sp_get_shift_definitions");
    $pdo->exec("CREATE PROCEDURE sp_get_shift_definitions() BEGIN SELECT sd.id AS definition_id, sd.shift_name AS definition_name, sd.category, sd.color_code, st.id AS shift_type_id, st.code AS shift_type_code, st.name AS shift_type_name FROM shift_definitions sd LEFT JOIN shift_types st ON st.id = sd.shift_type_id ORDER BY sd.id; END");
    
    $pdo->exec("DROP PROCEDURE IF EXISTS sp_get_schedule_patterns");
    $pdo->exec("CREATE PROCEDURE sp_get_schedule_patterns() BEGIN SELECT id, name, work_days_per_week, off_days_per_week, description FROM schedule_patterns ORDER BY id; END");
    
    // Shift requirements
    $pdo->exec("DROP PROCEDURE IF EXISTS sp_get_shift_requirements");
    $pdo->exec("CREATE PROCEDURE sp_get_shift_requirements(IN p_week_id INT, IN p_section_id INT) BEGIN SELECT sr.id, sr.shift_date AS date, sr.shift_type_id, st.code AS shift_type_code, st.name AS shift_type_name, sr.required_count FROM shift_requirements sr INNER JOIN shift_types st ON st.id = sr.shift_type_id WHERE sr.week_id = p_week_id AND sr.section_id = p_section_id ORDER BY sr.shift_date, sr.shift_type_id; END");
    
    $pdo->exec("DROP PROCEDURE IF EXISTS sp_set_shift_requirement");
    $pdo->exec("CREATE PROCEDURE sp_set_shift_requirement(IN p_week_id INT, IN p_section_id INT, IN p_date DATE, IN p_shift_type_id INT, IN p_required_count INT) BEGIN INSERT INTO shift_requirements (week_id, section_id, shift_date, shift_type_id, required_count) VALUES (p_week_id, p_section_id, p_date, p_shift_type_id, p_required_count) ON DUPLICATE KEY UPDATE required_count = VALUES(required_count); SELECT LAST_INSERT_ID() AS requirement_id; END");
    
    // Schedule generation
    $pdo->exec("DROP PROCEDURE IF EXISTS sp_generate_weekly_schedule");
    $pdo->exec("CREATE PROCEDURE sp_generate_weekly_schedule(IN p_week_id INT, IN p_section_id INT, IN p_generated_by_employee_id INT) BEGIN DECLARE v_schedule_id INT; DECLARE v_assignments_needed INT DEFAULT 0; DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN ROLLBACK; RESIGNAL; END; START TRANSACTION; INSERT INTO schedules (week_id, section_id, generated_by_admin_id, status) VALUES (p_week_id, p_section_id, p_generated_by_employee_id, 'DRAFT') ON DUPLICATE KEY UPDATE generated_at = NOW(), generated_by_admin_id = p_generated_by_employee_id, status = 'DRAFT'; SELECT id INTO v_schedule_id FROM schedules WHERE week_id = p_week_id AND section_id = p_section_id LIMIT 1; DELETE FROM schedule_assignments WHERE schedule_shift_id IN (SELECT id FROM schedule_shifts WHERE schedule_id = v_schedule_id); DELETE FROM schedule_shifts WHERE schedule_id = v_schedule_id; INSERT INTO schedule_shifts (schedule_id, shift_date, shift_definition_id, required_count) SELECT v_schedule_id, sr.shift_date, sd.id, sr.required_count FROM shift_requirements sr INNER JOIN shift_types st ON st.id = sr.shift_type_id INNER JOIN shift_definitions sd ON sd.shift_type_id = st.id WHERE sr.week_id = p_week_id AND sr.section_id = p_section_id; INSERT INTO schedule_assignments (schedule_shift_id, employee_id, assignment_source, is_senior, notes) SELECT ss.id, sr.employee_id, 'MATCHED_REQUEST', e.is_senior, CONCAT('Request: ', COALESCE(sr.reason, '')) FROM schedule_shifts ss INNER JOIN shift_requests sr ON sr.week_id = p_week_id AND sr.request_date = ss.shift_date INNER JOIN employees e ON e.id = sr.employee_id INNER JOIN user_roles ur ON ur.id = e.user_role_id WHERE ss.shift_definition_id = sr.shift_definition_id AND sr.status = 'APPROVED' AND sr.is_day_off = 0 AND ur.section_id = p_section_id AND NOT EXISTS (SELECT 1 FROM schedule_assignments sa WHERE sa.schedule_shift_id = ss.id AND sa.employee_id = e.id); SET v_assignments_needed = 1; WHILE v_assignments_needed > 0 DO INSERT INTO schedule_assignments (schedule_shift_id, employee_id, assignment_source, is_senior, notes) SELECT ss.id, e.id, 'AUTO_ASSIGNED', e.is_senior, 'Auto-assigned by system' FROM schedule_shifts ss INNER JOIN shift_definitions sd ON sd.id = ss.shift_definition_id INNER JOIN employees e ON e.is_active = 1 INNER JOIN user_roles ur ON ur.id = e.user_role_id LEFT JOIN schedule_assignments sa ON sa.schedule_shift_id = ss.id AND sa.employee_id = e.id WHERE ur.section_id = p_section_id AND sa.id IS NULL AND sd.category <> 'OFF' AND (SELECT COUNT(*) FROM schedule_assignments WHERE schedule_shift_id = ss.id) < ss.required_count ORDER BY e.seniority_level DESC, e.id LIMIT 1; SET v_assignments_needed = ROW_COUNT(); END WHILE; COMMIT; SELECT v_schedule_id AS schedule_id, 'Schedule generated successfully' AS message; END");
    
    $pdo->exec("DROP PROCEDURE IF EXISTS sp_get_weekly_schedule");
    $pdo->exec("CREATE PROCEDURE sp_get_weekly_schedule(IN p_week_id INT, IN p_section_id INT) BEGIN SELECT ss.id AS schedule_shift_id, ss.shift_date, sd.id AS shift_definition_id, sd.shift_name, sd.category AS shift_category, sd.color_code, sd.start_time, sd.end_time, sd.duration_hours, sa.id AS assignment_id, sa.assignment_source, sa.notes, e.id AS employee_id, e.full_name AS employee_name, e.employee_code, e.is_senior FROM schedule_shifts ss INNER JOIN schedules s ON s.id = ss.schedule_id INNER JOIN shift_definitions sd ON sd.id = ss.shift_definition_id LEFT JOIN schedule_assignments sa ON sa.schedule_shift_id = ss.id LEFT JOIN employees e ON e.id = sa.employee_id WHERE s.week_id = p_week_id AND s.section_id = p_section_id ORDER BY ss.shift_date, sd.shift_name, e.full_name; END");
    
    $pdo->exec("DROP PROCEDURE IF EXISTS sp_get_today_shift");
    $pdo->exec("CREATE PROCEDURE sp_get_today_shift(IN p_section_id INT, IN p_today DATE) BEGIN SELECT ss.id AS schedule_shift_id, ss.shift_date, sd.id AS shift_definition_id, sd.shift_name, sd.category AS shift_category, sd.start_time, sd.end_time, sa.id AS assignment_id, e.id AS employee_id, e.full_name AS employee_name, e.employee_code, e.is_senior, 'Scheduled' AS attendance_status FROM schedule_shifts ss INNER JOIN schedules s ON s.id = ss.schedule_id INNER JOIN shift_definitions sd ON sd.id = ss.shift_definition_id LEFT JOIN schedule_assignments sa ON sa.schedule_shift_id = ss.id LEFT JOIN employees e ON e.id = sa.employee_id WHERE s.section_id = p_section_id AND ss.shift_date = p_today AND sd.category <> 'OFF' ORDER BY sd.start_time, e.full_name; END");
    
    $pdo->exec("DROP PROCEDURE IF EXISTS sp_update_schedule_assignment");
    $pdo->exec("CREATE PROCEDURE sp_update_schedule_assignment(IN p_assignment_id INT, IN p_shift_definition_id INT, IN p_employee_id INT) BEGIN DECLARE v_schedule_shift_id INT; DECLARE v_new_shift_id INT; SELECT schedule_shift_id INTO v_schedule_shift_id FROM schedule_assignments WHERE id = p_assignment_id; SELECT id INTO v_new_shift_id FROM schedule_shifts WHERE schedule_id = (SELECT schedule_id FROM schedule_shifts WHERE id = v_schedule_shift_id) AND shift_date = (SELECT shift_date FROM schedule_shifts WHERE id = v_schedule_shift_id) AND shift_definition_id = p_shift_definition_id LIMIT 1; IF p_employee_id IS NOT NULL THEN UPDATE schedule_assignments SET schedule_shift_id = COALESCE(v_new_shift_id, v_schedule_shift_id), employee_id = p_employee_id, assignment_source = 'MANUALLY_ADJUSTED', notes = CONCAT(COALESCE(notes, ''), ' | Manually adjusted') WHERE id = p_assignment_id; ELSE UPDATE schedule_assignments SET schedule_shift_id = COALESCE(v_new_shift_id, v_schedule_shift_id), assignment_source = 'MANUALLY_ADJUSTED', notes = CONCAT(COALESCE(notes, ''), ' | Manually adjusted') WHERE id = p_assignment_id; END IF; SELECT ROW_COUNT() AS affected_rows; END");
    
    $pdo->exec("DROP PROCEDURE IF EXISTS sp_delete_schedule_assignment");
    $pdo->exec("CREATE PROCEDURE sp_delete_schedule_assignment(IN p_assignment_id INT) BEGIN DELETE FROM schedule_assignments WHERE id = p_assignment_id; SELECT ROW_COUNT() AS affected_rows; END");
    
    // Break management
    $pdo->exec("DROP PROCEDURE IF EXISTS sp_start_break");
    $pdo->exec("CREATE PROCEDURE sp_start_break(IN p_employee_id INT, IN p_worked_date DATE, IN p_schedule_shift_id INT) BEGIN DECLARE v_existing_break INT; SELECT id INTO v_existing_break FROM employee_breaks WHERE employee_id = p_employee_id AND worked_date = p_worked_date; IF v_existing_break IS NOT NULL THEN UPDATE employee_breaks SET break_start = NOW(), is_active = 1, schedule_shift_id = p_schedule_shift_id WHERE id = v_existing_break; ELSE INSERT INTO employee_breaks (employee_id, schedule_shift_id, worked_date, break_start, is_active) VALUES (p_employee_id, p_schedule_shift_id, p_worked_date, NOW(), 1); END IF; SELECT LAST_INSERT_ID() AS break_id, 'Break started' AS message; END");
    
    $pdo->exec("DROP PROCEDURE IF EXISTS sp_end_break");
    $pdo->exec("CREATE PROCEDURE sp_end_break(IN p_employee_id INT, IN p_worked_date DATE) BEGIN UPDATE employee_breaks SET break_end = NOW(), is_active = 0 WHERE employee_id = p_employee_id AND worked_date = p_worked_date AND is_active = 1; SELECT ROW_COUNT() AS affected_rows, 'Break ended' AS message; END");
    
    $pdo->exec("DROP PROCEDURE IF EXISTS sp_get_break_status");
    $pdo->exec("CREATE PROCEDURE sp_get_break_status(IN p_section_id INT, IN p_today DATE) BEGIN SELECT eb.id AS break_id, e.id AS employee_id, e.full_name AS employee_name, e.employee_code, sd.shift_name, sd.category AS shift_category, eb.break_start, eb.break_end, eb.is_active, CASE WHEN eb.break_end IS NULL AND eb.break_start IS NOT NULL THEN GREATEST(TIMESTAMPDIFF(MINUTE, eb.break_start, NOW()) - 30, 0) WHEN eb.break_end IS NOT NULL THEN GREATEST(TIMESTAMPDIFF(MINUTE, eb.break_start, eb.break_end) - 30, 0) ELSE 0 END AS delay_minutes, CASE WHEN eb.is_active = 1 THEN 'ON_BREAK' WHEN eb.break_start IS NULL THEN 'NOT_STARTED' WHEN eb.break_end IS NOT NULL THEN 'COMPLETED' ELSE 'UNKNOWN' END AS status FROM employee_breaks eb INNER JOIN employees e ON e.id = eb.employee_id INNER JOIN user_roles ur ON ur.id = e.user_role_id INNER JOIN sections s ON s.id = ur.section_id LEFT JOIN schedule_shifts ss ON ss.id = eb.schedule_shift_id LEFT JOIN shift_definitions sd ON sd.id = ss.shift_definition_id WHERE s.id = p_section_id AND eb.worked_date = p_today ORDER BY e.full_name; END");
    
    $pdo->exec("DROP PROCEDURE IF EXISTS sp_get_employee_break");
    $pdo->exec("CREATE PROCEDURE sp_get_employee_break(IN p_employee_id INT, IN p_worked_date DATE) BEGIN SELECT eb.id AS break_id, eb.break_start, eb.break_end, eb.is_active, CASE WHEN eb.break_end IS NULL AND eb.break_start IS NOT NULL THEN GREATEST(TIMESTAMPDIFF(MINUTE, eb.break_start, NOW()) - 30, 0) WHEN eb.break_end IS NOT NULL THEN GREATEST(TIMESTAMPDIFF(MINUTE, eb.break_start, eb.break_end) - 30, 0) ELSE 0 END AS delay_minutes FROM employee_breaks eb WHERE eb.employee_id = p_employee_id AND eb.worked_date = p_worked_date; END");
    
    // Shift requests
    $pdo->exec("DROP PROCEDURE IF EXISTS sp_submit_shift_request");
    $pdo->exec("CREATE PROCEDURE sp_submit_shift_request(IN p_employee_id INT, IN p_week_id INT, IN p_request_date DATE, IN p_shift_definition_id INT, IN p_is_day_off TINYINT, IN p_schedule_pattern_id INT, IN p_reason TEXT, IN p_importance_level VARCHAR(10)) BEGIN DECLARE v_day_of_week INT; DECLARE v_role_name VARCHAR(50); SET v_day_of_week = DAYOFWEEK(p_request_date); IF v_day_of_week = 1 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Shift requests are not allowed on Sunday'; END IF; SELECT r.role_name INTO v_role_name FROM employees e INNER JOIN user_roles ur ON ur.id = e.user_role_id INNER JOIN roles r ON r.id = ur.role_id WHERE e.id = p_employee_id; IF v_role_name = 'Senior' THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Senior employees cannot submit shift requests'; END IF; INSERT INTO shift_requests (employee_id, week_id, request_date, shift_definition_id, is_day_off, schedule_pattern_id, reason, importance_level) VALUES (p_employee_id, p_week_id, NULLIF(p_shift_definition_id, 0), p_is_day_off, p_schedule_pattern_id, p_reason, p_importance_level); SELECT LAST_INSERT_ID() AS request_id, 'Request submitted successfully' AS message; END");
    
    $pdo->exec("DROP PROCEDURE IF EXISTS sp_get_shift_requests");
    $pdo->exec("CREATE PROCEDURE sp_get_shift_requests(IN p_week_id INT, IN p_section_id INT, IN p_employee_id INT) BEGIN SELECT sr.id, sr.request_date, sr.importance_level, sr.status, sr.reason, sr.submitted_at, sr.reviewed_at, sd.shift_name, sd.category AS shift_category, sp.name AS pattern_name, e.id AS employee_id, e.full_name AS employee_name, e.employee_code, reviewer.full_name AS reviewer_name FROM shift_requests sr INNER JOIN employees e ON e.id = sr.employee_id INNER JOIN user_roles ur ON ur.id = e.user_role_id INNER JOIN sections s ON s.id = ur.section_id LEFT JOIN shift_definitions sd ON sd.id = sr.shift_definition_id INNER JOIN schedule_patterns sp ON sp.id = sr.schedule_pattern_id LEFT JOIN employees reviewer ON reviewer.id = sr.reviewed_by_admin_id WHERE sr.week_id = p_week_id AND s.id = p_section_id AND (p_employee_id IS NULL OR e.id = p_employee_id) ORDER BY sr.submitted_at DESC; END");
    
    $pdo->exec("DROP PROCEDURE IF EXISTS sp_update_shift_request_status");
    $pdo->exec("CREATE PROCEDURE sp_update_shift_request_status(IN p_request_id INT, IN p_status VARCHAR(10), IN p_reviewer_id INT) BEGIN UPDATE shift_requests SET status = p_status, reviewed_by_admin_id = p_reviewer_id, reviewed_at = NOW() WHERE id = p_request_id; SELECT ROW_COUNT() AS affected_rows; END");
    
    // Employee management
    $pdo->exec("DROP PROCEDURE IF EXISTS sp_create_employee");
    $pdo->exec("CREATE PROCEDURE sp_create_employee(IN p_username VARCHAR(100), IN p_password_hash VARCHAR(255), IN p_email VARCHAR(150), IN p_role_id INT, IN p_section_id INT, IN p_employee_code VARCHAR(50), IN p_full_name VARCHAR(150), IN p_is_senior TINYINT, IN p_seniority_level INT) BEGIN DECLARE v_user_id INT; DECLARE v_user_role_id INT; DECLARE v_employee_id INT; DECLARE v_username_exists INT DEFAULT 0; DECLARE v_email_exists INT DEFAULT 0; DECLARE v_employee_code_exists INT DEFAULT 0; DECLARE v_full_name_exists INT DEFAULT 0; DECLARE v_company_id INT DEFAULT NULL; DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN ROLLBACK; RESIGNAL; END; START TRANSACTION; SELECT company_id INTO v_company_id FROM sections WHERE id = p_section_id LIMIT 1; SELECT COUNT(*) INTO v_username_exists FROM users WHERE username = p_username AND company_id <=> v_company_id; IF v_username_exists > 0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Username already exists'; END IF; IF p_email IS NOT NULL AND p_email != '' THEN SELECT COUNT(*) INTO v_email_exists FROM users WHERE email = p_email AND email IS NOT NULL AND email != '' AND company_id <=> v_company_id; IF v_email_exists > 0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Email already exists'; END IF; END IF; SELECT COUNT(*) INTO v_employee_code_exists FROM employees WHERE employee_code = p_employee_code; IF v_employee_code_exists > 0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Employee code already exists'; END IF; SELECT COUNT(*) INTO v_full_name_exists FROM employees WHERE full_name = p_full_name; IF v_full_name_exists > 0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Full name already exists'; END IF; INSERT INTO users (company_id, username, password_hash, email) VALUES (v_company_id, p_username, p_password_hash, p_email); SET v_user_id = LAST_INSERT_ID(); INSERT INTO user_roles (user_id, role_id, section_id) VALUES (v_user_id, p_role_id, p_section_id); SET v_user_role_id = LAST_INSERT_ID(); INSERT INTO employees (user_role_id, employee_code, full_name, email, is_senior, seniority_level) VALUES (v_user_role_id, p_employee_code, p_full_name, p_email, p_is_senior, p_seniority_level); SET v_employee_id = LAST_INSERT_ID(); COMMIT; SELECT v_employee_id AS employee_id, v_user_id AS user_id; END");

    $pdo->exec("DROP PROCEDURE IF EXISTS sp_create_leader");
    $pdo->exec("CREATE PROCEDURE sp_create_leader(IN p_username VARCHAR(100), IN p_password_hash VARCHAR(255), IN p_email VARCHAR(150), IN p_role_id INT, IN p_section_id INT, IN p_full_name VARCHAR(150)) BEGIN DECLARE v_user_id INT; DECLARE v_user_role_id INT; DECLARE v_employee_id INT; DECLARE v_username_exists INT DEFAULT 0; DECLARE v_email_exists INT DEFAULT 0; DECLARE v_employee_code_exists INT DEFAULT 1; DECLARE v_employee_code VARCHAR(50); DECLARE v_company_id INT DEFAULT NULL; DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN ROLLBACK; RESIGNAL; END; START TRANSACTION; SELECT company_id INTO v_company_id FROM sections WHERE id = p_section_id LIMIT 1; SELECT COUNT(*) INTO v_username_exists FROM users WHERE username = p_username AND company_id <=> v_company_id; IF v_username_exists > 0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Username already exists'; END IF; IF p_email IS NOT NULL AND p_email != '' THEN SELECT COUNT(*) INTO v_email_exists FROM users WHERE email = p_email AND email IS NOT NULL AND email != '' AND company_id <=> v_company_id; IF v_email_exists > 0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Email already exists'; END IF; END IF; WHILE v_employee_code_exists > 0 DO SET v_employee_code = CONCAT('LDR-', LPAD(FLOOR(RAND() * 9999), 4, '0')); SELECT COUNT(*) INTO v_employee_code_exists FROM employees WHERE employee_code = v_employee_code; END WHILE; INSERT INTO users (company_id, username, password_hash, email) VALUES (v_company_id, p_username, p_password_hash, p_email); SET v_user_id = LAST_INSERT_ID(); INSERT INTO user_roles (user_id, role_id, section_id) VALUES (v_user_id, p_role_id, p_section_id); SET v_user_role_id = LAST_INSERT_ID(); INSERT INTO employees (user_role_id, employee_code, full_name, email, is_senior, seniority_level) VALUES (v_user_role_id, v_employee_code, p_full_name, p_email, 0, 0); SET v_employee_id = LAST_INSERT_ID(); COMMIT; SELECT v_employee_id AS employee_id, v_user_id AS user_id; END");
    
    $pdo->exec("DROP PROCEDURE IF EXISTS sp_get_employees_by_section");
    $pdo->exec("CREATE PROCEDURE sp_get_employees_by_section(IN p_section_id INT) BEGIN SELECT e.id, e.employee_code, e.full_name, e.email, e.seniority_level, e.is_senior, e.is_active, r.role_name, u.username FROM employees e INNER JOIN user_roles ur ON ur.id = e.user_role_id INNER JOIN roles r ON r.id = ur.role_id INNER JOIN users u ON u.id = ur.user_id WHERE ur.section_id = p_section_id AND e.is_active = 1 AND r.role_name IN ('Employee', 'Senior') ORDER BY e.seniority_level DESC, e.full_name; END");

    $pdo->exec("DROP PROCEDURE IF EXISTS sp_get_available_employees");
    $pdo->exec("CREATE PROCEDURE sp_get_available_employees(IN p_section_id INT, IN p_date DATE) BEGIN SELECT e.id, e.employee_code, e.full_name, e.email, e.seniority_level, e.is_senior FROM employees e INNER JOIN user_roles ur ON ur.id = e.user_role_id INNER JOIN roles r ON r.id = ur.role_id WHERE ur.section_id = p_section_id AND e.is_active = 1 AND r.role_name IN ('Employee', 'Senior') AND NOT EXISTS (SELECT 1 FROM schedule_assignments sa INNER JOIN schedule_shifts ss ON ss.id = sa.schedule_shift_id INNER JOIN schedules s ON s.id = ss.schedule_id WHERE sa.employee_id = e.id AND ss.shift_date = p_date AND s.section_id = p_section_id) ORDER BY e.seniority_level DESC, e.full_name; END");

    $pdo->exec("DROP PROCEDURE IF EXISTS sp_update_employee");
    $pdo->exec("CREATE PROCEDURE sp_update_employee(IN p_employee_id INT, IN p_section_id INT, IN p_full_name VARCHAR(150), IN p_email VARCHAR(150), IN p_role_id INT, IN p_seniority_level INT, IN p_is_senior TINYINT) BEGIN DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN ROLLBACK; RESIGNAL; END; START TRANSACTION; UPDATE employees e INNER JOIN user_roles ur ON ur.id = e.user_role_id INNER JOIN users u ON u.id = ur.user_id SET e.full_name = p_full_name, e.email = NULLIF(p_email, ''), e.seniority_level = p_seniority_level, e.is_senior = p_is_senior, ur.role_id = p_role_id, u.email = NULLIF(p_email, '') WHERE e.id = p_employee_id AND ur.section_id = p_section_id; SELECT ROW_COUNT() AS affected_rows; COMMIT; END");
    
    // Performance and analytics
    $pdo->exec("DROP PROCEDURE IF EXISTS sp_performance_report");
    $pdo->exec("CREATE PROCEDURE sp_performance_report(IN p_start_date DATE, IN p_end_date DATE, IN p_section_id INT, IN p_employee_id INT) BEGIN SELECT e.id AS employee_id, e.full_name AS employee_name, e.employee_code, COUNT(DISTINCT sa.schedule_shift_id) AS days_worked, COALESCE(SUM(GREATEST(TIMESTAMPDIFF(MINUTE, eb.break_start, eb.break_end) - 30, 0)), 0) AS total_delay_minutes, COALESCE(AVG(GREATEST(TIMESTAMPDIFF(MINUTE, eb.break_start, eb.break_end) - 30, 0)), 0) AS average_delay_minutes FROM employees e INNER JOIN user_roles ur ON ur.id = e.user_role_id INNER JOIN sections s ON s.id = ur.section_id LEFT JOIN schedule_assignments sa ON sa.employee_id = e.id LEFT JOIN schedule_shifts ss ON ss.id = sa.schedule_shift_id LEFT JOIN employee_breaks eb ON eb.employee_id = e.id AND eb.worked_date BETWEEN p_start_date AND p_end_date AND eb.break_end IS NOT NULL WHERE s.id = p_section_id AND e.is_active = 1 AND (p_employee_id IS NULL OR e.id = p_employee_id) AND (ss.shift_date IS NULL OR ss.shift_date BETWEEN p_start_date AND p_end_date) GROUP BY e.id, e.full_name, e.employee_code ORDER BY total_delay_minutes ASC, average_delay_minutes ASC, e.full_name; END");
    
    $pdo->exec("DROP PROCEDURE IF EXISTS sp_director_dashboard");
    $pdo->exec("CREATE PROCEDURE sp_director_dashboard(IN p_section_id INT, IN p_week_id INT) BEGIN SELECT 'Total Employees' AS metric_label, COUNT(DISTINCT e.id) AS metric_value, 'Active employees in section' AS description FROM employees e INNER JOIN user_roles ur ON ur.id = e.user_role_id WHERE ur.section_id = p_section_id AND e.is_active = 1 UNION ALL SELECT 'Approved Requests', COUNT(*) AS metric_value, 'Requests approved for the week' AS description FROM shift_requests sr INNER JOIN employees e ON e.id = sr.employee_id INNER JOIN user_roles ur ON ur.id = e.user_role_id WHERE sr.week_id = p_week_id AND ur.section_id = p_section_id AND sr.status = 'APPROVED' UNION ALL SELECT 'Pending Requests', COUNT(*) AS metric_value, 'Requests pending approval' AS description FROM shift_requests sr INNER JOIN employees e ON e.id = sr.employee_id INNER JOIN user_roles ur ON ur.id = e.user_role_id WHERE sr.week_id = p_week_id AND ur.section_id = p_section_id AND sr.status = 'PENDING' UNION ALL SELECT 'Total Breaks Today', COUNT(*) AS metric_value, 'Breaks logged today' AS description FROM employee_breaks eb INNER JOIN employees e ON e.id = eb.employee_id INNER JOIN user_roles ur ON ur.id = e.user_role_id WHERE ur.section_id = p_section_id AND eb.worked_date = CURDATE(); END");
    
    // Helper procedures
    $pdo->exec("DROP PROCEDURE IF EXISTS sp_get_all_sections");
    $pdo->exec("CREATE PROCEDURE sp_get_all_sections() BEGIN SELECT id, section_name FROM sections ORDER BY id; END");
    
    $pdo->exec("DROP PROCEDURE IF EXISTS sp_get_roles");
    $pdo->exec("CREATE PROCEDURE sp_get_roles() BEGIN SELECT id, role_name, description FROM roles ORDER BY id; END");
    
    echo "   ✓ All stored procedures created\n\n";
    
    echo "✅ Database setup complete!\n";
    echo "\n📝 Ready for:\n";
    echo "   - Company sign-ups at /signup.php\n";
    echo "   - Multi-tenant data isolation\n";
    echo "   - All business logic\n\n";
    
} catch (Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
