-- ============================================
-- SHIFT SCHEDULER SYSTEM - COMPLETE SCHEMA
-- ============================================

CREATE DATABASE IF NOT EXISTS ShiftSchedulerDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ShiftSchedulerDB;

-- ============================================
-- 1. ROLES TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(50) NOT NULL UNIQUE,
    description VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO roles (role_name, description) VALUES
('Director', 'Can access both sections, read-only'),
('Team Leader', 'Full CRUD permissions in assigned section'),
('Supervisor', 'Read-only access to assigned section'),
('Senior', 'Manages today\'s shift only'),
('Employee', 'Can submit requests and view schedule')
ON DUPLICATE KEY UPDATE role_name=role_name;

-- ============================================
-- 2. SECTIONS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS sections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    section_name VARCHAR(100) NOT NULL UNIQUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO sections (section_name) VALUES
('App After-Sales'),
('Agent After-Sales')
ON DUPLICATE KEY UPDATE section_name=section_name;

-- ============================================
-- 3. USERS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(150),
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ============================================
-- 4. USER ROLES TABLE (User ↔ Role ↔ Section)
-- ============================================
CREATE TABLE IF NOT EXISTS user_roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    role_id INT NOT NULL,
    section_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (section_id) REFERENCES sections(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_role_section (user_id, role_id, section_id)
);

-- ============================================
-- 5. EMPLOYEES TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_role_id INT NOT NULL,
    employee_code VARCHAR(50) NOT NULL UNIQUE,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(150),
    is_senior TINYINT(1) DEFAULT 0,
    seniority_level INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_role_id) REFERENCES user_roles(id) ON DELETE CASCADE
);

-- ============================================
-- 6. SHIFT DEFINITIONS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS shift_definitions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    shift_name VARCHAR(50) NOT NULL,
    category VARCHAR(20) NOT NULL CHECK (category IN ('AM', 'MID', 'PM', 'MIDNIGHT', 'OVERNIGHT', 'OFF')),
    start_time TIME,
    end_time TIME,
    duration_hours DECIMAL(4,2),
    color_code VARCHAR(20),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO shift_definitions (shift_name, category, start_time, end_time, duration_hours, color_code) VALUES
('Morning Shift', 'AM', '08:00:00', '16:00:00', 8.00, '#4CAF50'),
('Mid Shift', 'MID', '12:00:00', '20:00:00', 8.00, '#2196F3'),
('Evening Shift', 'PM', '16:00:00', '00:00:00', 8.00, '#FF9800'),
('Midnight Shift', 'MIDNIGHT', '00:00:00', '08:00:00', 8.00, '#9C27B0'),
('Overnight Shift', 'OVERNIGHT', '20:00:00', '08:00:00', 12.00, '#F44336'),
('Day Off', 'OFF', NULL, NULL, 0.00, '#9E9E9E')
ON DUPLICATE KEY UPDATE shift_name=shift_name;

-- ============================================
-- 7. SCHEDULE PATTERNS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS schedule_patterns (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pattern_name VARCHAR(50) NOT NULL UNIQUE,
    work_days_per_week INT NOT NULL,
    off_days_per_week INT NOT NULL,
    description VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO schedule_patterns (pattern_name, work_days_per_week, off_days_per_week, description) VALUES
('5 Days Pattern', 5, 2, 'Work 5 days, off 2 days'),
('6 Days Pattern', 6, 1, 'Work 6 days, off 1 day')
ON DUPLICATE KEY UPDATE pattern_name=pattern_name;

-- ============================================
-- 8. WEEKS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS weeks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    week_start_date DATE NOT NULL UNIQUE,
    week_end_date DATE NOT NULL,
    is_locked_for_requests TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- 9. SHIFT REQUIREMENTS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS shift_requirements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    week_id INT NOT NULL,
    date DATE NOT NULL,
    shift_definition_id INT NOT NULL,
    required_count INT NOT NULL DEFAULT 1,
    section_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (week_id) REFERENCES weeks(id) ON DELETE CASCADE,
    FOREIGN KEY (shift_definition_id) REFERENCES shift_definitions(id) ON DELETE CASCADE,
    FOREIGN KEY (section_id) REFERENCES sections(id) ON DELETE CASCADE,
    UNIQUE KEY unique_week_date_shift_section (week_id, date, shift_definition_id, section_id)
);

