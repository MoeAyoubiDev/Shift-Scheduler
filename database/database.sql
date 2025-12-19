CREATE DATABASE IF NOT EXISTS ShiftSchedulerDB;

USE ShiftSchedulerDB;

-- --------------------------------------------------
-- CORE REFERENCE TABLES
-- --------------------------------------------------
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(50) NOT NULL UNIQUE,
    description VARCHAR(255)
);

CREATE TABLE sections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    section_name VARCHAR(100) NOT NULL UNIQUE
);

INSERT INTO sections (section_name) VALUES ('App After-Sales'), ('Agent After-Sales');

INSERT INTO roles (role_name, description) VALUES
('Director', 'Read-only access to both sections'),
('Team Leader', 'Full CRUD access for assigned section'),
('Supervisor', 'Read-only access for assigned section'),
('Senior', 'Shift leader for today operations'),
('Employee', 'Shift request and schedule access');

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(150),
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE user_roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    role_id INT NOT NULL,
    section_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (role_id) REFERENCES roles(id),
    FOREIGN KEY (section_id) REFERENCES sections(id)
);

CREATE TABLE employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_role_id INT NOT NULL,
    employee_code VARCHAR(50) NOT NULL,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(150),
    is_senior TINYINT(1) DEFAULT 0,
    seniority_level INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    FOREIGN KEY (user_role_id) REFERENCES user_roles(id)
);

CREATE TABLE shift_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(10) NOT NULL UNIQUE,
    name VARCHAR(50) NOT NULL,
    start_time TIME NULL,
    end_time TIME NULL,
    duration_hours DECIMAL(4,2) NULL
);

INSERT INTO shift_types (code, name, start_time, end_time, duration_hours) VALUES
('AM', 'Morning', '06:00:00', '14:00:00', 8.00),
('MID', 'Mid', '10:00:00', '18:00:00', 8.00),
('PM', 'Evening', '14:00:00', '22:00:00', 8.00),
('MIDNIGHT', 'Midnight', '18:00:00', '02:00:00', 8.00),
('OVERNIGHT', 'Overnight', '22:00:00', '06:00:00', 8.00);

CREATE TABLE shift_definitions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    shift_name VARCHAR(50) NOT NULL,
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

INSERT INTO shift_definitions (shift_name, start_time, end_time, duration_hours, category, color_code, shift_type_id) VALUES
('AM Shift', '06:00:00', '14:00:00', 8.00, 'AM', '#38bdf8', 1),
('Mid Shift', '10:00:00', '18:00:00', 8.00, 'MID', '#6366f1', 2),
('PM Shift', '14:00:00', '22:00:00', 8.00, 'PM', '#f97316', 3),
('Midnight Shift', '18:00:00', '02:00:00', 8.00, 'MIDNIGHT', '#0f172a', 4),
('Overnight Shift', '22:00:00', '06:00:00', 8.00, 'OVERNIGHT', '#334155', 5);

CREATE TABLE schedule_patterns (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    work_days_per_week INT NOT NULL,
    off_days_per_week INT NOT NULL,
    default_shift_duration_hours DECIMAL(4,2),
    description VARCHAR(255)
);

INSERT INTO schedule_patterns (name, work_days_per_week, off_days_per_week, default_shift_duration_hours, description) VALUES
('5x2', 5, 2, 9.00, '5 days on / 2 days off (9 hours)'),
('6x1', 6, 1, 7.50, '6 days on / 1 day off (7.5 hours)');

CREATE TABLE weeks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    week_start_date DATE NOT NULL,
    week_end_date DATE NOT NULL,
    is_locked_for_requests TINYINT(1) DEFAULT 0,
    lock_reason VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE shift_requirements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    week_id INT NOT NULL,
    section_id INT NOT NULL,
    shift_date DATE NOT NULL,
    shift_type_id INT NOT NULL,
    required_count INT NOT NULL,
    FOREIGN KEY (week_id) REFERENCES weeks(id),
    FOREIGN KEY (section_id) REFERENCES sections(id),
    FOREIGN KEY (shift_type_id) REFERENCES shift_types(id)
);

