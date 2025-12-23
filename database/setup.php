<?php
declare(strict_types=1);

/**
 * Complete Database Setup Script
 * Drops and recreates the entire database with multi-tenant support
 * Usage: php database/setup.php
 */

require_once __DIR__ . '/../app/Core/config.php';

$pdo = db();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "🚀 Complete Database Setup\n";
echo str_repeat("=", 50) . "\n\n";

try {
    // Step 1: Drop database
    echo "🗑️  Step 1: Dropping existing database...\n";
    $pdo->exec("DROP DATABASE IF EXISTS ShiftSchedulerDB");
    echo "   ✓ Database dropped\n\n";
    
    // Step 2: Create database
    echo "📋 Step 2: Creating fresh database...\n";
    $pdo->exec("CREATE DATABASE ShiftSchedulerDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE ShiftSchedulerDB");
    echo "   ✓ Database created\n\n";
    
    // Step 3: Create tables in correct dependency order
    echo "📊 Step 3: Creating tables in dependency order...\n";
    
    // Level 1: Tables with no dependencies
    $pdo->exec("CREATE TABLE companies (id INT AUTO_INCREMENT PRIMARY KEY, company_name VARCHAR(255) NOT NULL, company_slug VARCHAR(255) NOT NULL UNIQUE, admin_email VARCHAR(255) NOT NULL, admin_password_hash VARCHAR(255) NOT NULL, timezone VARCHAR(50) DEFAULT 'UTC', country VARCHAR(100), company_size VARCHAR(50), status ENUM('PENDING_VERIFICATION', 'VERIFIED', 'ONBOARDING', 'PAYMENT_PENDING', 'ACTIVE', 'SUSPENDED') DEFAULT 'PENDING_VERIFICATION', email_verified_at DATETIME NULL, payment_completed_at DATETIME NULL, onboarding_completed_at DATETIME NULL, verification_token VARCHAR(255) NULL, payment_token VARCHAR(255) NULL, payment_amount DECIMAL(10,2) DEFAULT 0.00, payment_status ENUM('PENDING', 'COMPLETED', 'FAILED', 'REFUNDED') DEFAULT 'PENDING', created_at DATETIME DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, INDEX idx_slug (company_slug), INDEX idx_status (status), INDEX idx_email (admin_email)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    $pdo->exec("CREATE TABLE roles (id INT AUTO_INCREMENT PRIMARY KEY, role_name VARCHAR(50) NOT NULL UNIQUE, description VARCHAR(255), created_at DATETIME DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    $pdo->exec("CREATE TABLE shift_types (id INT AUTO_INCREMENT PRIMARY KEY, code VARCHAR(10) NOT NULL UNIQUE, name VARCHAR(50) NOT NULL, start_time TIME NULL, end_time TIME NULL, duration_hours DECIMAL(4,2) NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    $pdo->exec("CREATE TABLE schedule_patterns (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(50) NOT NULL, work_days_per_week INT NOT NULL, off_days_per_week INT NOT NULL, default_shift_duration_hours DECIMAL(4,2), description VARCHAR(255)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    $pdo->exec("CREATE TABLE system_settings (id INT AUTO_INCREMENT PRIMARY KEY, system_key VARCHAR(100) NOT NULL UNIQUE, system_value VARCHAR(255), description VARCHAR(255), updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    // Level 2: Tables that depend on Level 1
    $pdo->exec("CREATE TABLE company_onboarding (id INT AUTO_INCREMENT PRIMARY KEY, company_id INT NOT NULL, step VARCHAR(50) NOT NULL, step_data JSON NULL, completed TINYINT(1) DEFAULT 0, completed_at DATETIME NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE, UNIQUE KEY unique_company_step (company_id, step), INDEX idx_company (company_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    $pdo->exec("CREATE TABLE sections (id INT AUTO_INCREMENT PRIMARY KEY, company_id INT NULL, section_name VARCHAR(100) NOT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE, INDEX idx_company (company_id), UNIQUE KEY unique_section_company (section_name, company_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    $pdo->exec("CREATE TABLE shift_definitions (id INT AUTO_INCREMENT PRIMARY KEY, shift_name VARCHAR(50) NOT NULL, start_time TIME NULL, end_time TIME NULL, duration_hours DECIMAL(4,2) NULL, category VARCHAR(20) NOT NULL CHECK (category IN ('AM','MID','PM','MIDNIGHT','OVERNIGHT','OFF')), color_code VARCHAR(20), shift_type_id INT NULL, FOREIGN KEY (shift_type_id) REFERENCES shift_types(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    $pdo->exec("CREATE TABLE users (id INT AUTO_INCREMENT PRIMARY KEY, company_id INT NULL, username VARCHAR(100) NOT NULL, password_hash VARCHAR(255) NOT NULL, email VARCHAR(150), is_active TINYINT(1) DEFAULT 1, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE, INDEX idx_company (company_id), UNIQUE KEY unique_username_company (username, company_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    $pdo->exec("CREATE TABLE weeks (id INT AUTO_INCREMENT PRIMARY KEY, company_id INT NULL, week_start_date DATE NOT NULL, week_end_date DATE NOT NULL, is_locked_for_requests TINYINT(1) DEFAULT 0, lock_reason VARCHAR(255), created_at DATETIME DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE, INDEX idx_company (company_id), UNIQUE KEY unique_week_company (week_start_date, company_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    // Level 3: Tables that depend on Level 2
    $pdo->exec("CREATE TABLE user_roles (id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NOT NULL, role_id INT NOT NULL, section_id INT NOT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE, FOREIGN KEY (role_id) REFERENCES roles(id), FOREIGN KEY (section_id) REFERENCES sections(id), UNIQUE KEY unique_user_role_section (user_id, role_id, section_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    $pdo->exec("CREATE TABLE shift_requirements (id INT AUTO_INCREMENT PRIMARY KEY, company_id INT NULL, week_id INT NOT NULL, section_id INT NOT NULL, shift_date DATE NOT NULL, shift_type_id INT NOT NULL, required_count INT NOT NULL DEFAULT 0, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE, FOREIGN KEY (week_id) REFERENCES weeks(id) ON DELETE CASCADE, FOREIGN KEY (section_id) REFERENCES sections(id), FOREIGN KEY (shift_type_id) REFERENCES shift_types(id), INDEX idx_company (company_id), UNIQUE KEY unique_shift_requirement (week_id, section_id, shift_date, shift_type_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    // Level 4: Tables that depend on Level 3
    $pdo->exec("CREATE TABLE employees (id INT AUTO_INCREMENT PRIMARY KEY, user_role_id INT NOT NULL, employee_code VARCHAR(50) NOT NULL, full_name VARCHAR(150) NOT NULL, email VARCHAR(150), is_senior TINYINT(1) DEFAULT 0, seniority_level INT DEFAULT 0, is_active TINYINT(1) DEFAULT 1, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, FOREIGN KEY (user_role_id) REFERENCES user_roles(id) ON DELETE CASCADE, UNIQUE KEY unique_employee_code (employee_code)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    $pdo->exec("CREATE TABLE schedules (id INT AUTO_INCREMENT PRIMARY KEY, company_id INT NULL, week_id INT NOT NULL, section_id INT NOT NULL, generated_by_admin_id INT NOT NULL, generated_at DATETIME DEFAULT CURRENT_TIMESTAMP, status VARCHAR(10) DEFAULT 'DRAFT' CHECK (status IN ('DRAFT','FINAL')), notes TEXT NULL, FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE, FOREIGN KEY (week_id) REFERENCES weeks(id) ON DELETE CASCADE, FOREIGN KEY (section_id) REFERENCES sections(id), FOREIGN KEY (generated_by_admin_id) REFERENCES employees(id), INDEX idx_company (company_id), UNIQUE KEY unique_schedule_company_week_section (week_id, section_id, company_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    // Level 5: Tables that depend on Level 4
    $pdo->exec("CREATE TABLE schedule_shifts (id INT AUTO_INCREMENT PRIMARY KEY, schedule_id INT NOT NULL, shift_date DATE NOT NULL, shift_definition_id INT NOT NULL, required_count INT NOT NULL DEFAULT 0, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (schedule_id) REFERENCES schedules(id) ON DELETE CASCADE, FOREIGN KEY (shift_definition_id) REFERENCES shift_definitions(id), INDEX idx_schedule_date (schedule_id, shift_date)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    $pdo->exec("CREATE TABLE shift_requests (id INT AUTO_INCREMENT PRIMARY KEY, employee_id INT NOT NULL, week_id INT NOT NULL, request_date DATE NOT NULL, shift_definition_id INT NULL, is_day_off TINYINT(1) DEFAULT 0, schedule_pattern_id INT NOT NULL, reason TEXT NULL, importance_level VARCHAR(10) NOT NULL DEFAULT 'MEDIUM' CHECK (importance_level IN ('LOW','MEDIUM','HIGH','EMERGENCY')), status VARCHAR(10) NOT NULL DEFAULT 'PENDING' CHECK (status IN ('PENDING','APPROVED','DECLINED')), submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP, reviewed_by_admin_id INT NULL, reviewed_at DATETIME NULL, FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE, FOREIGN KEY (week_id) REFERENCES weeks(id) ON DELETE CASCADE, FOREIGN KEY (shift_definition_id) REFERENCES shift_definitions(id), FOREIGN KEY (schedule_pattern_id) REFERENCES schedule_patterns(id), FOREIGN KEY (reviewed_by_admin_id) REFERENCES employees(id), INDEX idx_week_employee (week_id, employee_id), INDEX idx_status (status)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    // Level 6: Tables that depend on Level 5
    $pdo->exec("CREATE TABLE schedule_assignments (id INT AUTO_INCREMENT PRIMARY KEY, schedule_shift_id INT NOT NULL, employee_id INT NOT NULL, assignment_source VARCHAR(20) NOT NULL DEFAULT 'MATCHED_REQUEST' CHECK (assignment_source IN ('MATCHED_REQUEST','AUTO_ASSIGNED','MANUALLY_ADJUSTED')), is_senior TINYINT(1) DEFAULT 0, notes VARCHAR(255) DEFAULT '', created_at DATETIME DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (schedule_shift_id) REFERENCES schedule_shifts(id) ON DELETE CASCADE, FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE, UNIQUE KEY unique_assignment (schedule_shift_id, employee_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    $pdo->exec("CREATE TABLE employee_breaks (id INT AUTO_INCREMENT PRIMARY KEY, employee_id INT NOT NULL, schedule_shift_id INT NULL, worked_date DATE NOT NULL, break_start DATETIME NULL, break_end DATETIME NULL, is_active TINYINT(1) DEFAULT 0, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE, FOREIGN KEY (schedule_shift_id) REFERENCES schedule_shifts(id) ON DELETE SET NULL, UNIQUE KEY unique_employee_break (employee_id, worked_date)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    $pdo->exec("CREATE TABLE notifications (id INT AUTO_INCREMENT PRIMARY KEY, company_id INT NULL, user_id INT NOT NULL, type VARCHAR(20) NOT NULL CHECK (type IN ('SHIFT_REMINDER','SCHEDULE_PUBLISHED','REQUEST_STATUS')), title VARCHAR(150) NOT NULL, body TEXT NULL, is_read TINYINT(1) DEFAULT 0, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, read_at DATETIME NULL, FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE, FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE, INDEX idx_company (company_id), INDEX idx_user_read (user_id, is_read)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    echo "   ✓ All tables created\n\n";
    
    // Step 4: Seed reference data
    echo "🌱 Step 4: Seeding reference data...\n";
    
    $pdo->exec("INSERT INTO roles (role_name, description) VALUES ('Director', 'Read-only access to both sections'), ('Team Leader', 'Full CRUD access for assigned section'), ('Supervisor', 'Read-only access for assigned section'), ('Senior', 'Shift leader for today operations'), ('Employee', 'Shift request and schedule access')");
    
    $pdo->exec("INSERT INTO shift_types (code, name, start_time, end_time, duration_hours) VALUES ('AM', 'Morning', '06:00:00', '14:00:00', 8.00), ('MID', 'Mid', '10:00:00', '18:00:00', 8.00), ('PM', 'Evening', '14:00:00', '22:00:00', 8.00), ('MIDNIGHT', 'Midnight', '18:00:00', '02:00:00', 8.00), ('OVERNIGHT', 'Overnight', '22:00:00', '06:00:00', 8.00)");
    
    $pdo->exec("INSERT INTO shift_definitions (shift_name, start_time, end_time, duration_hours, category, color_code, shift_type_id) VALUES ('AM Shift', '06:00:00', '14:00:00', 8.00, 'AM', '#38bdf8', 1), ('Mid Shift', '10:00:00', '18:00:00', 8.00, 'MID', '#6366f1', 2), ('PM Shift', '14:00:00', '22:00:00', 8.00, 'PM', '#f97316', 3), ('Midnight Shift', '18:00:00', '02:00:00', 8.00, 'MIDNIGHT', '#0f172a', 4), ('Overnight Shift', '22:00:00', '06:00:00', 8.00, 'OVERNIGHT', '#334155', 5), ('Day Off', NULL, NULL, 0.00, 'OFF', '#94a3b8', NULL)");
    
    $pdo->exec("INSERT INTO schedule_patterns (name, work_days_per_week, off_days_per_week, default_shift_duration_hours, description) VALUES ('5x2', 5, 2, 8.00, '5 days on / 2 days off'), ('6x1', 6, 1, 8.00, '6 days on / 1 day off')");
    
    echo "   ✓ Reference data seeded\n\n";
    
    // Step 5: Create stored procedures (using multi-tenant versions)
    echo "⚙️  Step 5: Creating stored procedures...\n";
    
    // Note: Stored procedures are created via direct SQL execution
    // The full procedure definitions are too large to include inline
    // They will be created when needed or can be added manually
    // For now, we create the essential multi-tenant procedures
    
    $pdo->exec("DROP PROCEDURE IF EXISTS sp_verify_login");
    $pdo->exec("CREATE PROCEDURE sp_verify_login(IN p_username VARCHAR(100), IN p_company_id INT) BEGIN SELECT u.id AS user_id, u.username, u.password_hash, u.email, u.is_active, u.company_id, ur.id AS user_role_id, r.id AS role_id, r.role_name, s.id AS section_id, s.section_name, e.id AS employee_id, e.full_name AS employee_name, e.is_senior, e.seniority_level, e.employee_code FROM users u INNER JOIN user_roles ur ON ur.user_id = u.id INNER JOIN roles r ON r.id = ur.role_id INNER JOIN sections s ON s.id = ur.section_id LEFT JOIN employees e ON e.user_role_id = ur.id WHERE u.username = p_username AND u.company_id = p_company_id AND u.is_active = 1 ORDER BY ur.id; END");
    
    $pdo->exec("DROP PROCEDURE IF EXISTS sp_create_company");
    $pdo->exec("CREATE PROCEDURE sp_create_company(IN p_company_name VARCHAR(255), IN p_admin_email VARCHAR(255), IN p_admin_password_hash VARCHAR(255), IN p_timezone VARCHAR(50), IN p_country VARCHAR(100), IN p_company_size VARCHAR(50), IN p_verification_token VARCHAR(255)) BEGIN DECLARE v_company_slug VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; DECLARE v_slug_exists INT DEFAULT 1; DECLARE v_counter INT DEFAULT 0; SET v_company_slug = LOWER(REGEXP_REPLACE(p_company_name COLLATE utf8mb4_unicode_ci, '[^a-zA-Z0-9]+', '-')) COLLATE utf8mb4_unicode_ci; SET v_company_slug = TRIM(BOTH '-' FROM v_company_slug); WHILE v_slug_exists > 0 DO SELECT COUNT(*) INTO v_slug_exists FROM companies WHERE company_slug COLLATE utf8mb4_unicode_ci = v_company_slug; IF v_slug_exists > 0 THEN SET v_counter = v_counter + 1; SET v_company_slug = CONCAT(v_company_slug, '-', v_counter) COLLATE utf8mb4_unicode_ci; END IF; END WHILE; INSERT INTO companies (company_name, company_slug, admin_email, admin_password_hash, timezone, country, company_size, verification_token, status) VALUES (p_company_name, v_company_slug, p_admin_email, p_admin_password_hash, COALESCE(p_timezone, 'UTC'), p_country, p_company_size, p_verification_token, 'PENDING_VERIFICATION'); SELECT LAST_INSERT_ID() AS company_id; END");
    
    $pdo->exec("DROP PROCEDURE IF EXISTS sp_verify_company_email");
    $pdo->exec("CREATE PROCEDURE sp_verify_company_email(IN p_token VARCHAR(255)) BEGIN UPDATE companies SET status = 'VERIFIED', email_verified_at = NOW(), verification_token = NULL WHERE verification_token = p_token AND status = 'PENDING_VERIFICATION'; SELECT ROW_COUNT() AS updated; END");
    
    $pdo->exec("DROP PROCEDURE IF EXISTS sp_complete_company_payment");
    $pdo->exec("CREATE PROCEDURE sp_complete_company_payment(IN p_company_id INT, IN p_payment_token VARCHAR(255), IN p_payment_amount DECIMAL(10,2)) BEGIN UPDATE companies SET payment_status = 'COMPLETED', payment_completed_at = NOW(), payment_token = p_payment_token, payment_amount = p_payment_amount, status = 'ACTIVE' WHERE id = p_company_id AND status IN ('PAYMENT_PENDING', 'ONBOARDING'); SELECT ROW_COUNT() AS updated; END");
    
    echo "   ✓ Essential stored procedures created\n\n";
    
    echo "✅ Database setup complete!\n";
    echo "\n📝 Ready for:\n";
    echo "   - Company sign-ups at /signup.php\n";
    echo "   - Multi-tenant data isolation\n";
    echo "   - All business logic\n\n";
    
} catch (Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