-- ============================================
-- 10. SHIFT REQUESTS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS shift_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    week_id INT NOT NULL,
    submit_date DATE NOT NULL,
    shift_definition_id INT,
    is_day_off TINYINT(1) DEFAULT 0,
    schedule_pattern_id INT NOT NULL,
    reason TEXT,
    importance_level VARCHAR(10) DEFAULT 'NORMAL' CHECK (importance_level IN ('LOW', 'NORMAL', 'HIGH')),
    status VARCHAR(10) DEFAULT 'PENDING' CHECK (status IN ('PENDING', 'APPROVED', 'DECLINED')),
    submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    reviewed_by_employee_id INT,
    reviewed_at DATETIME,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (week_id) REFERENCES weeks(id) ON DELETE CASCADE,
    FOREIGN KEY (shift_definition_id) REFERENCES shift_definitions(id) ON DELETE SET NULL,
    FOREIGN KEY (schedule_pattern_id) REFERENCES schedule_patterns(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by_employee_id) REFERENCES employees(id) ON DELETE SET NULL,
    INDEX idx_employee_week (employee_id, week_id),
    INDEX idx_status (status)
);

-- ============================================
-- 11. SCHEDULES TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    week_id INT NOT NULL,
    section_id INT NOT NULL,
    generated_by_employee_id INT NOT NULL,
    generated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(10) DEFAULT 'DRAFT' CHECK (status IN ('DRAFT', 'FINAL')),
    notes TEXT,
    FOREIGN KEY (week_id) REFERENCES weeks(id) ON DELETE CASCADE,
    FOREIGN KEY (section_id) REFERENCES sections(id) ON DELETE CASCADE,
    FOREIGN KEY (generated_by_employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    UNIQUE KEY unique_week_section (week_id, section_id)
);

-- ============================================
-- 12. SCHEDULE SHIFTS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS schedule_shifts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    schedule_id INT NOT NULL,
    date DATE NOT NULL,
    shift_definition_id INT NOT NULL,
    required_count INT NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (schedule_id) REFERENCES schedules(id) ON DELETE CASCADE,
    FOREIGN KEY (shift_definition_id) REFERENCES shift_definitions(id) ON DELETE CASCADE,
    INDEX idx_schedule_date (schedule_id, date)
);

-- ============================================
-- 13. SCHEDULE ASSIGNMENTS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS schedule_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    schedule_shift_id INT NOT NULL,
    employee_id INT NOT NULL,
    assignment_source VARCHAR(20) DEFAULT 'MATCHED_REQUEST' CHECK (assignment_source IN ('MATCHED_REQUEST', 'AUTO_ASSIGNED', 'MANUALLY_ADJUSTED')),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (schedule_shift_id) REFERENCES schedule_shifts(id) ON DELETE CASCADE,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    UNIQUE KEY unique_shift_employee (schedule_shift_id, employee_id)
);

-- ============================================
-- 14. EMPLOYEE BREAKS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS employee_breaks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    schedule_shift_id INT,
    worked_date DATE NOT NULL,
    break_start DATETIME,
    break_end DATETIME,
    is_active TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (schedule_shift_id) REFERENCES schedule_shifts(id) ON DELETE SET NULL,
    UNIQUE KEY unique_employee_date (employee_id, worked_date)
);

-- ============================================
-- 15. SYSTEM SETTINGS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value VARCHAR(255),
    description VARCHAR(255),
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO system_settings (setting_key, setting_value, description) VALUES
('break_duration_minutes', '30', 'Standard break duration in minutes'),
('allow_sunday_requests', '0', 'Allow shift requests for Sunday (0=No, 1=Yes)')
ON DUPLICATE KEY UPDATE setting_key=setting_key;