CREATE UNIQUE INDEX uq_shift_requirements ON shift_requirements (week_id, section_id, shift_date, shift_type_id);

CREATE TABLE shift_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    week_id INT NOT NULL,
    submit_date DATE NOT NULL,
    shift_definition_id INT NULL,
    is_day_off TINYINT(1) DEFAULT 0,
    schedule_pattern_id INT NOT NULL,
    reason TEXT NULL,
    importance_level VARCHAR(10) CHECK (importance_level IN ('LOW','NORMAL','HIGH')) DEFAULT 'NORMAL',
    status VARCHAR(10) CHECK (status IN ('PENDING','APPROVED','DECLINED')) DEFAULT 'PENDING',
    flagged_as_important TINYINT(1) DEFAULT 0,
    submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    reviewed_by_admin_id INT NULL,
    reviewed_at DATETIME NULL,
    FOREIGN KEY (employee_id) REFERENCES employees(id),
    FOREIGN KEY (week_id) REFERENCES weeks(id),
    FOREIGN KEY (shift_definition_id) REFERENCES shift_definitions(id),
    FOREIGN KEY (schedule_pattern_id) REFERENCES schedule_patterns(id),
    FOREIGN KEY (reviewed_by_admin_id) REFERENCES employees(id)
);

CREATE TABLE schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    week_id INT NOT NULL,
    section_id INT NOT NULL,
    generated_by_admin_id INT NOT NULL,
    generated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(10) CHECK (status IN ('DRAFT','FINAL')) DEFAULT 'DRAFT',
    excel_file_path VARCHAR(255) NULL,
    notes TEXT NULL,
    FOREIGN KEY (week_id) REFERENCES weeks(id),
    FOREIGN KEY (section_id) REFERENCES sections(id),
    FOREIGN KEY (generated_by_admin_id) REFERENCES employees(id)
);

CREATE UNIQUE INDEX uq_schedules_week_section ON schedules (week_id, section_id);

CREATE TABLE schedule_shifts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    schedule_id INT NOT NULL,
    shift_date DATE NOT NULL,
    shift_definition_id INT NOT NULL,
    required_count INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (schedule_id) REFERENCES schedules(id),
    FOREIGN KEY (shift_definition_id) REFERENCES shift_definitions(id)
);

CREATE TABLE schedule_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    schedule_shift_id INT NOT NULL,
    employee_id INT NOT NULL,
    assignment_source VARCHAR(20) CHECK (assignment_source IN (
        'MATCHED_REQUEST','AUTO_ASSIGNED','MANUALLY_ADJUSTED'
    )) DEFAULT 'MATCHED_REQUEST',
    is_senior TINYINT(1) DEFAULT 0,
    notes VARCHAR(255) DEFAULT '',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (schedule_shift_id) REFERENCES schedule_shifts(id),
    FOREIGN KEY (employee_id) REFERENCES employees(id)
);

CREATE UNIQUE INDEX uq_schedule_assignment ON schedule_assignments (schedule_shift_id, employee_id);

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
    CONSTRAINT uq_employee_break UNIQUE (employee_id, worked_date)
);

CREATE TABLE system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    system_key VARCHAR(100) NOT NULL UNIQUE,
    system_value VARCHAR(255),
    description VARCHAR(255)
);

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

-- --------------------------------------------------
-- STORED PROCEDURES
-- --------------------------------------------------
DELIMITER $$

CREATE PROCEDURE sp_get_user_login(IN p_username VARCHAR(100))
BEGIN
    SELECT u.id AS user_id,
           u.username,
           u.password_hash,
           u.email,
           r.role_name,
           s.id AS section_id,
           s.section_name,
           e.id AS employee_id,
           e.is_senior,
           e.seniority_level
    FROM users u
    INNER JOIN user_roles ur ON ur.user_id = u.id
    INNER JOIN roles r ON r.id = ur.role_id
    INNER JOIN sections s ON s.id = ur.section_id
    LEFT JOIN employees e ON e.user_role_id = ur.id
    WHERE u.username = p_username
    ORDER BY ur.id;
END$$

