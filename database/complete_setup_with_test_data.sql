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
    company_id INT NOT NULL,
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
    company_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id),
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_role_company (user_id, role_id, company_id),
    INDEX idx_company (company_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE shift_requirements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    week_id INT NOT NULL,
    shift_date DATE NOT NULL,
    shift_type_id INT NOT NULL,
    required_count INT NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (week_id) REFERENCES weeks(id) ON DELETE CASCADE,
    FOREIGN KEY (shift_type_id) REFERENCES shift_types(id),
    INDEX idx_company (company_id),
    UNIQUE KEY unique_shift_requirement (week_id, company_id, shift_date, shift_type_id)
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
    company_id INT NOT NULL,
    week_id INT NOT NULL,
    generated_by_admin_id INT NOT NULL,
    generated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('DRAFT','FINAL') DEFAULT 'DRAFT',
    notes TEXT NULL,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (week_id) REFERENCES weeks(id) ON DELETE CASCADE,
    FOREIGN KEY (generated_by_admin_id) REFERENCES employees(id),
    INDEX idx_company (company_id),
    UNIQUE KEY unique_schedule_company_week (week_id, company_id)
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
    ('Supervisor', 'Full system visibility and oversight'),
    ('Team Leader', 'Scheduling and employee management'),
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

DROP PROCEDURE IF EXISTS sp_create_supervisor $$
CREATE PROCEDURE sp_create_supervisor(
    IN p_company_id INT,
    IN p_username VARCHAR(100),
    IN p_password_hash VARCHAR(255),
    IN p_email VARCHAR(150),
    IN p_full_name VARCHAR(150)
)
BEGIN
    DECLARE v_user_id INT;
    DECLARE v_role_id INT;
    DECLARE v_username_candidate VARCHAR(100);
    DECLARE v_username_exists INT DEFAULT 1;
    DECLARE v_suffix INT DEFAULT 0;

    SELECT id INTO v_role_id FROM roles WHERE role_name = 'Supervisor' LIMIT 1;

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
    VALUES (p_company_id, v_username_candidate, p_password_hash, p_email, 'Supervisor', 1, 1);
    SET v_user_id = LAST_INSERT_ID();

    INSERT INTO user_roles (user_id, role_id, company_id)
    VALUES (v_user_id, v_role_id, p_company_id);

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
           c.company_name,
           ur.id AS user_role_id,
           r.id AS role_id,
           r.role_name,
           e.id AS employee_id,
           e.full_name AS employee_name,
           e.is_senior,
           e.seniority_level,
           e.employee_code
    FROM users u
    INNER JOIN user_roles ur ON ur.user_id = u.id
    INNER JOIN roles r ON r.id = ur.role_id
    INNER JOIN companies c ON c.id = u.company_id
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
           c.company_name,
           ur.id AS user_role_id,
           r.id AS role_id,
           r.role_name,
           e.id AS employee_id,
           e.full_name AS employee_name,
           e.is_senior,
           e.seniority_level,
           e.employee_code
    FROM users u
    INNER JOIN user_roles ur ON ur.user_id = u.id
    INNER JOIN roles r ON r.id = ur.role_id
    INNER JOIN companies c ON c.id = u.company_id
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
CREATE PROCEDURE sp_upsert_week(IN p_company_id INT, IN p_week_start DATE, IN p_week_end DATE)
BEGIN
    DECLARE v_week_id INT;
    SELECT id INTO v_week_id FROM weeks WHERE week_start_date = p_week_start AND company_id = p_company_id LIMIT 1;
    IF v_week_id IS NULL THEN
        INSERT INTO weeks (company_id, week_start_date, week_end_date) VALUES (p_company_id, p_week_start, p_week_end);
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
CREATE PROCEDURE sp_get_shift_requirements(IN p_week_id INT, IN p_company_id INT)
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
    WHERE sr.week_id = p_week_id AND sr.company_id = p_company_id;
END $$

DROP PROCEDURE IF EXISTS sp_set_shift_requirement $$
CREATE PROCEDURE sp_set_shift_requirement(
    IN p_week_id INT,
    IN p_company_id INT,
    IN p_date DATE,
    IN p_shift_type_id INT,
    IN p_required_count INT
)
BEGIN
    INSERT INTO shift_requirements (week_id, company_id, shift_date, shift_type_id, required_count)
    VALUES (p_week_id, p_company_id, p_date, p_shift_type_id, p_required_count)
    ON DUPLICATE KEY UPDATE required_count = p_required_count;
END $$

DROP PROCEDURE IF EXISTS sp_generate_weekly_schedule $$
CREATE PROCEDURE sp_generate_weekly_schedule(IN p_week_id INT, IN p_company_id INT, IN p_generated_by_employee_id INT)
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
    WHERE week_id = p_week_id AND company_id = p_company_id
    LIMIT 1;

    IF v_schedule_id IS NULL THEN
        INSERT INTO schedules (week_id, company_id, generated_by_admin_id, status)
        VALUES (p_week_id, p_company_id, p_generated_by_employee_id, 'DRAFT');
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
    WHERE sr.week_id = p_week_id AND sr.company_id = p_company_id;

    INSERT INTO schedule_assignments (schedule_shift_id, employee_id, assignment_source, is_senior, notes)
    SELECT ss.id, sr.employee_id, 'MATCHED_REQUEST', e.is_senior, CONCAT('Request: ', COALESCE(sr.reason, ''))
    FROM schedule_shifts ss
    INNER JOIN shift_requests sr ON sr.week_id = p_week_id AND sr.request_date = ss.shift_date
    INNER JOIN employees e ON e.id = sr.employee_id
    INNER JOIN user_roles ur ON ur.id = e.user_role_id
    WHERE ss.shift_definition_id = sr.shift_definition_id
      AND sr.status = 'APPROVED'
      AND sr.is_day_off = 0
      AND ur.company_id = p_company_id
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
        WHERE ur.company_id = p_company_id
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
CREATE PROCEDURE sp_get_weekly_schedule(IN p_week_id INT, IN p_company_id INT)
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
    WHERE s.week_id = p_week_id AND s.company_id = p_company_id
    ORDER BY ss.shift_date, sd.shift_name, e.full_name;
END $$

DROP PROCEDURE IF EXISTS sp_get_today_shift $$
CREATE PROCEDURE sp_get_today_shift(IN p_company_id INT, IN p_today DATE)
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
    WHERE s.company_id = p_company_id
      AND ss.shift_date = p_today
      AND sd.category <> 'OFF'
    ORDER BY sd.start_time, e.full_name;
END $$

DROP PROCEDURE IF EXISTS sp_get_coverage_gaps $$
CREATE PROCEDURE sp_get_coverage_gaps(IN p_week_id INT, IN p_company_id INT)
BEGIN
    SELECT ss.shift_date,
           sd.shift_name,
           ss.required_count,
           COUNT(sa.id) AS assigned_count
    FROM schedules s
    INNER JOIN schedule_shifts ss ON ss.schedule_id = s.id
    INNER JOIN shift_definitions sd ON sd.id = ss.shift_definition_id
    LEFT JOIN schedule_assignments sa ON sa.schedule_shift_id = ss.id
    WHERE s.week_id = p_week_id AND s.company_id = p_company_id
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
CREATE PROCEDURE sp_get_break_status(IN p_company_id INT, IN p_today DATE)
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
    LEFT JOIN schedule_shifts ss ON ss.id = eb.schedule_shift_id
    LEFT JOIN shift_definitions sd ON sd.id = ss.shift_definition_id
    WHERE ur.company_id = p_company_id AND eb.worked_date = p_today
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
    SET v_day_of_week = DAYOFWEEK(p_request_date);
    IF v_day_of_week = 1 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Shift requests are not allowed on Sunday';
    END IF;

    INSERT INTO shift_requests (employee_id, week_id, request_date, shift_definition_id, is_day_off, schedule_pattern_id, reason, importance_level)
    VALUES (p_employee_id, p_week_id, NULLIF(p_shift_definition_id, 0), p_is_day_off, p_schedule_pattern_id, p_reason, p_importance_level);

    SELECT LAST_INSERT_ID() AS request_id, 'Request submitted successfully' AS message;
END $$

DROP PROCEDURE IF EXISTS sp_get_shift_requests $$
CREATE PROCEDURE sp_get_shift_requests(IN p_week_id INT, IN p_company_id INT, IN p_employee_id INT)
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
    LEFT JOIN shift_definitions sd ON sd.id = sr.shift_definition_id
    INNER JOIN schedule_patterns sp ON sp.id = sr.schedule_pattern_id
    LEFT JOIN employees reviewer ON reviewer.id = sr.reviewed_by_admin_id
    WHERE sr.week_id = p_week_id AND ur.company_id = p_company_id
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
    IN p_company_id INT,
    IN p_username VARCHAR(100),
    IN p_password_hash VARCHAR(255),
    IN p_email VARCHAR(150),
    IN p_role_id INT,
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
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    START TRANSACTION;

    SELECT COUNT(*) INTO v_username_exists FROM users WHERE username = p_username AND company_id <=> p_company_id;
    IF v_username_exists > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Username already exists';
    END IF;

    IF p_email IS NOT NULL AND p_email != '' THEN
        SELECT COUNT(*) INTO v_email_exists FROM users WHERE email = p_email AND email IS NOT NULL AND email != '' AND company_id <=> p_company_id;
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
    VALUES (p_company_id, p_username, p_password_hash, p_email);
    SET v_user_id = LAST_INSERT_ID();

    INSERT INTO user_roles (user_id, role_id, company_id)
    VALUES (v_user_id, p_role_id, p_company_id);
    SET v_user_role_id = LAST_INSERT_ID();

    INSERT INTO employees (user_role_id, employee_code, full_name, email, is_senior, seniority_level)
    VALUES (v_user_role_id, p_employee_code, p_full_name, p_email, p_is_senior, p_seniority_level);
    SET v_employee_id = LAST_INSERT_ID();

    COMMIT;
    SELECT v_employee_id AS employee_id, v_user_id AS user_id;
END $$

DROP PROCEDURE IF EXISTS sp_create_leader $$
CREATE PROCEDURE sp_create_leader(
    IN p_company_id INT,
    IN p_username VARCHAR(100),
    IN p_password_hash VARCHAR(255),
    IN p_email VARCHAR(150),
    IN p_role_id INT,
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

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    START TRANSACTION;

    SELECT COUNT(*) INTO v_username_exists FROM users WHERE username = p_username AND company_id <=> p_company_id;
    IF v_username_exists > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Username already exists';
    END IF;

    IF p_email IS NOT NULL AND p_email != '' THEN
        SELECT COUNT(*) INTO v_email_exists FROM users WHERE email = p_email AND email IS NOT NULL AND email != '' AND company_id <=> p_company_id;
        IF v_email_exists > 0 THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Email already exists';
        END IF;
    END IF;

    WHILE v_employee_code_exists > 0 DO
        SET v_employee_code = CONCAT('LDR-', LPAD(FLOOR(RAND() * 9999), 4, '0'));
        SELECT COUNT(*) INTO v_employee_code_exists FROM employees WHERE employee_code = v_employee_code;
    END WHILE;

    INSERT INTO users (company_id, username, password_hash, email)
    VALUES (p_company_id, p_username, p_password_hash, p_email);
    SET v_user_id = LAST_INSERT_ID();

    INSERT INTO user_roles (user_id, role_id, company_id)
    VALUES (v_user_id, p_role_id, p_company_id);
    SET v_user_role_id = LAST_INSERT_ID();

    INSERT INTO employees (user_role_id, employee_code, full_name, email, is_senior, seniority_level)
    VALUES (v_user_role_id, v_employee_code, p_full_name, p_email, 0, 0);
    SET v_employee_id = LAST_INSERT_ID();

    COMMIT;
    SELECT v_employee_id AS employee_id, v_user_id AS user_id;
END $$

DROP PROCEDURE IF EXISTS sp_get_employees_by_company $$
CREATE PROCEDURE sp_get_employees_by_company(IN p_company_id INT)
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
    WHERE ur.company_id = p_company_id
      AND e.is_active = 1
    ORDER BY e.seniority_level DESC, e.full_name;
END $$

DROP PROCEDURE IF EXISTS sp_get_available_employees $$
CREATE PROCEDURE sp_get_available_employees(IN p_company_id INT, IN p_date DATE)
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
    WHERE ur.company_id = p_company_id
      AND e.is_active = 1
      AND NOT EXISTS (
          SELECT 1
          FROM schedule_assignments sa
          INNER JOIN schedule_shifts ss ON ss.id = sa.schedule_shift_id
          INNER JOIN schedules s ON s.id = ss.schedule_id
          WHERE sa.employee_id = e.id
            AND ss.shift_date = p_date
            AND s.company_id = p_company_id
      )
    ORDER BY e.seniority_level DESC, e.full_name;
END $$

DROP PROCEDURE IF EXISTS sp_update_employee $$
CREATE PROCEDURE sp_update_employee(
    IN p_employee_id INT,
    IN p_company_id INT,
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
    WHERE e.id = p_employee_id AND ur.company_id = p_company_id;

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
           u.company_id
    FROM employees e
    INNER JOIN user_roles ur ON ur.id = e.user_role_id
    INNER JOIN roles r ON r.id = ur.role_id
    INNER JOIN users u ON u.id = ur.user_id
    WHERE e.is_active = 1
      AND r.role_name IN ('Team Leader', 'Supervisor')
    ORDER BY r.role_name, e.full_name;
END $$

DROP PROCEDURE IF EXISTS sp_performance_report $$
CREATE PROCEDURE sp_performance_report(IN p_start_date DATE, IN p_end_date DATE, IN p_company_id INT, IN p_employee_id INT)
BEGIN
    SELECT e.id AS employee_id,
           e.full_name AS employee_name,
           e.employee_code,
           COUNT(DISTINCT sa.schedule_shift_id) AS days_worked,
           COALESCE(SUM(GREATEST(TIMESTAMPDIFF(MINUTE, eb.break_start, eb.break_end) - 30, 0)), 0) AS total_delay_minutes,
           COALESCE(AVG(GREATEST(TIMESTAMPDIFF(MINUTE, eb.break_start, eb.break_end) - 30, 0)), 0) AS average_delay_minutes
    FROM employees e
    INNER JOIN user_roles ur ON ur.id = e.user_role_id
    LEFT JOIN schedule_assignments sa ON sa.employee_id = e.id
    LEFT JOIN schedule_shifts ss ON ss.id = sa.schedule_shift_id
    LEFT JOIN employee_breaks eb ON eb.employee_id = e.id
        AND eb.worked_date BETWEEN p_start_date AND p_end_date
        AND eb.break_end IS NOT NULL
    WHERE ur.company_id = p_company_id
      AND e.is_active = 1
      AND (p_employee_id IS NULL OR e.id = p_employee_id)
      AND (ss.shift_date IS NULL OR ss.shift_date BETWEEN p_start_date AND p_end_date)
    GROUP BY e.id, e.full_name, e.employee_code
    ORDER BY total_delay_minutes ASC, average_delay_minutes ASC, e.full_name;
END $$

DROP PROCEDURE IF EXISTS sp_supervisor_dashboard $$
CREATE PROCEDURE sp_supervisor_dashboard(IN p_company_id INT, IN p_week_id INT)
BEGIN
    SELECT COUNT(DISTINCT e.id) AS total_employees,
           SUM(CASE WHEN sr.status = 'APPROVED' THEN 1 ELSE 0 END) AS approved_requests,
           SUM(CASE WHEN sr.status = 'PENDING' THEN 1 ELSE 0 END) AS pending_requests,
           SUM(CASE WHEN eb.worked_date = CURDATE() THEN 1 ELSE 0 END) AS breaks_today
    FROM employees e
    INNER JOIN user_roles ur ON ur.id = e.user_role_id AND ur.company_id = p_company_id
    LEFT JOIN shift_requests sr ON sr.employee_id = e.id AND sr.week_id = p_week_id
    LEFT JOIN employee_breaks eb ON eb.employee_id = e.id
    WHERE e.is_active = 1;
END $$

DELIMITER ;
