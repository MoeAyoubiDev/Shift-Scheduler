-- Shift Scheduler Database: Schema + Procedures + Seed Data
-- Single source of truth for fresh installs

DROP DATABASE IF EXISTS ShiftSchedulerDB;
CREATE DATABASE ShiftSchedulerDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ShiftSchedulerDB;

-- ===============================
-- Tables
-- ===============================
CREATE TABLE companies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(255) NOT NULL,
    company_slug VARCHAR(191) NOT NULL UNIQUE,
    admin_email VARCHAR(191) NOT NULL,
    admin_password_hash VARCHAR(255) NULL,
    timezone VARCHAR(50) DEFAULT 'UTC',
    country VARCHAR(100),
    company_size VARCHAR(50),
    status ENUM('PENDING_VERIFICATION', 'VERIFIED', 'ONBOARDING', 'PAYMENT_PENDING', 'ACTIVE', 'SUSPENDED') DEFAULT 'PENDING_VERIFICATION',
    email_verified_at DATETIME NULL,
    payment_completed_at DATETIME NULL,
    onboarding_completed_at DATETIME NULL,
    verification_token VARCHAR(255) NULL,
    payment_token VARCHAR(255) NULL,
    payment_amount DECIMAL(10,2) DEFAULT 0.00,
    payment_status ENUM('PENDING', 'COMPLETED', 'FAILED', 'REFUNDED') DEFAULT 'PENDING',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (company_slug),
    INDEX idx_status (status),
    INDEX idx_email (admin_email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(50) NOT NULL UNIQUE,
    description VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE shift_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(10) NOT NULL UNIQUE,
    name VARCHAR(50) NOT NULL,
    start_time TIME NULL,
    end_time TIME NULL,
    duration_hours DECIMAL(4,2) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE schedule_patterns (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    work_days_per_week INT NOT NULL,
    off_days_per_week INT NOT NULL,
    default_shift_duration_hours DECIMAL(4,2),
    description VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    system_key VARCHAR(100) NOT NULL UNIQUE,
    system_value VARCHAR(255),
    description VARCHAR(255),
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE company_onboarding (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    step VARCHAR(50) NOT NULL,
    step_data JSON NULL,
    completed TINYINT(1) DEFAULT 0,
    completed_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    UNIQUE KEY unique_company_step (company_id, step),
    INDEX idx_company (company_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE sections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NULL,
    section_name VARCHAR(100) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    INDEX idx_company (company_id),
    UNIQUE KEY unique_section_company (section_name, company_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE shift_definitions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    shift_name VARCHAR(50) NOT NULL,
    start_time TIME NULL,
    end_time TIME NULL,
    duration_hours DECIMAL(4,2) NULL,
    category ENUM('AM','MID','PM','MIDNIGHT','OVERNIGHT','OFF') NOT NULL,
    color_code VARCHAR(20),
    shift_type_id INT NULL,
    FOREIGN KEY (shift_type_id) REFERENCES shift_types(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NULL,
    username VARCHAR(100) NOT NULL,
    password_hash VARCHAR(255) NULL,
    email VARCHAR(150),
    role VARCHAR(50) NULL,
    onboarding_completed TINYINT(1) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    INDEX idx_company (company_id),
    UNIQUE KEY unique_username_company (username, company_id),
    UNIQUE KEY unique_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE weeks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NULL,
    week_start_date DATE NOT NULL,
    week_end_date DATE NOT NULL,
    is_locked_for_requests TINYINT(1) DEFAULT 0,
    lock_reason VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    INDEX idx_company (company_id),
    UNIQUE KEY unique_week_company (week_start_date, company_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE user_roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    role_id INT NOT NULL,
    section_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id),
    FOREIGN KEY (section_id) REFERENCES sections(id),
    UNIQUE KEY unique_user_role_section (user_id, role_id, section_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE shift_requirements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NULL,
    week_id INT NOT NULL,
    section_id INT NOT NULL,
    shift_date DATE NOT NULL,
    shift_type_id INT NOT NULL,
    required_count INT NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (week_id) REFERENCES weeks(id) ON DELETE CASCADE,
    FOREIGN KEY (section_id) REFERENCES sections(id),
    FOREIGN KEY (shift_type_id) REFERENCES shift_types(id),
    INDEX idx_company (company_id),
    UNIQUE KEY unique_shift_requirement (week_id, section_id, shift_date, shift_type_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_role_id INT NOT NULL,
    employee_code VARCHAR(50) NOT NULL,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(150),
    is_senior TINYINT(1) DEFAULT 0,
    seniority_level INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_role_id) REFERENCES user_roles(id) ON DELETE CASCADE,
    UNIQUE KEY unique_employee_code (employee_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NULL,
    week_id INT NOT NULL,
    section_id INT NOT NULL,
    generated_by_admin_id INT NOT NULL,
    generated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('DRAFT','FINAL') DEFAULT 'DRAFT',
    notes TEXT NULL,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (week_id) REFERENCES weeks(id) ON DELETE CASCADE,
    FOREIGN KEY (section_id) REFERENCES sections(id),
    FOREIGN KEY (generated_by_admin_id) REFERENCES employees(id),
    INDEX idx_company (company_id),
    UNIQUE KEY unique_schedule_company_week_section (week_id, section_id, company_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE schedule_shifts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    schedule_id INT NOT NULL,
    shift_date DATE NOT NULL,
    shift_definition_id INT NOT NULL,
    required_count INT NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (schedule_id) REFERENCES schedules(id) ON DELETE CASCADE,
    FOREIGN KEY (shift_definition_id) REFERENCES shift_definitions(id),
    INDEX idx_schedule_date (schedule_id, shift_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE shift_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    week_id INT NOT NULL,
    request_date DATE NOT NULL,
    shift_definition_id INT NULL,
    is_day_off TINYINT(1) DEFAULT 0,
    schedule_pattern_id INT NOT NULL,
    reason TEXT NULL,
    importance_level ENUM('LOW','MEDIUM','HIGH','EMERGENCY') NOT NULL DEFAULT 'MEDIUM',
    status ENUM('PENDING','APPROVED','DECLINED') NOT NULL DEFAULT 'PENDING',
    submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    reviewed_by_admin_id INT NULL,
    reviewed_at DATETIME NULL,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (week_id) REFERENCES weeks(id) ON DELETE CASCADE,
    FOREIGN KEY (shift_definition_id) REFERENCES shift_definitions(id),
    FOREIGN KEY (schedule_pattern_id) REFERENCES schedule_patterns(id),
    FOREIGN KEY (reviewed_by_admin_id) REFERENCES employees(id),
    INDEX idx_week_employee (week_id, employee_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE schedule_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    schedule_shift_id INT NOT NULL,
    employee_id INT NOT NULL,
    assignment_source ENUM('MATCHED_REQUEST','AUTO_ASSIGNED','MANUALLY_ADJUSTED') NOT NULL DEFAULT 'MATCHED_REQUEST',
    is_senior TINYINT(1) DEFAULT 0,
    notes VARCHAR(255) DEFAULT '',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (schedule_shift_id) REFERENCES schedule_shifts(id) ON DELETE CASCADE,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    UNIQUE KEY unique_assignment (schedule_shift_id, employee_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE employee_breaks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    schedule_shift_id INT NULL,
    worked_date DATE NOT NULL,
    break_start DATETIME NULL,
    break_end DATETIME NULL,
    is_active TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (schedule_shift_id) REFERENCES schedule_shifts(id) ON DELETE SET NULL,
    UNIQUE KEY unique_employee_break (employee_id, worked_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NULL,
    user_id INT NOT NULL,
    type ENUM('SHIFT_REMINDER','SCHEDULE_PUBLISHED','REQUEST_STATUS') NOT NULL,
    title VARCHAR(150) NOT NULL,
    body TEXT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    read_at DATETIME NULL,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_company (company_id),
    INDEX idx_user_read (user_id, is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===============================
-- Seed Reference Data
-- ===============================
INSERT INTO roles (role_name, description) VALUES
    ('Director', 'Executive oversight across sections'),
    ('Team Leader', 'Full scheduling and employee management'),
    ('Supervisor', 'Monitoring and reporting'),
    ('Senior', 'Shift leader for operational coverage'),
    ('Employee', 'Shift requests and schedule access')
ON DUPLICATE KEY UPDATE description = VALUES(description);

INSERT INTO shift_types (code, name, start_time, end_time, duration_hours) VALUES
    ('AM', 'Morning', '06:00:00', '14:00:00', 8.00),
    ('MID', 'Mid', '10:00:00', '18:00:00', 8.00),
    ('PM', 'Evening', '14:00:00', '22:00:00', 8.00),
    ('MIDNIGHT', 'Midnight', '18:00:00', '02:00:00', 8.00),
    ('OVERNIGHT', 'Overnight', '22:00:00', '06:00:00', 8.00),
    ('FLEX', 'Flex', '09:00:00', '17:00:00', 8.00),
    ('SPLIT', 'Split Shift', '08:00:00', '16:00:00', 8.00)
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO shift_definitions (shift_name, start_time, end_time, duration_hours, category, color_code, shift_type_id) VALUES
    ('AM Shift', '06:00:00', '14:00:00', 8.00, 'AM', '#38bdf8', 1),
    ('Mid Shift', '10:00:00', '18:00:00', 8.00, 'MID', '#6366f1', 2),
    ('PM Shift', '14:00:00', '22:00:00', 8.00, 'PM', '#f97316', 3),
    ('Midnight Shift', '18:00:00', '02:00:00', 8.00, 'MIDNIGHT', '#0f172a', 4),
    ('Overnight Shift', '22:00:00', '06:00:00', 8.00, 'OVERNIGHT', '#334155', 5),
    ('Flex Shift', '09:00:00', '17:00:00', 8.00, 'AM', '#22c55e', 6),
    ('Split Shift', '08:00:00', '16:00:00', 8.00, 'MID', '#ec4899', 7),
    ('Day Off', NULL, NULL, 0.00, 'OFF', '#94a3b8', NULL)
ON DUPLICATE KEY UPDATE shift_name = VALUES(shift_name);

INSERT INTO schedule_patterns (name, work_days_per_week, off_days_per_week, default_shift_duration_hours, description) VALUES
    ('5x2', 5, 2, 8.00, '5 days on / 2 days off'),
    ('6x1', 6, 1, 8.00, '6 days on / 1 day off'),
    ('4x3', 4, 3, 10.00, '4 days on / 3 days off'),
    ('Rotating', 5, 2, 8.00, 'Rotating schedule pattern'),
    ('Weekend Coverage', 4, 3, 8.00, 'Weekend-focused staffing plan')
ON DUPLICATE KEY UPDATE description = VALUES(description);

INSERT INTO system_settings (system_key, system_value, description) VALUES
    ('schedule_generation_mode', 'auto', 'Default weekly schedule generation mode'),
    ('break_duration_minutes', '30', 'Standard break duration in minutes'),
    ('shift_request_window_days', '7', 'Days in advance allowed for shift requests'),
    ('timezone_default', 'UTC', 'Default timezone for new companies'),
    ('compliance_mode', 'enabled', 'Enable compliance tracking features')
ON DUPLICATE KEY UPDATE system_value = VALUES(system_value);

-- ===============================
-- Stored Procedures
-- ===============================
DELIMITER $$

DROP PROCEDURE IF EXISTS sp_get_roles $$
CREATE PROCEDURE sp_get_roles()
BEGIN
    SELECT id, role_name, description
    FROM roles
    ORDER BY id;
END $$

DROP PROCEDURE IF EXISTS sp_get_company_by_email $$
CREATE PROCEDURE sp_get_company_by_email(IN p_admin_email VARCHAR(191))
BEGIN
    SELECT * FROM companies WHERE admin_email = p_admin_email LIMIT 1;
END $$

DROP PROCEDURE IF EXISTS sp_get_company_by_id $$
CREATE PROCEDURE sp_get_company_by_id(IN p_company_id INT)
BEGIN
    SELECT * FROM companies WHERE id = p_company_id LIMIT 1;
END $$

DROP PROCEDURE IF EXISTS sp_mark_company_verified $$
CREATE PROCEDURE sp_mark_company_verified(IN p_company_id INT)
BEGIN
    UPDATE companies
    SET status = 'VERIFIED',
        email_verified_at = NOW(),
        verification_token = NULL
    WHERE id = p_company_id AND status != 'ACTIVE';
    SELECT ROW_COUNT() AS updated;
END $$

DROP PROCEDURE IF EXISTS sp_activate_company $$
CREATE PROCEDURE sp_activate_company(IN p_company_id INT)
BEGIN
    UPDATE companies
    SET status = 'ACTIVE',
        onboarding_completed_at = NOW()
    WHERE id = p_company_id;
    SELECT ROW_COUNT() AS updated;
END $$

DROP PROCEDURE IF EXISTS sp_upsert_onboarding_step $$
CREATE PROCEDURE sp_upsert_onboarding_step(
    IN p_company_id INT,
    IN p_step VARCHAR(50),
    IN p_step_data JSON,
    IN p_completed TINYINT
)
BEGIN
    INSERT INTO company_onboarding (company_id, step, step_data, completed, completed_at)
    VALUES (p_company_id, p_step, p_step_data, p_completed, IF(p_completed = 1, NOW(), NULL))
    ON DUPLICATE KEY UPDATE
        step_data = VALUES(step_data),
        completed = VALUES(completed),
        completed_at = IF(VALUES(completed) = 1, NOW(), completed_at);
END $$

DROP PROCEDURE IF EXISTS sp_get_onboarding_progress $$
CREATE PROCEDURE sp_get_onboarding_progress(IN p_company_id INT)
BEGIN
    SELECT step, step_data, completed, completed_at
    FROM company_onboarding
    WHERE company_id = p_company_id
    ORDER BY created_at;
END $$

DROP PROCEDURE IF EXISTS sp_create_company $$
CREATE PROCEDURE sp_create_company(
    IN p_company_name VARCHAR(255),
    IN p_admin_email VARCHAR(255),
    IN p_admin_password_hash VARCHAR(255),
    IN p_timezone VARCHAR(50),
    IN p_country VARCHAR(100),
    IN p_company_size VARCHAR(50),
    IN p_verification_token VARCHAR(255)
)
BEGIN
    DECLARE v_company_slug VARCHAR(255);
    DECLARE v_slug_exists INT DEFAULT 1;
    DECLARE v_counter INT DEFAULT 0;

    SET v_company_slug = LOWER(REGEXP_REPLACE(p_company_name COLLATE utf8mb4_unicode_ci, '[^a-zA-Z0-9]+', '-')) COLLATE utf8mb4_unicode_ci;
    SET v_company_slug = TRIM(BOTH '-' FROM v_company_slug);

    WHILE v_slug_exists > 0 DO
        SELECT COUNT(*) INTO v_slug_exists FROM companies WHERE company_slug COLLATE utf8mb4_unicode_ci = v_company_slug;
        IF v_slug_exists > 0 THEN
            SET v_counter = v_counter + 1;
            SET v_company_slug = CONCAT(v_company_slug, '-', v_counter) COLLATE utf8mb4_unicode_ci;
        END IF;
    END WHILE;

    INSERT INTO companies (
        company_name, company_slug, admin_email, admin_password_hash,
        timezone, country, company_size, verification_token, status, email_verified_at
    ) VALUES (
        p_company_name, v_company_slug, p_admin_email, p_admin_password_hash,
        COALESCE(p_timezone, 'UTC'), p_country, p_company_size, p_verification_token, 'VERIFIED', NOW()
    );

    SELECT LAST_INSERT_ID() AS company_id;
END $$

DROP PROCEDURE IF EXISTS sp_create_director $$
CREATE PROCEDURE sp_create_director(
    IN p_company_id INT,
    IN p_company_name VARCHAR(255),
    IN p_username VARCHAR(100),
    IN p_password_hash VARCHAR(255),
    IN p_email VARCHAR(150),
    IN p_full_name VARCHAR(150)
)
BEGIN
    DECLARE v_user_id INT;
    DECLARE v_role_id INT;
    DECLARE v_section_id INT;
    DECLARE v_username_candidate VARCHAR(100);
    DECLARE v_username_exists INT DEFAULT 1;
    DECLARE v_suffix INT DEFAULT 0;

    SELECT id INTO v_role_id FROM roles WHERE role_name = 'Director' LIMIT 1;

    SELECT id INTO v_section_id
    FROM sections
    WHERE company_id = p_company_id AND section_name = CONCAT(p_company_name, ' - Main')
    LIMIT 1;

    IF v_section_id IS NULL THEN
        INSERT INTO sections (section_name, company_id)
        VALUES (CONCAT(p_company_name, ' - Main'), p_company_id);
        SET v_section_id = LAST_INSERT_ID();
    END IF;

    SET v_username_candidate = p_username;
    WHILE v_username_exists > 0 DO
        SELECT COUNT(*) INTO v_username_exists
        FROM users
        WHERE username = v_username_candidate AND company_id <=> p_company_id;
        IF v_username_exists > 0 THEN
            SET v_suffix = v_suffix + 1;
            SET v_username_candidate = CONCAT(p_username, v_suffix);
        END IF;
    END WHILE;

    INSERT INTO users (company_id, username, password_hash, email, role, onboarding_completed, is_active)
    VALUES (p_company_id, v_username_candidate, p_password_hash, p_email, 'Director', 1, 1);
    SET v_user_id = LAST_INSERT_ID();

    INSERT INTO user_roles (user_id, role_id, section_id)
    VALUES (v_user_id, v_role_id, v_section_id);

    SELECT v_user_id AS user_id;
END $$

DROP PROCEDURE IF EXISTS sp_get_user_by_email $$
CREATE PROCEDURE sp_get_user_by_email(IN p_email VARCHAR(150))
BEGIN
    SELECT u.id AS user_id,
           u.username,
           u.email,
           u.password_hash,
           u.onboarding_completed,
           u.is_active,
           u.company_id,
           ur.id AS user_role_id,
           r.id AS role_id,
           r.role_name,
           s.id AS section_id,
           s.section_name,
           e.id AS employee_id,
           e.full_name AS employee_name,
           e.is_senior,
           e.seniority_level,
           e.employee_code
    FROM users u
    INNER JOIN user_roles ur ON ur.user_id = u.id
    INNER JOIN roles r ON r.id = ur.role_id
    INNER JOIN sections s ON s.id = ur.section_id
    LEFT JOIN employees e ON e.user_role_id = ur.id
    WHERE u.email = p_email AND u.is_active = 1;
END $$

DROP PROCEDURE IF EXISTS sp_get_user_by_identifier $$
CREATE PROCEDURE sp_get_user_by_identifier(IN p_identifier VARCHAR(150))
BEGIN
    SELECT u.id AS user_id,
           u.username,
           u.email,
           u.password_hash,
           u.onboarding_completed,
           u.is_active,
           u.company_id,
           ur.id AS user_role_id,
           r.id AS role_id,
           r.role_name,
           s.id AS section_id,
           s.section_name,
           e.id AS employee_id,
           e.full_name AS employee_name,
           e.is_senior,
           e.seniority_level,
           e.employee_code
    FROM users u
    INNER JOIN user_roles ur ON ur.user_id = u.id
    INNER JOIN roles r ON r.id = ur.role_id
    INNER JOIN sections s ON s.id = ur.section_id
    LEFT JOIN employees e ON e.user_role_id = ur.id
    WHERE (u.username = p_identifier OR u.email = p_identifier) AND u.is_active = 1;
END $$

DROP PROCEDURE IF EXISTS sp_user_email_exists $$
CREATE PROCEDURE sp_user_email_exists(IN p_email VARCHAR(150))
BEGIN
    SELECT COUNT(*) > 0 AS email_exists
    FROM users
    WHERE email = p_email;
END $$

DROP PROCEDURE IF EXISTS sp_verify_company_email $$
CREATE PROCEDURE sp_verify_company_email(IN p_token VARCHAR(255))
BEGIN
    UPDATE companies
    SET status = 'VERIFIED', email_verified_at = NOW(), verification_token = NULL
    WHERE verification_token = p_token AND status = 'PENDING_VERIFICATION';
    SELECT ROW_COUNT() AS updated;
END $$

DROP PROCEDURE IF EXISTS sp_complete_company_payment $$
CREATE PROCEDURE sp_complete_company_payment(IN p_company_id INT, IN p_payment_token VARCHAR(255), IN p_payment_amount DECIMAL(10,2))
BEGIN
    UPDATE companies
    SET payment_status = 'COMPLETED',
        payment_completed_at = NOW(),
        payment_token = p_payment_token,
        payment_amount = p_payment_amount,
        status = 'ACTIVE'
    WHERE id = p_company_id AND status IN ('PAYMENT_PENDING', 'ONBOARDING');
    SELECT ROW_COUNT() AS updated;
END $$

DROP PROCEDURE IF EXISTS sp_upsert_week $$
CREATE PROCEDURE sp_upsert_week(IN p_week_start DATE, IN p_week_end DATE)
BEGIN
    DECLARE v_week_id INT;
    SELECT id INTO v_week_id FROM weeks WHERE week_start_date = p_week_start LIMIT 1;
    IF v_week_id IS NULL THEN
        INSERT INTO weeks (week_start_date, week_end_date) VALUES (p_week_start, p_week_end);
        SET v_week_id = LAST_INSERT_ID();
    END IF;
    SELECT v_week_id AS week_id;
END $$

DROP PROCEDURE IF EXISTS sp_get_shift_types $$
CREATE PROCEDURE sp_get_shift_types()
BEGIN
    SELECT id,
           id AS shift_type_id,
           code,
           name AS shift_type_name,
           start_time,
           end_time
    FROM shift_types
    ORDER BY id;
END $$

DROP PROCEDURE IF EXISTS sp_get_shift_definitions $$
CREATE PROCEDURE sp_get_shift_definitions()
BEGIN
    SELECT sd.id AS definition_id,
           sd.shift_name AS definition_name,
           sd.start_time,
           sd.end_time,
           sd.duration_hours,
           sd.category,
           sd.color_code,
           st.id AS shift_type_id,
           st.name AS shift_type_name
    FROM shift_definitions sd
    LEFT JOIN shift_types st ON st.id = sd.shift_type_id
    ORDER BY sd.id;
END $$

DROP PROCEDURE IF EXISTS sp_get_schedule_patterns $$
CREATE PROCEDURE sp_get_schedule_patterns()
BEGIN
    SELECT id, name, work_days_per_week, off_days_per_week, default_shift_duration_hours, description
    FROM schedule_patterns
    ORDER BY id;
END $$

DROP PROCEDURE IF EXISTS sp_get_shift_requirements $$
CREATE PROCEDURE sp_get_shift_requirements(IN p_week_id INT, IN p_section_id INT)
BEGIN
    SELECT sr.id,
           sr.shift_date,
           sr.shift_date AS date,
           sr.required_count,
           st.id AS shift_type_id,
           st.name AS shift_type_name,
           sd.shift_name
    FROM shift_requirements sr
    INNER JOIN shift_types st ON st.id = sr.shift_type_id
    INNER JOIN shift_definitions sd ON sd.shift_type_id = st.id
    WHERE sr.week_id = p_week_id AND sr.section_id = p_section_id;
END $$

DROP PROCEDURE IF EXISTS sp_set_shift_requirement $$
CREATE PROCEDURE sp_set_shift_requirement(
    IN p_week_id INT,
    IN p_section_id INT,
    IN p_date DATE,
    IN p_shift_type_id INT,
    IN p_required_count INT
)
BEGIN
    INSERT INTO shift_requirements (week_id, section_id, shift_date, shift_type_id, required_count)
    VALUES (p_week_id, p_section_id, p_date, p_shift_type_id, p_required_count)
    ON DUPLICATE KEY UPDATE required_count = p_required_count;
END $$

DROP PROCEDURE IF EXISTS sp_generate_weekly_schedule $$
CREATE PROCEDURE sp_generate_weekly_schedule(IN p_week_id INT, IN p_section_id INT, IN p_generated_by_employee_id INT)
BEGIN
    DECLARE v_schedule_id INT;
    DECLARE v_assignments_needed INT DEFAULT 1;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    START TRANSACTION;

    SELECT id INTO v_schedule_id
    FROM schedules
    WHERE week_id = p_week_id AND section_id = p_section_id
    LIMIT 1;

    IF v_schedule_id IS NULL THEN
        INSERT INTO schedules (week_id, section_id, generated_by_admin_id, status)
        VALUES (p_week_id, p_section_id, p_generated_by_employee_id, 'DRAFT');
        SET v_schedule_id = LAST_INSERT_ID();
    ELSE
        DELETE FROM schedule_assignments
        WHERE schedule_shift_id IN (SELECT id FROM schedule_shifts WHERE schedule_id = v_schedule_id);
        DELETE FROM schedule_shifts WHERE schedule_id = v_schedule_id;
    END IF;

    INSERT INTO schedule_shifts (schedule_id, shift_date, shift_definition_id, required_count)
    SELECT v_schedule_id, sr.shift_date, sd.id, sr.required_count
    FROM shift_requirements sr
    INNER JOIN shift_types st ON st.id = sr.shift_type_id
    INNER JOIN shift_definitions sd ON sd.shift_type_id = st.id
    WHERE sr.week_id = p_week_id AND sr.section_id = p_section_id;

    INSERT INTO schedule_assignments (schedule_shift_id, employee_id, assignment_source, is_senior, notes)
    SELECT ss.id, sr.employee_id, 'MATCHED_REQUEST', e.is_senior, CONCAT('Request: ', COALESCE(sr.reason, ''))
    FROM schedule_shifts ss
    INNER JOIN shift_requests sr ON sr.week_id = p_week_id AND sr.request_date = ss.shift_date
    INNER JOIN employees e ON e.id = sr.employee_id
    INNER JOIN user_roles ur ON ur.id = e.user_role_id
    WHERE ss.shift_definition_id = sr.shift_definition_id
      AND sr.status = 'APPROVED'
      AND sr.is_day_off = 0
      AND ur.section_id = p_section_id
      AND NOT EXISTS (
          SELECT 1 FROM schedule_assignments sa
          WHERE sa.schedule_shift_id = ss.id AND sa.employee_id = e.id
      );

    SET v_assignments_needed = 1;
    WHILE v_assignments_needed > 0 DO
        INSERT INTO schedule_assignments (schedule_shift_id, employee_id, assignment_source, is_senior, notes)
        SELECT ss.id, e.id, 'AUTO_ASSIGNED', e.is_senior, 'Auto-assigned by system'
        FROM schedule_shifts ss
        INNER JOIN shift_definitions sd ON sd.id = ss.shift_definition_id
        INNER JOIN employees e ON e.is_active = 1
        INNER JOIN user_roles ur ON ur.id = e.user_role_id
        LEFT JOIN schedule_assignments sa ON sa.schedule_shift_id = ss.id AND sa.employee_id = e.id
        WHERE ur.section_id = p_section_id
          AND sa.id IS NULL
          AND sd.category <> 'OFF'
          AND (SELECT COUNT(*) FROM schedule_assignments WHERE schedule_shift_id = ss.id) < ss.required_count
        ORDER BY e.seniority_level DESC, e.id
        LIMIT 1;

        SET v_assignments_needed = ROW_COUNT();
    END WHILE;

    COMMIT;
    SELECT v_schedule_id AS schedule_id, 'Schedule generated successfully' AS message;
END $$

DROP PROCEDURE IF EXISTS sp_get_weekly_schedule $$
CREATE PROCEDURE sp_get_weekly_schedule(IN p_week_id INT, IN p_section_id INT)
BEGIN
    SELECT ss.id AS schedule_shift_id,
           ss.shift_date,
           sd.id AS shift_definition_id,
           sd.shift_name,
           sd.category AS shift_category,
           sd.color_code,
           sd.start_time,
           sd.end_time,
           sd.duration_hours,
           sa.id AS assignment_id,
           sa.assignment_source,
           sa.notes,
           e.id AS employee_id,
           e.full_name AS employee_name,
           e.employee_code,
           e.is_senior
    FROM schedule_shifts ss
    INNER JOIN schedules s ON s.id = ss.schedule_id
    INNER JOIN shift_definitions sd ON sd.id = ss.shift_definition_id
    LEFT JOIN schedule_assignments sa ON sa.schedule_shift_id = ss.id
    LEFT JOIN employees e ON e.id = sa.employee_id
    WHERE s.week_id = p_week_id AND s.section_id = p_section_id
    ORDER BY ss.shift_date, sd.shift_name, e.full_name;
END $$

DROP PROCEDURE IF EXISTS sp_get_today_shift $$
CREATE PROCEDURE sp_get_today_shift(IN p_section_id INT, IN p_today DATE)
BEGIN
    SELECT ss.id AS schedule_shift_id,
           ss.shift_date,
           sd.id AS shift_definition_id,
           sd.shift_name,
           sd.category AS shift_category,
           sd.start_time,
           sd.end_time,
           sa.id AS assignment_id,
           e.id AS employee_id,
           e.full_name AS employee_name,
           e.employee_code,
           e.is_senior,
           'Scheduled' AS attendance_status
    FROM schedule_shifts ss
    INNER JOIN schedules s ON s.id = ss.schedule_id
    INNER JOIN shift_definitions sd ON sd.id = ss.shift_definition_id
    LEFT JOIN schedule_assignments sa ON sa.schedule_shift_id = ss.id
    LEFT JOIN employees e ON e.id = sa.employee_id
    WHERE s.section_id = p_section_id
      AND ss.shift_date = p_today
      AND sd.category <> 'OFF'
    ORDER BY sd.start_time, e.full_name;
END $$

DROP PROCEDURE IF EXISTS sp_get_coverage_gaps $$
CREATE PROCEDURE sp_get_coverage_gaps(IN p_week_id INT, IN p_section_id INT)
BEGIN
    SELECT ss.shift_date,
           sd.shift_name,
           ss.required_count,
           COUNT(sa.id) AS assigned_count
    FROM schedules s
    INNER JOIN schedule_shifts ss ON ss.schedule_id = s.id
    INNER JOIN shift_definitions sd ON sd.id = ss.shift_definition_id
    LEFT JOIN schedule_assignments sa ON sa.schedule_shift_id = ss.id
    WHERE s.week_id = p_week_id AND s.section_id = p_section_id
    GROUP BY ss.id
    HAVING assigned_count < ss.required_count;
END $$

DROP PROCEDURE IF EXISTS sp_update_schedule_assignment $$
CREATE PROCEDURE sp_update_schedule_assignment(
    IN p_assignment_id INT,
    IN p_shift_definition_id INT,
    IN p_employee_id INT
)
BEGIN
    DECLARE v_schedule_shift_id INT;
    DECLARE v_new_shift_id INT;

    SELECT schedule_shift_id INTO v_schedule_shift_id
    FROM schedule_assignments
    WHERE id = p_assignment_id;

    SELECT id INTO v_new_shift_id
    FROM schedule_shifts
    WHERE schedule_id = (SELECT schedule_id FROM schedule_shifts WHERE id = v_schedule_shift_id)
      AND shift_date = (SELECT shift_date FROM schedule_shifts WHERE id = v_schedule_shift_id)
      AND shift_definition_id = p_shift_definition_id
    LIMIT 1;

    IF p_employee_id IS NOT NULL THEN
        UPDATE schedule_assignments
        SET schedule_shift_id = COALESCE(v_new_shift_id, v_schedule_shift_id),
            employee_id = p_employee_id,
            assignment_source = 'MANUALLY_ADJUSTED',
            notes = CONCAT(COALESCE(notes, ''), ' | Manually adjusted')
        WHERE id = p_assignment_id;
    ELSE
        UPDATE schedule_assignments
        SET schedule_shift_id = COALESCE(v_new_shift_id, v_schedule_shift_id),
            assignment_source = 'MANUALLY_ADJUSTED',
            notes = CONCAT(COALESCE(notes, ''), ' | Manually adjusted')
        WHERE id = p_assignment_id;
    END IF;

    SELECT ROW_COUNT() AS affected_rows;
END $$

DROP PROCEDURE IF EXISTS sp_delete_schedule_assignment $$
CREATE PROCEDURE sp_delete_schedule_assignment(IN p_assignment_id INT)
BEGIN
    DELETE FROM schedule_assignments WHERE id = p_assignment_id;
    SELECT ROW_COUNT() AS affected_rows;
END $$

DROP PROCEDURE IF EXISTS sp_start_break $$
CREATE PROCEDURE sp_start_break(IN p_employee_id INT, IN p_worked_date DATE, IN p_schedule_shift_id INT)
BEGIN
    DECLARE v_existing_break INT;
    SELECT id INTO v_existing_break
    FROM employee_breaks
    WHERE employee_id = p_employee_id AND worked_date = p_worked_date;

    IF v_existing_break IS NOT NULL THEN
        UPDATE employee_breaks
        SET break_start = NOW(), is_active = 1, schedule_shift_id = p_schedule_shift_id
        WHERE id = v_existing_break;
    ELSE
        INSERT INTO employee_breaks (employee_id, schedule_shift_id, worked_date, break_start, is_active)
        VALUES (p_employee_id, p_schedule_shift_id, p_worked_date, NOW(), 1);
    END IF;

    SELECT LAST_INSERT_ID() AS break_id, 'Break started' AS message;
END $$

DROP PROCEDURE IF EXISTS sp_end_break $$
CREATE PROCEDURE sp_end_break(IN p_employee_id INT, IN p_worked_date DATE)
BEGIN
    UPDATE employee_breaks
    SET break_end = NOW(), is_active = 0
    WHERE employee_id = p_employee_id AND worked_date = p_worked_date AND is_active = 1;

    SELECT ROW_COUNT() AS affected_rows, 'Break ended' AS message;
END $$

DROP PROCEDURE IF EXISTS sp_get_break_status $$
CREATE PROCEDURE sp_get_break_status(IN p_section_id INT, IN p_today DATE)
BEGIN
    SELECT eb.id AS break_id,
           e.id AS employee_id,
           e.full_name AS employee_name,
           e.employee_code,
           sd.shift_name,
           sd.category AS shift_category,
           eb.break_start,
           eb.break_end,
           eb.is_active,
           CASE
               WHEN eb.break_end IS NULL AND eb.break_start IS NOT NULL THEN GREATEST(TIMESTAMPDIFF(MINUTE, eb.break_start, NOW()) - 30, 0)
               WHEN eb.break_end IS NOT NULL THEN GREATEST(TIMESTAMPDIFF(MINUTE, eb.break_start, eb.break_end) - 30, 0)
               ELSE 0
           END AS delay_minutes,
           CASE
               WHEN eb.is_active = 1 THEN 'ON_BREAK'
               WHEN eb.break_start IS NULL THEN 'NOT_STARTED'
               WHEN eb.break_end IS NOT NULL THEN 'COMPLETED'
               ELSE 'UNKNOWN'
           END AS status
    FROM employee_breaks eb
    INNER JOIN employees e ON e.id = eb.employee_id
    INNER JOIN user_roles ur ON ur.id = e.user_role_id
    INNER JOIN sections s ON s.id = ur.section_id
    LEFT JOIN schedule_shifts ss ON ss.id = eb.schedule_shift_id
    LEFT JOIN shift_definitions sd ON sd.id = ss.shift_definition_id
    WHERE s.id = p_section_id AND eb.worked_date = p_today
    ORDER BY e.full_name;
END $$

DROP PROCEDURE IF EXISTS sp_get_employee_break $$
CREATE PROCEDURE sp_get_employee_break(IN p_employee_id INT, IN p_worked_date DATE)
BEGIN
    SELECT eb.id AS break_id,
           eb.break_start,
           eb.break_end,
           eb.is_active,
           CASE
               WHEN eb.break_end IS NULL AND eb.break_start IS NOT NULL THEN GREATEST(TIMESTAMPDIFF(MINUTE, eb.break_start, NOW()) - 30, 0)
               WHEN eb.break_end IS NOT NULL THEN GREATEST(TIMESTAMPDIFF(MINUTE, eb.break_start, eb.break_end) - 30, 0)
               ELSE 0
           END AS delay_minutes
    FROM employee_breaks eb
    WHERE eb.employee_id = p_employee_id AND eb.worked_date = p_worked_date;
END $$

DROP PROCEDURE IF EXISTS sp_submit_shift_request $$
CREATE PROCEDURE sp_submit_shift_request(
    IN p_employee_id INT,
    IN p_week_id INT,
    IN p_request_date DATE,
    IN p_shift_definition_id INT,
    IN p_is_day_off TINYINT,
    IN p_schedule_pattern_id INT,
    IN p_reason TEXT,
    IN p_importance_level VARCHAR(10)
)
BEGIN
    DECLARE v_day_of_week INT;
    DECLARE v_role_name VARCHAR(50);

    SET v_day_of_week = DAYOFWEEK(p_request_date);
    IF v_day_of_week = 1 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Shift requests are not allowed on Sunday';
    END IF;

    SELECT r.role_name INTO v_role_name
    FROM employees e
    INNER JOIN user_roles ur ON ur.id = e.user_role_id
    INNER JOIN roles r ON r.id = ur.role_id
    WHERE e.id = p_employee_id;

    IF v_role_name = 'Senior' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Senior employees cannot submit shift requests';
    END IF;

    INSERT INTO shift_requests (employee_id, week_id, request_date, shift_definition_id, is_day_off, schedule_pattern_id, reason, importance_level)
    VALUES (p_employee_id, p_week_id, NULLIF(p_shift_definition_id, 0), p_is_day_off, p_schedule_pattern_id, p_reason, p_importance_level);

    SELECT LAST_INSERT_ID() AS request_id, 'Request submitted successfully' AS message;
END $$

DROP PROCEDURE IF EXISTS sp_get_shift_requests $$
CREATE PROCEDURE sp_get_shift_requests(IN p_week_id INT, IN p_section_id INT, IN p_employee_id INT)
BEGIN
    SELECT sr.id,
           sr.request_date,
           sr.importance_level,
           sr.status,
           sr.reason,
           sr.submitted_at,
           sr.reviewed_at,
           sd.shift_name,
           sd.category AS shift_category,
           sp.name AS pattern_name,
           e.id AS employee_id,
           e.full_name AS employee_name,
           e.employee_code,
           reviewer.full_name AS reviewer_name
    FROM shift_requests sr
    INNER JOIN employees e ON e.id = sr.employee_id
    INNER JOIN user_roles ur ON ur.id = e.user_role_id
    INNER JOIN sections s ON s.id = ur.section_id
    LEFT JOIN shift_definitions sd ON sd.id = sr.shift_definition_id
    INNER JOIN schedule_patterns sp ON sp.id = sr.schedule_pattern_id
    LEFT JOIN employees reviewer ON reviewer.id = sr.reviewed_by_admin_id
    WHERE sr.week_id = p_week_id AND s.id = p_section_id
      AND (p_employee_id IS NULL OR e.id = p_employee_id)
    ORDER BY sr.submitted_at DESC;
END $$

DROP PROCEDURE IF EXISTS sp_update_shift_request_status $$
CREATE PROCEDURE sp_update_shift_request_status(IN p_request_id INT, IN p_status VARCHAR(10), IN p_reviewer_id INT)
BEGIN
    UPDATE shift_requests
    SET status = p_status,
        reviewed_by_admin_id = p_reviewer_id,
        reviewed_at = NOW()
    WHERE id = p_request_id;
    SELECT ROW_COUNT() AS affected_rows;
END $$

DROP PROCEDURE IF EXISTS sp_create_employee $$
CREATE PROCEDURE sp_create_employee(
    IN p_username VARCHAR(100),
    IN p_password_hash VARCHAR(255),
    IN p_email VARCHAR(150),
    IN p_role_id INT,
    IN p_section_id INT,
    IN p_employee_code VARCHAR(50),
    IN p_full_name VARCHAR(150),
    IN p_is_senior TINYINT,
    IN p_seniority_level INT
)
BEGIN
    DECLARE v_user_id INT;
    DECLARE v_user_role_id INT;
    DECLARE v_employee_id INT;
    DECLARE v_username_exists INT DEFAULT 0;
    DECLARE v_email_exists INT DEFAULT 0;
    DECLARE v_employee_code_exists INT DEFAULT 0;
    DECLARE v_full_name_exists INT DEFAULT 0;
    DECLARE v_company_id INT DEFAULT NULL;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    START TRANSACTION;

    SELECT company_id INTO v_company_id FROM sections WHERE id = p_section_id LIMIT 1;
    SELECT COUNT(*) INTO v_username_exists FROM users WHERE username = p_username AND company_id <=> v_company_id;
    IF v_username_exists > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Username already exists';
    END IF;

    IF p_email IS NOT NULL AND p_email != '' THEN
        SELECT COUNT(*) INTO v_email_exists FROM users WHERE email = p_email AND email IS NOT NULL AND email != '' AND company_id <=> v_company_id;
        IF v_email_exists > 0 THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Email already exists';
        END IF;
    END IF;

    SELECT COUNT(*) INTO v_employee_code_exists FROM employees WHERE employee_code = p_employee_code;
    IF v_employee_code_exists > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Employee code already exists';
    END IF;

    SELECT COUNT(*) INTO v_full_name_exists FROM employees WHERE full_name = p_full_name;
    IF v_full_name_exists > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Full name already exists';
    END IF;

    INSERT INTO users (company_id, username, password_hash, email)
    VALUES (v_company_id, p_username, p_password_hash, p_email);
    SET v_user_id = LAST_INSERT_ID();

    INSERT INTO user_roles (user_id, role_id, section_id)
    VALUES (v_user_id, p_role_id, p_section_id);
    SET v_user_role_id = LAST_INSERT_ID();

    INSERT INTO employees (user_role_id, employee_code, full_name, email, is_senior, seniority_level)
    VALUES (v_user_role_id, p_employee_code, p_full_name, p_email, p_is_senior, p_seniority_level);
    SET v_employee_id = LAST_INSERT_ID();

    COMMIT;
    SELECT v_employee_id AS employee_id, v_user_id AS user_id;
END $$

DROP PROCEDURE IF EXISTS sp_create_leader $$
CREATE PROCEDURE sp_create_leader(
    IN p_username VARCHAR(100),
    IN p_password_hash VARCHAR(255),
    IN p_email VARCHAR(150),
    IN p_role_id INT,
    IN p_section_id INT,
    IN p_full_name VARCHAR(150)
)
BEGIN
    DECLARE v_user_id INT;
    DECLARE v_user_role_id INT;
    DECLARE v_employee_id INT;
    DECLARE v_username_exists INT DEFAULT 0;
    DECLARE v_email_exists INT DEFAULT 0;
    DECLARE v_employee_code_exists INT DEFAULT 1;
    DECLARE v_employee_code VARCHAR(50);
    DECLARE v_company_id INT DEFAULT NULL;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    START TRANSACTION;

    SELECT company_id INTO v_company_id FROM sections WHERE id = p_section_id LIMIT 1;
    SELECT COUNT(*) INTO v_username_exists FROM users WHERE username = p_username AND company_id <=> v_company_id;
    IF v_username_exists > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Username already exists';
    END IF;

    IF p_email IS NOT NULL AND p_email != '' THEN
        SELECT COUNT(*) INTO v_email_exists FROM users WHERE email = p_email AND email IS NOT NULL AND email != '' AND company_id <=> v_company_id;
        IF v_email_exists > 0 THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Email already exists';
        END IF;
    END IF;

    WHILE v_employee_code_exists > 0 DO
        SET v_employee_code = CONCAT('LDR-', LPAD(FLOOR(RAND() * 9999), 4, '0'));
        SELECT COUNT(*) INTO v_employee_code_exists FROM employees WHERE employee_code = v_employee_code;
    END WHILE;

    INSERT INTO users (company_id, username, password_hash, email)
    VALUES (v_company_id, p_username, p_password_hash, p_email);
    SET v_user_id = LAST_INSERT_ID();

    INSERT INTO user_roles (user_id, role_id, section_id)
    VALUES (v_user_id, p_role_id, p_section_id);
    SET v_user_role_id = LAST_INSERT_ID();

    INSERT INTO employees (user_role_id, employee_code, full_name, email, is_senior, seniority_level)
    VALUES (v_user_role_id, v_employee_code, p_full_name, p_email, 0, 0);
    SET v_employee_id = LAST_INSERT_ID();

    COMMIT;
    SELECT v_employee_id AS employee_id, v_user_id AS user_id;
END $$

DROP PROCEDURE IF EXISTS sp_get_employees_by_section $$
CREATE PROCEDURE sp_get_employees_by_section(IN p_section_id INT)
BEGIN
    SELECT e.id,
           e.employee_code,
           e.full_name,
           e.email,
           e.seniority_level,
           e.is_senior,
           e.is_active,
           r.role_name,
           u.username
    FROM employees e
    INNER JOIN user_roles ur ON ur.id = e.user_role_id
    INNER JOIN roles r ON r.id = ur.role_id
    INNER JOIN users u ON u.id = ur.user_id
    WHERE ur.section_id = p_section_id
      AND e.is_active = 1
      AND r.role_name IN ('Employee', 'Senior')
    ORDER BY e.seniority_level DESC, e.full_name;
END $$

DROP PROCEDURE IF EXISTS sp_get_available_employees $$
CREATE PROCEDURE sp_get_available_employees(IN p_section_id INT, IN p_date DATE)
BEGIN
    SELECT e.id,
           e.employee_code,
           e.full_name,
           e.email,
           e.seniority_level,
           e.is_senior
    FROM employees e
    INNER JOIN user_roles ur ON ur.id = e.user_role_id
    INNER JOIN roles r ON r.id = ur.role_id
    WHERE ur.section_id = p_section_id
      AND e.is_active = 1
      AND r.role_name IN ('Employee', 'Senior')
      AND NOT EXISTS (
          SELECT 1
          FROM schedule_assignments sa
          INNER JOIN schedule_shifts ss ON ss.id = sa.schedule_shift_id
          INNER JOIN schedules s ON s.id = ss.schedule_id
          WHERE sa.employee_id = e.id
            AND ss.shift_date = p_date
            AND s.section_id = p_section_id
      )
    ORDER BY e.seniority_level DESC, e.full_name;
END $$

DROP PROCEDURE IF EXISTS sp_update_employee $$
CREATE PROCEDURE sp_update_employee(
    IN p_employee_id INT,
    IN p_section_id INT,
    IN p_full_name VARCHAR(150),
    IN p_email VARCHAR(150),
    IN p_role_id INT,
    IN p_seniority_level INT,
    IN p_is_senior TINYINT
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    START TRANSACTION;

    UPDATE employees e
    INNER JOIN user_roles ur ON ur.id = e.user_role_id
    INNER JOIN users u ON u.id = ur.user_id
    SET e.full_name = p_full_name,
        e.email = NULLIF(p_email, ''),
        e.seniority_level = p_seniority_level,
        e.is_senior = p_is_senior,
        ur.role_id = p_role_id,
        u.email = NULLIF(p_email, '')
    WHERE e.id = p_employee_id AND ur.section_id = p_section_id;

    SELECT ROW_COUNT() AS affected_rows;
    COMMIT;
END $$

DROP PROCEDURE IF EXISTS sp_get_admin_directory $$
CREATE PROCEDURE sp_get_admin_directory()
BEGIN
    SELECT e.id,
           e.employee_code,
           e.full_name,
           e.email,
           r.role_name,
           u.username,
           s.section_name
    FROM employees e
    INNER JOIN user_roles ur ON ur.id = e.user_role_id
    INNER JOIN roles r ON r.id = ur.role_id
    INNER JOIN users u ON u.id = ur.user_id
    INNER JOIN sections s ON s.id = ur.section_id
    WHERE e.is_active = 1
      AND r.role_name IN ('Team Leader', 'Supervisor')
    ORDER BY s.section_name, r.role_name, e.full_name;
END $$

DROP PROCEDURE IF EXISTS sp_performance_report $$
CREATE PROCEDURE sp_performance_report(IN p_start_date DATE, IN p_end_date DATE, IN p_section_id INT, IN p_employee_id INT)
BEGIN
    SELECT e.id AS employee_id,
           e.full_name AS employee_name,
           e.employee_code,
           COUNT(DISTINCT sa.schedule_shift_id) AS days_worked,
           COALESCE(SUM(GREATEST(TIMESTAMPDIFF(MINUTE, eb.break_start, eb.break_end) - 30, 0)), 0) AS total_delay_minutes,
           COALESCE(AVG(GREATEST(TIMESTAMPDIFF(MINUTE, eb.break_start, eb.break_end) - 30, 0)), 0) AS average_delay_minutes
    FROM employees e
    INNER JOIN user_roles ur ON ur.id = e.user_role_id
    INNER JOIN sections s ON s.id = ur.section_id
    LEFT JOIN schedule_assignments sa ON sa.employee_id = e.id
    LEFT JOIN schedule_shifts ss ON ss.id = sa.schedule_shift_id
    LEFT JOIN employee_breaks eb ON eb.employee_id = e.id
        AND eb.worked_date BETWEEN p_start_date AND p_end_date
        AND eb.break_end IS NOT NULL
    WHERE s.id = p_section_id
      AND e.is_active = 1
      AND (p_employee_id IS NULL OR e.id = p_employee_id)
      AND (ss.shift_date IS NULL OR ss.shift_date BETWEEN p_start_date AND p_end_date)
    GROUP BY e.id, e.full_name, e.employee_code
    ORDER BY total_delay_minutes ASC, average_delay_minutes ASC, e.full_name;
END $$

DROP PROCEDURE IF EXISTS sp_director_dashboard $$
CREATE PROCEDURE sp_director_dashboard(IN p_section_id INT, IN p_week_id INT)
BEGIN
    SELECT 'Total Employees' AS metric_label, COUNT(DISTINCT e.id) AS metric_value, 'Active employees in section' AS description
    FROM employees e
    INNER JOIN user_roles ur ON ur.id = e.user_role_id
    WHERE ur.section_id = p_section_id AND e.is_active = 1
    UNION ALL
    SELECT 'Approved Requests', COUNT(*), 'Requests approved for the week'
    FROM shift_requests sr
    INNER JOIN employees e ON e.id = sr.employee_id
    INNER JOIN user_roles ur ON ur.id = e.user_role_id
    WHERE sr.week_id = p_week_id AND ur.section_id = p_section_id AND sr.status = 'APPROVED'
    UNION ALL
    SELECT 'Pending Requests', COUNT(*), 'Requests pending approval'
    FROM shift_requests sr
    INNER JOIN employees e ON e.id = sr.employee_id
    INNER JOIN user_roles ur ON ur.id = e.user_role_id
    WHERE sr.week_id = p_week_id AND ur.section_id = p_section_id AND sr.status = 'PENDING'
    UNION ALL
    SELECT 'Total Breaks Today', COUNT(*), 'Breaks logged today'
    FROM employee_breaks eb
    INNER JOIN employees e ON e.id = eb.employee_id
    INNER JOIN user_roles ur ON ur.id = e.user_role_id
    WHERE ur.section_id = p_section_id AND eb.worked_date = CURDATE();
END $$

DROP PROCEDURE IF EXISTS sp_get_all_sections $$
CREATE PROCEDURE sp_get_all_sections()
BEGIN
    SELECT id, section_name FROM sections ORDER BY id;
END $$

DELIMITER ;

-- ===============================
-- Test Data: Main Company + Connected Records
-- ===============================

INSERT INTO companies (
    company_name,
    company_slug,
    admin_email,
    admin_password_hash,
    timezone,
    country,
    company_size,
    status,
    email_verified_at,
    payment_status,
    payment_completed_at,
    onboarding_completed_at
) VALUES (
    'Acme Corporation',
    'acme-corporation',
    'admin@acme.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'America/New_York',
    'United States',
    '50-100',
    'ACTIVE',
    NOW(),
    'COMPLETED',
    NOW(),
    NOW()
);

SET @company_id = LAST_INSERT_ID();

INSERT INTO company_onboarding (company_id, step, step_data, completed, completed_at) VALUES
    (@company_id, 'company_profile', JSON_OBJECT('industry', 'Healthcare', 'location', 'New York, NY'), 1, NOW()),
    (@company_id, 'team_setup', JSON_OBJECT('teams', 6, 'employees', 30), 1, NOW()),
    (@company_id, 'schedule_preferences', JSON_OBJECT('week_start', 'Monday', 'timezone', 'America/New_York'), 1, NOW()),
    (@company_id, 'policy_acknowledged', JSON_OBJECT('policy_version', '2024.1'), 1, NOW()),
    (@company_id, 'launch_ready', JSON_OBJECT('status', 'ready'), 1, NOW());

INSERT INTO sections (company_id, section_name) VALUES
    (@company_id, 'Operations'),
    (@company_id, 'Customer Service'),
    (@company_id, 'Warehouse'),
    (@company_id, 'Security'),
    (@company_id, 'Maintenance'),
    (@company_id, 'Administration');

SET @section_ops = (SELECT id FROM sections WHERE company_id = @company_id AND section_name = 'Operations');
SET @section_customer = (SELECT id FROM sections WHERE company_id = @company_id AND section_name = 'Customer Service');
SET @section_warehouse = (SELECT id FROM sections WHERE company_id = @company_id AND section_name = 'Warehouse');
SET @section_security = (SELECT id FROM sections WHERE company_id = @company_id AND section_name = 'Security');
SET @section_maintenance = (SELECT id FROM sections WHERE company_id = @company_id AND section_name = 'Maintenance');
SET @section_admin = (SELECT id FROM sections WHERE company_id = @company_id AND section_name = 'Administration');

SET @role_director = (SELECT id FROM roles WHERE role_name = 'Director');
SET @role_team_leader = (SELECT id FROM roles WHERE role_name = 'Team Leader');
SET @role_supervisor = (SELECT id FROM roles WHERE role_name = 'Supervisor');
SET @role_senior = (SELECT id FROM roles WHERE role_name = 'Senior');
SET @role_employee = (SELECT id FROM roles WHERE role_name = 'Employee');

SET @pattern_5x2 = (SELECT id FROM schedule_patterns WHERE name = '5x2');
SET @pattern_6x1 = (SELECT id FROM schedule_patterns WHERE name = '6x1');
SET @pattern_4x3 = (SELECT id FROM schedule_patterns WHERE name = '4x3');
SET @pattern_rotating = (SELECT id FROM schedule_patterns WHERE name = 'Rotating');

SET @shift_am = (SELECT id FROM shift_types WHERE code = 'AM');
SET @shift_mid = (SELECT id FROM shift_types WHERE code = 'MID');
SET @shift_pm = (SELECT id FROM shift_types WHERE code = 'PM');
SET @shift_midnight = (SELECT id FROM shift_types WHERE code = 'MIDNIGHT');

SET @def_am = (SELECT id FROM shift_definitions WHERE shift_name = 'AM Shift');
SET @def_mid = (SELECT id FROM shift_definitions WHERE shift_name = 'Mid Shift');
SET @def_pm = (SELECT id FROM shift_definitions WHERE shift_name = 'PM Shift');

INSERT INTO users (company_id, username, password_hash, email, role, onboarding_completed, is_active) VALUES
    (@company_id, 'director1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'director1@acme.com', 'Director', 1, 1),
    (@company_id, 'lead1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'lead1@acme.com', 'Team Leader', 1, 1),
    (@company_id, 'lead2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'lead2@acme.com', 'Team Leader', 1, 1),
    (@company_id, 'lead3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'lead3@acme.com', 'Team Leader', 1, 1),
    (@company_id, 'supervisor1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'supervisor1@acme.com', 'Supervisor', 1, 1),
    (@company_id, 'supervisor2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'supervisor2@acme.com', 'Supervisor', 1, 1),
    (@company_id, 'supervisor3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'supervisor3@acme.com', 'Supervisor', 1, 1),
    (@company_id, 'senior1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'senior1@acme.com', 'Senior', 1, 1),
    (@company_id, 'senior2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'senior2@acme.com', 'Senior', 1, 1),
    (@company_id, 'senior3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'senior3@acme.com', 'Senior', 1, 1),
    (@company_id, 'employee1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employee1@acme.com', 'Employee', 1, 1),
    (@company_id, 'employee2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employee2@acme.com', 'Employee', 1, 1),
    (@company_id, 'employee3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employee3@acme.com', 'Employee', 1, 1),
    (@company_id, 'employee4', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employee4@acme.com', 'Employee', 1, 1),
    (@company_id, 'employee5', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employee5@acme.com', 'Employee', 1, 1),
    (@company_id, 'employee6', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employee6@acme.com', 'Employee', 1, 1),
    (@company_id, 'employee7', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employee7@acme.com', 'Employee', 1, 1),
    (@company_id, 'employee8', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employee8@acme.com', 'Employee', 1, 1),
    (@company_id, 'employee9', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employee9@acme.com', 'Employee', 1, 1),
    (@company_id, 'employee10', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employee10@acme.com', 'Employee', 1, 1),
    (@company_id, 'employee11', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employee11@acme.com', 'Employee', 1, 1),
    (@company_id, 'employee12', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employee12@acme.com', 'Employee', 1, 1),
    (@company_id, 'employee13', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employee13@acme.com', 'Employee', 1, 1),
    (@company_id, 'employee14', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employee14@acme.com', 'Employee', 1, 1),
    (@company_id, 'employee15', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employee15@acme.com', 'Employee', 1, 1),
    (@company_id, 'employee16', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employee16@acme.com', 'Employee', 1, 1),
    (@company_id, 'employee17', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employee17@acme.com', 'Employee', 1, 1),
    (@company_id, 'employee18', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employee18@acme.com', 'Employee', 1, 1),
    (@company_id, 'employee19', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employee19@acme.com', 'Employee', 1, 1),
    (@company_id, 'employee20', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employee20@acme.com', 'Employee', 1, 1);

INSERT INTO user_roles (user_id, role_id, section_id) VALUES
    ((SELECT id FROM users WHERE username = 'director1'), @role_director, @section_admin),
    ((SELECT id FROM users WHERE username = 'lead1'), @role_team_leader, @section_ops),
    ((SELECT id FROM users WHERE username = 'lead2'), @role_team_leader, @section_customer),
    ((SELECT id FROM users WHERE username = 'lead3'), @role_team_leader, @section_warehouse),
    ((SELECT id FROM users WHERE username = 'supervisor1'), @role_supervisor, @section_ops),
    ((SELECT id FROM users WHERE username = 'supervisor2'), @role_supervisor, @section_customer),
    ((SELECT id FROM users WHERE username = 'supervisor3'), @role_supervisor, @section_warehouse),
    ((SELECT id FROM users WHERE username = 'senior1'), @role_senior, @section_security),
    ((SELECT id FROM users WHERE username = 'senior2'), @role_senior, @section_maintenance),
    ((SELECT id FROM users WHERE username = 'senior3'), @role_senior, @section_ops),
    ((SELECT id FROM users WHERE username = 'employee1'), @role_employee, @section_ops),
    ((SELECT id FROM users WHERE username = 'employee2'), @role_employee, @section_ops),
    ((SELECT id FROM users WHERE username = 'employee3'), @role_employee, @section_ops),
    ((SELECT id FROM users WHERE username = 'employee4'), @role_employee, @section_customer),
    ((SELECT id FROM users WHERE username = 'employee5'), @role_employee, @section_customer),
    ((SELECT id FROM users WHERE username = 'employee6'), @role_employee, @section_customer),
    ((SELECT id FROM users WHERE username = 'employee7'), @role_employee, @section_warehouse),
    ((SELECT id FROM users WHERE username = 'employee8'), @role_employee, @section_warehouse),
    ((SELECT id FROM users WHERE username = 'employee9'), @role_employee, @section_warehouse),
    ((SELECT id FROM users WHERE username = 'employee10'), @role_employee, @section_security),
    ((SELECT id FROM users WHERE username = 'employee11'), @role_employee, @section_security),
    ((SELECT id FROM users WHERE username = 'employee12'), @role_employee, @section_security),
    ((SELECT id FROM users WHERE username = 'employee13'), @role_employee, @section_maintenance),
    ((SELECT id FROM users WHERE username = 'employee14'), @role_employee, @section_maintenance),
    ((SELECT id FROM users WHERE username = 'employee15'), @role_employee, @section_maintenance),
    ((SELECT id FROM users WHERE username = 'employee16'), @role_employee, @section_ops),
    ((SELECT id FROM users WHERE username = 'employee17'), @role_employee, @section_customer),
    ((SELECT id FROM users WHERE username = 'employee18'), @role_employee, @section_warehouse),
    ((SELECT id FROM users WHERE username = 'employee19'), @role_employee, @section_security),
    ((SELECT id FROM users WHERE username = 'employee20'), @role_employee, @section_maintenance);

INSERT INTO employees (user_role_id, employee_code, full_name, email, is_senior, seniority_level, is_active) VALUES
    ((SELECT ur.id FROM user_roles ur INNER JOIN users u ON u.id = ur.user_id WHERE u.username = 'director1'), 'EMP0001', 'Avery Morgan', 'director1@acme.com', 0, 6, 1),
    ((SELECT ur.id FROM user_roles ur INNER JOIN users u ON u.id = ur.user_id WHERE u.username = 'lead1'), 'EMP0002', 'Jordan Lee', 'lead1@acme.com', 0, 5, 1),
    ((SELECT ur.id FROM user_roles ur INNER JOIN users u ON u.id = ur.user_id WHERE u.username = 'lead2'), 'EMP0003', 'Casey Patel', 'lead2@acme.com', 0, 5, 1),
    ((SELECT ur.id FROM user_roles ur INNER JOIN users u ON u.id = ur.user_id WHERE u.username = 'lead3'), 'EMP0004', 'Riley Chen', 'lead3@acme.com', 0, 5, 1),
    ((SELECT ur.id FROM user_roles ur INNER JOIN users u ON u.id = ur.user_id WHERE u.username = 'supervisor1'), 'EMP0005', 'Morgan Diaz', 'supervisor1@acme.com', 0, 4, 1),
    ((SELECT ur.id FROM user_roles ur INNER JOIN users u ON u.id = ur.user_id WHERE u.username = 'supervisor2'), 'EMP0006', 'Jamie Brooks', 'supervisor2@acme.com', 0, 4, 1),
    ((SELECT ur.id FROM user_roles ur INNER JOIN users u ON u.id = ur.user_id WHERE u.username = 'supervisor3'), 'EMP0007', 'Taylor Nguyen', 'supervisor3@acme.com', 0, 4, 1),
    ((SELECT ur.id FROM user_roles ur INNER JOIN users u ON u.id = ur.user_id WHERE u.username = 'senior1'), 'EMP0008', 'Samira Johnson', 'senior1@acme.com', 1, 3, 1),
    ((SELECT ur.id FROM user_roles ur INNER JOIN users u ON u.id = ur.user_id WHERE u.username = 'senior2'), 'EMP0009', 'Logan Rivera', 'senior2@acme.com', 1, 3, 1),
    ((SELECT ur.id FROM user_roles ur INNER JOIN users u ON u.id = ur.user_id WHERE u.username = 'senior3'), 'EMP0010', 'Peyton Kim', 'senior3@acme.com', 1, 3, 1),
    ((SELECT ur.id FROM user_roles ur INNER JOIN users u ON u.id = ur.user_id WHERE u.username = 'employee1'), 'EMP0011', 'Harper Scott', 'employee1@acme.com', 0, 2, 1),
    ((SELECT ur.id FROM user_roles ur INNER JOIN users u ON u.id = ur.user_id WHERE u.username = 'employee2'), 'EMP0012', 'Rowan Kelly', 'employee2@acme.com', 0, 2, 1),
    ((SELECT ur.id FROM user_roles ur INNER JOIN users u ON u.id = ur.user_id WHERE u.username = 'employee3'), 'EMP0013', 'Kai Phillips', 'employee3@acme.com', 0, 2, 1),
    ((SELECT ur.id FROM user_roles ur INNER JOIN users u ON u.id = ur.user_id WHERE u.username = 'employee4'), 'EMP0014', 'Emerson Reed', 'employee4@acme.com', 0, 2, 1),
    ((SELECT ur.id FROM user_roles ur INNER JOIN users u ON u.id = ur.user_id WHERE u.username = 'employee5'), 'EMP0015', 'Dakota Perry', 'employee5@acme.com', 0, 2, 1),
    ((SELECT ur.id FROM user_roles ur INNER JOIN users u ON u.id = ur.user_id WHERE u.username = 'employee6'), 'EMP0016', 'Reese Ward', 'employee6@acme.com', 0, 2, 1),
    ((SELECT ur.id FROM user_roles ur INNER JOIN users u ON u.id = ur.user_id WHERE u.username = 'employee7'), 'EMP0017', 'Quinn Foster', 'employee7@acme.com', 0, 2, 1),
    ((SELECT ur.id FROM user_roles ur INNER JOIN users u ON u.id = ur.user_id WHERE u.username = 'employee8'), 'EMP0018', 'Cameron Price', 'employee8@acme.com', 0, 2, 1),
    ((SELECT ur.id FROM user_roles ur INNER JOIN users u ON u.id = ur.user_id WHERE u.username = 'employee9'), 'EMP0019', 'Avery Collins', 'employee9@acme.com', 0, 2, 1),
    ((SELECT ur.id FROM user_roles ur INNER JOIN users u ON u.id = ur.user_id WHERE u.username = 'employee10'), 'EMP0020', 'Hayden Murphy', 'employee10@acme.com', 0, 1, 1),
    ((SELECT ur.id FROM user_roles ur INNER JOIN users u ON u.id = ur.user_id WHERE u.username = 'employee11'), 'EMP0021', 'Skyler Bennett', 'employee11@acme.com', 0, 1, 1),
    ((SELECT ur.id FROM user_roles ur INNER JOIN users u ON u.id = ur.user_id WHERE u.username = 'employee12'), 'EMP0022', 'Elliot Hughes', 'employee12@acme.com', 0, 1, 1),
    ((SELECT ur.id FROM user_roles ur INNER JOIN users u ON u.id = ur.user_id WHERE u.username = 'employee13'), 'EMP0023', 'Finley Woods', 'employee13@acme.com', 0, 1, 1),
    ((SELECT ur.id FROM user_roles ur INNER JOIN users u ON u.id = ur.user_id WHERE u.username = 'employee14'), 'EMP0024', 'Marley Hayes', 'employee14@acme.com', 0, 1, 1),
    ((SELECT ur.id FROM user_roles ur INNER JOIN users u ON u.id = ur.user_id WHERE u.username = 'employee15'), 'EMP0025', 'Rory James', 'employee15@acme.com', 0, 1, 1),
    ((SELECT ur.id FROM user_roles ur INNER JOIN users u ON u.id = ur.user_id WHERE u.username = 'employee16'), 'EMP0026', 'Parker Steele', 'employee16@acme.com', 0, 1, 1),
    ((SELECT ur.id FROM user_roles ur INNER JOIN users u ON u.id = ur.user_id WHERE u.username = 'employee17'), 'EMP0027', 'Sage Turner', 'employee17@acme.com', 0, 1, 1),
    ((SELECT ur.id FROM user_roles ur INNER JOIN users u ON u.id = ur.user_id WHERE u.username = 'employee18'), 'EMP0028', 'Justice Patel', 'employee18@acme.com', 0, 1, 1),
    ((SELECT ur.id FROM user_roles ur INNER JOIN users u ON u.id = ur.user_id WHERE u.username = 'employee19'), 'EMP0029', 'Phoenix Alvarez', 'employee19@acme.com', 0, 1, 1),
    ((SELECT ur.id FROM user_roles ur INNER JOIN users u ON u.id = ur.user_id WHERE u.username = 'employee20'), 'EMP0030', 'River Stone', 'employee20@acme.com', 0, 1, 1);

SET @week_start = DATE_SUB(CURDATE(), INTERVAL (DAYOFWEEK(CURDATE()) - 2) DAY);

INSERT INTO weeks (company_id, week_start_date, week_end_date)
SELECT
    @company_id,
    DATE_ADD(@week_start, INTERVAL seq * 7 DAY),
    DATE_ADD(DATE_ADD(@week_start, INTERVAL seq * 7 DAY), INTERVAL 6 DAY)
FROM (
    SELECT 0 AS seq UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
    UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9
    UNION ALL SELECT 10 UNION ALL SELECT 11 UNION ALL SELECT 12 UNION ALL SELECT 13 UNION ALL SELECT 14
    UNION ALL SELECT 15 UNION ALL SELECT 16 UNION ALL SELECT 17 UNION ALL SELECT 18 UNION ALL SELECT 19
    UNION ALL SELECT 20 UNION ALL SELECT 21 UNION ALL SELECT 22 UNION ALL SELECT 23 UNION ALL SELECT 24
    UNION ALL SELECT 25 UNION ALL SELECT 26 UNION ALL SELECT 27 UNION ALL SELECT 28 UNION ALL SELECT 29
) AS seq_table;

SET @week_0 = (SELECT id FROM weeks WHERE company_id = @company_id AND week_start_date = @week_start);
SET @week_1 = (SELECT id FROM weeks WHERE company_id = @company_id AND week_start_date = DATE_ADD(@week_start, INTERVAL 7 DAY));
SET @week_2 = (SELECT id FROM weeks WHERE company_id = @company_id AND week_start_date = DATE_ADD(@week_start, INTERVAL 14 DAY));
SET @week_3 = (SELECT id FROM weeks WHERE company_id = @company_id AND week_start_date = DATE_ADD(@week_start, INTERVAL 21 DAY));
SET @week_4 = (SELECT id FROM weeks WHERE company_id = @company_id AND week_start_date = DATE_ADD(@week_start, INTERVAL 28 DAY));
SET @week_5 = (SELECT id FROM weeks WHERE company_id = @company_id AND week_start_date = DATE_ADD(@week_start, INTERVAL 35 DAY));

INSERT INTO shift_requirements (company_id, week_id, section_id, shift_date, shift_type_id, required_count) VALUES
    (@company_id, @week_0, @section_ops, DATE_ADD(@week_start, INTERVAL 0 DAY), @shift_am, 4),
    (@company_id, @week_0, @section_customer, DATE_ADD(@week_start, INTERVAL 1 DAY), @shift_mid, 3),
    (@company_id, @week_0, @section_warehouse, DATE_ADD(@week_start, INTERVAL 2 DAY), @shift_pm, 3),
    (@company_id, @week_0, @section_security, DATE_ADD(@week_start, INTERVAL 3 DAY), @shift_midnight, 2),
    (@company_id, @week_0, @section_maintenance, DATE_ADD(@week_start, INTERVAL 4 DAY), @shift_am, 2),
    (@company_id, @week_1, @section_ops, DATE_ADD(@week_start, INTERVAL 7 DAY), @shift_mid, 4),
    (@company_id, @week_1, @section_customer, DATE_ADD(@week_start, INTERVAL 8 DAY), @shift_pm, 3),
    (@company_id, @week_1, @section_warehouse, DATE_ADD(@week_start, INTERVAL 9 DAY), @shift_am, 3),
    (@company_id, @week_1, @section_security, DATE_ADD(@week_start, INTERVAL 10 DAY), @shift_midnight, 2),
    (@company_id, @week_1, @section_maintenance, DATE_ADD(@week_start, INTERVAL 11 DAY), @shift_mid, 2),
    (@company_id, @week_2, @section_ops, DATE_ADD(@week_start, INTERVAL 14 DAY), @shift_pm, 4),
    (@company_id, @week_2, @section_customer, DATE_ADD(@week_start, INTERVAL 15 DAY), @shift_am, 3),
    (@company_id, @week_2, @section_warehouse, DATE_ADD(@week_start, INTERVAL 16 DAY), @shift_mid, 3),
    (@company_id, @week_2, @section_security, DATE_ADD(@week_start, INTERVAL 17 DAY), @shift_midnight, 2),
    (@company_id, @week_2, @section_maintenance, DATE_ADD(@week_start, INTERVAL 18 DAY), @shift_pm, 2),
    (@company_id, @week_3, @section_ops, DATE_ADD(@week_start, INTERVAL 21 DAY), @shift_am, 4),
    (@company_id, @week_3, @section_customer, DATE_ADD(@week_start, INTERVAL 22 DAY), @shift_mid, 3),
    (@company_id, @week_3, @section_warehouse, DATE_ADD(@week_start, INTERVAL 23 DAY), @shift_pm, 3),
    (@company_id, @week_3, @section_security, DATE_ADD(@week_start, INTERVAL 24 DAY), @shift_midnight, 2),
    (@company_id, @week_3, @section_maintenance, DATE_ADD(@week_start, INTERVAL 25 DAY), @shift_am, 2),
    (@company_id, @week_4, @section_ops, DATE_ADD(@week_start, INTERVAL 28 DAY), @shift_mid, 4),
    (@company_id, @week_4, @section_customer, DATE_ADD(@week_start, INTERVAL 29 DAY), @shift_pm, 3),
    (@company_id, @week_4, @section_warehouse, DATE_ADD(@week_start, INTERVAL 30 DAY), @shift_am, 3),
    (@company_id, @week_4, @section_security, DATE_ADD(@week_start, INTERVAL 31 DAY), @shift_midnight, 2),
    (@company_id, @week_4, @section_maintenance, DATE_ADD(@week_start, INTERVAL 32 DAY), @shift_mid, 2),
    (@company_id, @week_5, @section_ops, DATE_ADD(@week_start, INTERVAL 35 DAY), @shift_pm, 4),
    (@company_id, @week_5, @section_customer, DATE_ADD(@week_start, INTERVAL 36 DAY), @shift_am, 3),
    (@company_id, @week_5, @section_warehouse, DATE_ADD(@week_start, INTERVAL 37 DAY), @shift_mid, 3),
    (@company_id, @week_5, @section_security, DATE_ADD(@week_start, INTERVAL 38 DAY), @shift_midnight, 2),
    (@company_id, @week_5, @section_maintenance, DATE_ADD(@week_start, INTERVAL 39 DAY), @shift_pm, 2);

SET @director_employee_id = (
    SELECT e.id
    FROM employees e
    INNER JOIN user_roles ur ON ur.id = e.user_role_id
    INNER JOIN users u ON u.id = ur.user_id
    WHERE u.username = 'director1'
    LIMIT 1
);

INSERT INTO schedules (company_id, week_id, section_id, generated_by_admin_id, status, notes)
SELECT
    @company_id,
    w.id,
    s.id,
    @director_employee_id,
    CASE WHEN w.id IN (@week_0, @week_1, @week_2) THEN 'FINAL' ELSE 'DRAFT' END,
    CONCAT('Schedule for ', s.section_name, ' - Week of ', w.week_start_date)
FROM weeks w
INNER JOIN sections s ON s.company_id = @company_id
WHERE w.id IN (@week_0, @week_1, @week_2, @week_3, @week_4, @week_5)
  AND s.id IN (@section_ops, @section_customer, @section_warehouse, @section_security, @section_maintenance)
ORDER BY w.week_start_date, s.section_name;

INSERT INTO schedule_shifts (schedule_id, shift_date, shift_definition_id, required_count)
SELECT
    s.id,
    DATE_ADD(w.week_start_date, INTERVAL shift_map.day_offset DAY),
    shift_map.shift_definition_id,
    shift_map.required_count
FROM schedules s
INNER JOIN weeks w ON w.id = s.week_id
INNER JOIN (
    SELECT 0 AS day_offset, @def_am AS shift_definition_id, 3 AS required_count
    UNION ALL SELECT 1, @def_mid, 3
    UNION ALL SELECT 2, @def_pm, 2
) AS shift_map
WHERE s.id IN (SELECT id FROM schedules ORDER BY id LIMIT 10)
ORDER BY s.id, shift_map.day_offset;

SET @emp_ops = (
    SELECT e.id FROM employees e
    INNER JOIN user_roles ur ON ur.id = e.user_role_id
    WHERE ur.section_id = @section_ops
    ORDER BY e.seniority_level DESC, e.id
    LIMIT 1
);
SET @emp_customer = (
    SELECT e.id FROM employees e
    INNER JOIN user_roles ur ON ur.id = e.user_role_id
    WHERE ur.section_id = @section_customer
    ORDER BY e.seniority_level DESC, e.id
    LIMIT 1
);
SET @emp_warehouse = (
    SELECT e.id FROM employees e
    INNER JOIN user_roles ur ON ur.id = e.user_role_id
    WHERE ur.section_id = @section_warehouse
    ORDER BY e.seniority_level DESC, e.id
    LIMIT 1
);
SET @emp_security = (
    SELECT e.id FROM employees e
    INNER JOIN user_roles ur ON ur.id = e.user_role_id
    WHERE ur.section_id = @section_security
    ORDER BY e.seniority_level DESC, e.id
    LIMIT 1
);
SET @emp_maintenance = (
    SELECT e.id FROM employees e
    INNER JOIN user_roles ur ON ur.id = e.user_role_id
    WHERE ur.section_id = @section_maintenance
    ORDER BY e.seniority_level DESC, e.id
    LIMIT 1
);

INSERT INTO schedule_assignments (schedule_shift_id, employee_id, assignment_source, is_senior, notes)
SELECT
    ss.id,
    CASE s.section_id
        WHEN @section_ops THEN @emp_ops
        WHEN @section_customer THEN @emp_customer
        WHEN @section_warehouse THEN @emp_warehouse
        WHEN @section_security THEN @emp_security
        WHEN @section_maintenance THEN @emp_maintenance
    END AS employee_id,
    'AUTO_ASSIGNED',
    e.is_senior,
    CONCAT('Seed assignment for shift ', ss.id)
FROM schedule_shifts ss
INNER JOIN schedules s ON s.id = ss.schedule_id
INNER JOIN employees e ON e.id = CASE s.section_id
    WHEN @section_ops THEN @emp_ops
    WHEN @section_customer THEN @emp_customer
    WHEN @section_warehouse THEN @emp_warehouse
    WHEN @section_security THEN @emp_security
    WHEN @section_maintenance THEN @emp_maintenance
END
ORDER BY ss.id
LIMIT 30;

INSERT INTO shift_requests (
    employee_id,
    week_id,
    request_date,
    shift_definition_id,
    is_day_off,
    schedule_pattern_id,
    reason,
    importance_level,
    status,
    submitted_at
)
SELECT
    e.id,
    CASE MOD(e.id, 3)
        WHEN 0 THEN @week_0
        WHEN 1 THEN @week_1
        ELSE @week_2
    END,
    DATE_ADD(
        (SELECT week_start_date FROM weeks WHERE id = CASE MOD(e.id, 3)
            WHEN 0 THEN @week_0
            WHEN 1 THEN @week_1
            ELSE @week_2
        END),
        INTERVAL (1 + MOD(e.id, 4)) DAY
    ),
    CASE WHEN MOD(e.id, 5) = 0 THEN NULL ELSE @def_am END,
    CASE WHEN MOD(e.id, 7) = 0 THEN 1 ELSE 0 END,
    CASE MOD(e.id, 4)
        WHEN 0 THEN @pattern_5x2
        WHEN 1 THEN @pattern_6x1
        WHEN 2 THEN @pattern_4x3
        ELSE @pattern_rotating
    END,
    CASE MOD(e.id, 4)
        WHEN 0 THEN 'Medical appointment'
        WHEN 1 THEN 'Family obligation'
        WHEN 2 THEN 'Training request'
        ELSE 'Personal preference'
    END,
    CASE MOD(e.id, 3)
        WHEN 0 THEN 'HIGH'
        WHEN 1 THEN 'MEDIUM'
        ELSE 'LOW'
    END,
    CASE MOD(e.id, 3)
        WHEN 0 THEN 'APPROVED'
        WHEN 1 THEN 'PENDING'
        ELSE 'DECLINED'
    END,
    DATE_SUB(NOW(), INTERVAL MOD(e.id, 10) DAY)
FROM employees e
ORDER BY e.id
LIMIT 30;

INSERT INTO employee_breaks (employee_id, schedule_shift_id, worked_date, break_start, break_end, is_active)
SELECT
    t.employee_id,
    t.schedule_shift_id,
    t.shift_date,
    DATE_ADD(CONCAT(t.shift_date, ' ', t.start_time), INTERVAL 2 HOUR),
    DATE_ADD(CONCAT(t.shift_date, ' ', t.start_time), INTERVAL 2 HOUR + 30 MINUTE),
    0
FROM (
    SELECT
        sa.employee_id,
        MIN(ss.id) AS schedule_shift_id,
        ss.shift_date,
        MIN(sd.start_time) AS start_time
    FROM schedule_assignments sa
    INNER JOIN schedule_shifts ss ON ss.id = sa.schedule_shift_id
    INNER JOIN shift_definitions sd ON sd.id = ss.shift_definition_id
    GROUP BY sa.employee_id, ss.shift_date
    ORDER BY sa.employee_id, ss.shift_date
    LIMIT 30
) AS t;

INSERT INTO notifications (company_id, user_id, type, title, body, is_read, created_at)
SELECT
    @company_id,
    u.id,
    CASE MOD(u.id, 3)
        WHEN 0 THEN 'SHIFT_REMINDER'
        WHEN 1 THEN 'SCHEDULE_PUBLISHED'
        ELSE 'REQUEST_STATUS'
    END,
    CASE MOD(u.id, 3)
        WHEN 0 THEN 'Shift Reminder'
        WHEN 1 THEN 'Schedule Published'
        ELSE 'Request Status Updated'
    END,
    CASE MOD(u.id, 3)
        WHEN 0 THEN 'Your shift starts in 2 hours.'
        WHEN 1 THEN 'A new schedule has been published for your section.'
        ELSE 'Your shift request status has changed.'
    END,
    CASE WHEN MOD(u.id, 2) = 0 THEN 1 ELSE 0 END,
    DATE_SUB(NOW(), INTERVAL MOD(u.id, 7) DAY)
FROM users u
WHERE u.company_id = @company_id
ORDER BY u.id
LIMIT 30;

SELECT
    'Database setup complete!' AS message,
    @company_id AS company_id,
    'acme-corporation' AS company_slug,
    'admin@acme.com' AS admin_email,
    'password' AS admin_password,
    (SELECT COUNT(*) FROM companies) AS total_companies,
    (SELECT COUNT(*) FROM users) AS total_users,
    (SELECT COUNT(*) FROM employees) AS total_employees,
    (SELECT COUNT(*) FROM sections) AS total_sections,
    (SELECT COUNT(*) FROM weeks) AS total_weeks,
    (SELECT COUNT(*) FROM schedules) AS total_schedules,
    (SELECT COUNT(*) FROM shift_requests) AS total_requests,
    (SELECT COUNT(*) FROM notifications) AS total_notifications;