CREATE PROCEDURE sp_get_roles()
BEGIN
    SELECT id, role_name
    FROM roles
    ORDER BY id;
END$$

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

    INSERT INTO users (username, password_hash, email)
    VALUES (p_username, p_password_hash, p_email);

    SET v_user_id = LAST_INSERT_ID();

    INSERT INTO user_roles (user_id, role_id, section_id)
    VALUES (v_user_id, p_role_id, p_section_id);

    SET v_user_role_id = LAST_INSERT_ID();

    INSERT INTO employees (user_role_id, employee_code, full_name, email, is_senior, seniority_level)
    VALUES (v_user_role_id, p_employee_code, p_full_name, p_email, p_is_senior, p_seniority_level);

    SET v_employee_id = LAST_INSERT_ID();

    SELECT v_employee_id AS employee_id;
END$$

CREATE PROCEDURE sp_upsert_week(IN p_week_start DATE, IN p_week_end DATE)
BEGIN
    DECLARE v_week_id INT;

    SELECT id INTO v_week_id FROM weeks WHERE week_start_date = p_week_start LIMIT 1;

    IF v_week_id IS NULL THEN
        INSERT INTO weeks (week_start_date, week_end_date)
        VALUES (p_week_start, p_week_end);
        SET v_week_id = LAST_INSERT_ID();
    END IF;

    SELECT v_week_id AS week_id;
END$$

CREATE PROCEDURE sp_get_shift_types()
BEGIN
    SELECT id AS shift_type_id, code, name AS shift_type_name
    FROM shift_types
    ORDER BY id;
END$$

CREATE PROCEDURE sp_get_shift_definitions()
BEGIN
    SELECT sd.id AS definition_id,
           sd.shift_name AS definition_name,
           sd.category,
           st.name AS shift_type_name,
           st.id AS shift_type_id
    FROM shift_definitions sd
    LEFT JOIN shift_types st ON st.id = sd.shift_type_id
    ORDER BY sd.id;
END$$

CREATE PROCEDURE sp_get_schedule_patterns()
BEGIN
    SELECT id, name, work_days_per_week, off_days_per_week, description
    FROM schedule_patterns
    ORDER BY id;
END$$

CREATE PROCEDURE sp_submit_shift_request(
    IN p_employee_id INT,
    IN p_week_id INT,
    IN p_submit_date DATE,
    IN p_shift_definition_id INT,
    IN p_is_day_off TINYINT,
    IN p_schedule_pattern_id INT,
    IN p_reason TEXT,
    IN p_importance_level VARCHAR(10)
)
BEGIN
    INSERT INTO shift_requests (
        employee_id,
        week_id,
        submit_date,
        shift_definition_id,
        is_day_off,
        schedule_pattern_id,
        reason,
        importance_level
    ) VALUES (
        p_employee_id,
        p_week_id,
        p_submit_date,
        NULLIF(p_shift_definition_id, 0),
        p_is_day_off,
        p_schedule_pattern_id,
        p_reason,
        p_importance_level
    );

    SELECT LAST_INSERT_ID() AS request_id;
END$$

CREATE PROCEDURE sp_get_shift_requests(IN p_week_id INT, IN p_section_id INT)
BEGIN
    SELECT sr.id,
           sr.submit_date,
           sd.shift_name,
           sp.name AS pattern_name,
           sr.importance_level,
           sr.status,
           e.full_name AS employee_name
    FROM shift_requests sr
    INNER JOIN employees e ON e.id = sr.employee_id
    INNER JOIN user_roles ur ON ur.id = e.user_role_id
    INNER JOIN sections s ON s.id = ur.section_id
    LEFT JOIN shift_definitions sd ON sd.id = sr.shift_definition_id
    INNER JOIN schedule_patterns sp ON sp.id = sr.schedule_pattern_id
    WHERE sr.week_id = p_week_id AND s.id = p_section_id
    ORDER BY sr.submitted_at DESC;
END$$

CREATE PROCEDURE sp_update_shift_request_status(
    IN p_request_id INT,
    IN p_status VARCHAR(10),
    IN p_reviewer_id INT
)
BEGIN
    UPDATE shift_requests
    SET status = p_status,
        reviewed_by_admin_id = p_reviewer_id,
        reviewed_at = NOW()
    WHERE id = p_request_id;
