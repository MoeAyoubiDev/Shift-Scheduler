CREATE DATABASE ShiftSchedulerDB;

USE ShiftSchedulerDB;

-- 1. Roles
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    RoleName VARCHAR(50) NOT NULL UNIQUE,
    description VARCHAR(255)
);

-- 2. Sections (Departments)
CREATE TABLE sections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    section_name VARCHAR(100) NOT NULL UNIQUE
);

INSERT INTO sections (section_name) VALUES ('App After-Sales');
INSERT INTO sections (section_name) VALUES ('Agent After-Sales');

-- 3. Users (Login Accounts)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(150),
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 4. User Roles (User ↔ Role ↔ Section)
CREATE TABLE user_roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    userId INT NOT NULL,
    role_id INT NOT NULL,
    section_id INT NOT NULL,
    FOREIGN KEY (userId) REFERENCES users(id),
    FOREIGN KEY (role_id) REFERENCES roles(id),
    FOREIGN KEY (section_id) REFERENCES sections(id)
);

-- 5. Employees (HR-level entity)
CREATE TABLE employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_rolesId INT NOT NULL,
    employee_code VARCHAR(50) NOT NULL,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(150),
    is_senior TINYINT(1) DEFAULT 0,
    seniority_level INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    FOREIGN KEY (user_rolesId) REFERENCES user_roles(id)
);

-- 6. Shift Types (High-level)
CREATE TABLE shift_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(10) NOT NULL UNIQUE, -- e.g. AM, MID, PM
    name VARCHAR(50) NOT NULL,
    start_time TIME NULL,
    end_time TIME NULL,
    duration_hours DECIMAL(4,2) NULL
);

-- 7. Shift Definitions (Concrete Shift Templates)
CREATE TABLE shift_definitions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    shiftName VARCHAR(50) NOT NULL,
    start_time TIME NULL,
    end_time TIME NULL,
    duration_hours DECIMAL(4,2) NULL,
    category VARCHAR(20) CHECK (category IN (
        'AM','MID','PM','MIDNIGHT',
        'OVERNIGHT','OFF','PAID LEAVE','SICK LEAVE'
    )),
    color_code VARCHAR(20),
    shift_type_id INT NULL,
    FOREIGN KEY (shift_type_id) REFERENCES shift_types(id)
);

-- 8. Schedule Patterns (5/2, 6/1)
CREATE TABLE schedule_patterns (
    id INT AUTO_INCREMENT PRIMARY KEY,
    names VARCHAR(50) NOT NULL,
    work_days_per_week INT NOT NULL,
    off_days_per_week INT NOT NULL,
    default_shift_duration_hours DECIMAL(4,2),
    description VARCHAR(255)
);

-- 9. Weeks
CREATE TABLE weeks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    week_start_date DATE NOT NULL,
    week_end_date DATE NOT NULL,
    is_locked_for_requests TINYINT(1) DEFAULT 0,
    lock_reason VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 10. Shift Requirements (How many per shift/day)
CREATE TABLE shift_requirements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    week_id INT NOT NULL,
    date DATE NOT NULL,
    shift_type_id INT NOT NULL,
    required_count INT NOT NULL,
    FOREIGN KEY (week_id) REFERENCES weeks(id),
    FOREIGN KEY (shift_type_id) REFERENCES shift_types(id)
);

-- 11. Shift Requests (Employees)
CREATE TABLE shift_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    week_id INT NOT NULL,
    SubmitDate DATE NOT NULL,
    shift_definition_id INT NULL,
    is_day_off TINYINT(1) DEFAULT 0,
    schedule_pattern_id INT NOT NULL,
    reason TEXT NULL,
    importance_level VARCHAR(10) CHECK (importance_level IN ('LOW','NORMAL','HIGH')) DEFAULT 'NORMAL',
    status VARCHAR(10) CHECK (status IN ('PENDING','APPROVED','DECLINED')) DEFAULT 'PENDING',
    flagged_as_important TINYINT(1) DEFAULT 0,
    submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    reviewed_by_admin_id INT NULL, -- FK employees
    reviewed_at DATETIME NULL,
    FOREIGN KEY (employee_id) REFERENCES employees(id),
    FOREIGN KEY (week_id) REFERENCES weeks(id),
    FOREIGN KEY (shift_definition_id) REFERENCES shift_definitions(id),
    FOREIGN KEY (schedule_pattern_id) REFERENCES schedule_patterns(id),
    FOREIGN KEY (reviewed_by_admin_id) REFERENCES employees(id)
);

-- 12. Schedules (Weekly containers)
CREATE TABLE schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    week_id INT NOT NULL,
    generated_by_admin_id INT NOT NULL, -- employee_id (TL)
    generated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(10) CHECK (status IN ('DRAFT','FINAL')) DEFAULT 'DRAFT',
    excel_file_path VARCHAR(255) NULL,
    notes TEXT NULL,
    FOREIGN KEY (week_id) REFERENCES weeks(id),
    FOREIGN KEY (generated_by_admin_id) REFERENCES employees(id)
);

-- 13. Schedule Shifts (Each shift row for a day)
CREATE TABLE schedule_shifts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    schedule_id INT NOT NULL,
    `date` DATE NOT NULL,
    shift_definition_id INT NOT NULL,
    required_count INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (schedule_id) REFERENCES schedules(id),
    FOREIGN KEY (shift_definition_id) REFERENCES shift_definitions(id)
);

-- 14. Schedule Assignments (Employee ↔ Shift)
CREATE TABLE schedule_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    schedule_shift_id INT NOT NULL,
    employee_id INT NOT NULL,
    assignment_source VARCHAR(20) CHECK (assignment_source IN (
        'MATCHED_REQUEST','AUTO_ASSIGNED','MANUALLY_ADJUSTED'
    )) DEFAULT 'MATCHED_REQUEST',
    is_senior TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (schedule_shift_id) REFERENCES schedule_shifts(id),
    FOREIGN KEY (employee_id) REFERENCES employees(id)
);

-- 15. Breaks (One per day per employee)
CREATE TABLE employee_breaks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    schedule_shift_id INT NULL,
    worked_date DATE NOT NULL,
    break_start DATETIME NULL,
    break_end DATETIME NULL,
    is_active TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id),
    FOREIGN KEY (schedule_shift_id) REFERENCES schedule_shifts(id),
    CONSTRAINT UQ_employee_break UNIQUE (employee_id, worked_date)
);

-- 16. System Settings (Flags & configs)
CREATE TABLE system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    Systemkey VARCHAR(100) NOT NULL UNIQUE,
    Svalue VARCHAR(255),
    descriptions VARCHAR(255)
);

-- 17. Notifications (Optional – if used alongside Firebase)
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type VARCHAR(20) CHECK (type IN ('SHIFT_REMINDER','SCHEDULE_PUBLISHED','REQUEST_STATUS')) NOT NULL,
    title VARCHAR(150) NOT NULL,
    body TEXT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    read_at DATETIME NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