END$$

CREATE PROCEDURE sp_get_shift_requirements(IN p_week_id INT, IN p_section_id INT)
BEGIN
    SELECT id,
           shift_date,
           shift_type_id,
           required_count
    FROM shift_requirements
    WHERE week_id = p_week_id AND section_id = p_section_id
    ORDER BY shift_date, shift_type_id;
END$$

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
    ON DUPLICATE KEY UPDATE required_count = VALUES(required_count);
END$$

CREATE PROCEDURE sp_generate_weekly_schedule(
    IN p_week_id INT,
    IN p_section_id INT,
    IN p_generated_by_employee_id INT
)
BEGIN
    DECLARE v_schedule_id INT;
    DECLARE v_assignments_needed INT DEFAULT 0;

    INSERT INTO schedules (week_id, section_id, generated_by_admin_id)
    VALUES (p_week_id, p_section_id, p_generated_by_employee_id)
    ON DUPLICATE KEY UPDATE generated_at = NOW(), generated_by_admin_id = p_generated_by_employee_id;

    SELECT id INTO v_schedule_id FROM schedules WHERE week_id = p_week_id AND section_id = p_section_id LIMIT 1;

    DELETE FROM schedule_assignments
    WHERE schedule_shift_id IN (SELECT id FROM schedule_shifts WHERE schedule_id = v_schedule_id);
    DELETE FROM schedule_shifts WHERE schedule_id = v_schedule_id;

    INSERT INTO schedule_shifts (schedule_id, shift_date, shift_definition_id, required_count)
    SELECT v_schedule_id,
           sr.shift_date,
           sd.id,
           sr.required_count
    FROM shift_requirements sr
    INNER JOIN shift_types st ON st.id = sr.shift_type_id
    INNER JOIN shift_definitions sd ON sd.shift_type_id = st.id
    WHERE sr.week_id = p_week_id AND sr.section_id = p_section_id;

    INSERT INTO schedule_assignments (schedule_shift_id, employee_id, assignment_source, is_senior, notes)
    SELECT ss.id,
           sr.employee_id,
           'MATCHED_REQUEST',
           e.is_senior,
           sr.reason
    FROM schedule_shifts ss
    INNER JOIN shift_requests sr ON sr.week_id = p_week_id
    INNER JOIN employees e ON e.id = sr.employee_id
    WHERE ss.shift_definition_id = sr.shift_definition_id
      AND sr.status = 'APPROVED'
      AND sr.is_day_off = 0;

    SELECT SUM(GREATEST(ss.required_count - COALESCE(sa_counts.assigned_count, 0), 0))
    INTO v_assignments_needed
    FROM schedule_shifts ss
    LEFT JOIN (
        SELECT schedule_shift_id, COUNT(*) AS assigned_count
        FROM schedule_assignments
        GROUP BY schedule_shift_id
    ) sa_counts ON sa_counts.schedule_shift_id = ss.id
    WHERE ss.schedule_id = v_schedule_id;

    WHILE v_assignments_needed > 0 DO
        INSERT INTO schedule_assignments (schedule_shift_id, employee_id, assignment_source, is_senior, notes)
        SELECT ss.id,
               e.id,
               'AUTO_ASSIGNED',
               e.is_senior,
               'Auto assignment'
        FROM schedule_shifts ss
        INNER JOIN shift_definitions sd ON sd.id = ss.shift_definition_id
        INNER JOIN employees e ON e.is_active = 1
        INNER JOIN user_roles ur ON ur.id = e.user_role_id AND ur.section_id = p_section_id
        LEFT JOIN schedule_assignments sa ON sa.schedule_shift_id = ss.id AND sa.employee_id = e.id
        LEFT JOIN (
            SELECT schedule_shift_id, COUNT(*) AS assigned_count
            FROM schedule_assignments
            GROUP BY schedule_shift_id
        ) sa_counts ON sa_counts.schedule_shift_id = ss.id
        WHERE sa.id IS NULL
          AND ss.required_count > COALESCE(sa_counts.assigned_count, 0)
          AND sd.category <> 'OFF'
        ORDER BY e.seniority_level DESC
        LIMIT 1;

        SET v_assignments_needed = v_assignments_needed - 1;
    END WHILE;
END$$

CREATE PROCEDURE sp_get_weekly_schedule(IN p_week_id INT, IN p_section_id INT)
BEGIN
    SELECT ss.shift_date,
           sd.shift_name,
           sd.id AS shift_definition_id,
           sa.id AS assignment_id,
           sa.assignment_source,
           sa.notes,
           e.full_name AS employee_name
    FROM schedule_shifts ss
    INNER JOIN schedules s ON s.id = ss.schedule_id
    INNER JOIN shift_definitions sd ON sd.id = ss.shift_definition_id
    LEFT JOIN schedule_assignments sa ON sa.schedule_shift_id = ss.id
    LEFT JOIN employees e ON e.id = sa.employee_id
    WHERE s.week_id = p_week_id AND s.section_id = p_section_id
    ORDER BY ss.shift_date, sd.shift_name, e.full_name;
END$$

CREATE PROCEDURE sp_update_schedule_assignment(IN p_assignment_id INT, IN p_shift_definition_id INT)
BEGIN
    UPDATE schedule_shifts ss
    INNER JOIN schedule_assignments sa ON sa.schedule_shift_id = ss.id
    SET ss.shift_definition_id = p_shift_definition_id,
        sa.assignment_source = 'MANUALLY_ADJUSTED'
    WHERE sa.id = p_assignment_id;
END$$

CREATE PROCEDURE sp_get_today_shift(IN p_section_id INT, IN p_today DATE)
BEGIN
    SELECT ss.id AS schedule_shift_id,
           sd.shift_name,
           e.full_name AS employee_name,
           e.id AS employee_id,
           'Scheduled' AS attendance_status
    FROM schedule_shifts ss
    INNER JOIN schedules s ON s.id = ss.schedule_id
    INNER JOIN shift_definitions sd ON sd.id = ss.shift_definition_id
    LEFT JOIN schedule_assignments sa ON sa.schedule_shift_id = ss.id
    LEFT JOIN employees e ON e.id = sa.employee_id
    WHERE s.section_id = p_section_id AND ss.shift_date = p_today
    ORDER BY sd.shift_name;
END$$

CREATE PROCEDURE sp_start_break(
    IN p_employee_id INT,
    IN p_worked_date DATE,
    IN p_schedule_shift_id INT
)
BEGIN
    INSERT INTO employee_breaks (employee_id, schedule_shift_id, worked_date, break_start, is_active)
    VALUES (p_employee_id, p_schedule_shift_id, p_worked_date, NOW(), 1)
    ON DUPLICATE KEY UPDATE break_start = NOW(), is_active = 1, schedule_shift_id = p_schedule_shift_id;
END$$

CREATE PROCEDURE sp_end_break(IN p_employee_id INT, IN p_worked_date DATE)
BEGIN
    UPDATE employee_breaks
    SET break_end = NOW(), is_active = 0
    WHERE employee_id = p_employee_id AND worked_date = p_worked_date;
END$$

CREATE PROCEDURE sp_get_break_status(IN p_section_id INT, IN p_today DATE)
BEGIN
    SELECT e.full_name AS employee_name,
           sd.shift_name,
           eb.break_start,
           eb.break_end,
           CASE
               WHEN eb.break_end IS NULL THEN TIMESTAMPDIFF(MINUTE, eb.break_start, NOW()) - 30
               ELSE TIMESTAMPDIFF(MINUTE, eb.break_start, eb.break_end) - 30
           END AS delay_minutes,
           CASE
               WHEN eb.is_active = 1 THEN 'On Break'
               WHEN eb.break_end IS NULL THEN 'Not Started'
               ELSE 'Completed'
           END AS status
    FROM employee_breaks eb
    INNER JOIN employees e ON e.id = eb.employee_id
    INNER JOIN user_roles ur ON ur.id = e.user_role_id
    INNER JOIN sections s ON s.id = ur.section_id
    LEFT JOIN schedule_shifts ss ON ss.id = eb.schedule_shift_id
    LEFT JOIN shift_definitions sd ON sd.id = ss.shift_definition_id
    WHERE s.id = p_section_id AND eb.worked_date = p_today
    ORDER BY e.full_name;
END$$

CREATE PROCEDURE sp_performance_report(
    IN p_start_date DATE,
    IN p_end_date DATE,
    IN p_section_id INT,
    IN p_employee_id INT
)
BEGIN
    SELECT e.full_name AS employee_name,
           COUNT(DISTINCT sa.schedule_shift_id) AS days_worked,
           SUM(GREATEST(TIMESTAMPDIFF(MINUTE, eb.break_start, eb.break_end) - 30, 0)) AS total_delay_minutes,
           AVG(GREATEST(TIMESTAMPDIFF(MINUTE, eb.break_start, eb.break_end) - 30, 0)) AS average_delay_minutes
    FROM employees e
    INNER JOIN user_roles ur ON ur.id = e.user_role_id
    INNER JOIN sections s ON s.id = ur.section_id
    LEFT JOIN schedule_assignments sa ON sa.employee_id = e.id
    LEFT JOIN schedule_shifts ss ON ss.id = sa.schedule_shift_id
    LEFT JOIN employee_breaks eb ON eb.employee_id = e.id AND eb.worked_date BETWEEN p_start_date AND p_end_date
    WHERE s.id = p_section_id
      AND (p_employee_id IS NULL OR e.id = p_employee_id)
      AND ss.shift_date BETWEEN p_start_date AND p_end_date
    GROUP BY e.id
    ORDER BY total_delay_minutes ASC;
END$$

CREATE PROCEDURE sp_director_dashboard(IN p_section_id INT, IN p_week_id INT)
BEGIN
    SELECT 'Total Employees' AS label,
           COUNT(DISTINCT e.id) AS value,
           'Active employees in section' AS description
    FROM employees e
    INNER JOIN user_roles ur ON ur.id = e.user_role_id
    WHERE ur.section_id = p_section_id
    UNION ALL
    SELECT 'Approved Requests',
           COUNT(*) AS value,
           'Requests approved for the week' AS description
    FROM shift_requests sr
    INNER JOIN employees e ON e.id = sr.employee_id
    INNER JOIN user_roles ur ON ur.id = e.user_role_id
    WHERE sr.week_id = p_week_id AND ur.section_id = p_section_id AND sr.status = 'APPROVED'
    UNION ALL
    SELECT 'Total Breaks',
           COUNT(*) AS value,
           'Breaks logged for the week' AS description
    FROM employee_breaks eb
    INNER JOIN employees e ON e.id = eb.employee_id
    INNER JOIN user_roles ur ON ur.id = e.user_role_id
    WHERE ur.section_id = p_section_id;
END$$

CREATE PROCEDURE sp_get_employees_by_section(IN p_section_id INT)
BEGIN
    SELECT e.id,
           e.full_name,
           e.employee_code,
           e.seniority_level,
           e.is_senior,
           r.role_name,
           u.email
    FROM employees e
    INNER JOIN user_roles ur ON ur.id = e.user_role_id
    INNER JOIN roles r ON r.id = ur.role_id
    INNER JOIN users u ON u.id = ur.user_id
    WHERE ur.section_id = p_section_id
    ORDER BY e.full_name;
END$$

CREATE PROCEDURE sp_get_available_employees(IN p_section_id INT, IN p_date DATE)
BEGIN
    SELECT e.id,
           e.full_name,
           e.employee_code,
           e.seniority_level
    FROM employees e
    INNER JOIN user_roles ur ON ur.id = e.user_role_id
    LEFT JOIN schedule_assignments sa ON sa.employee_id = e.id
    LEFT JOIN schedule_shifts ss ON ss.id = sa.schedule_shift_id
    WHERE ur.section_id = p_section_id
      AND (ss.shift_date IS NULL OR ss.shift_date <> p_date)
    ORDER BY e.seniority_level DESC;
END$$

DELIMITER ;
