-- =====================================================
-- CLEAN TEST DATA SCRIPT FOR SHIFT SCHEDULER
-- =====================================================
-- This script removes ALL existing data and inserts fresh test data
-- Run this to reset the database to a clean state
-- =====================================================

USE ShiftSchedulerDB;

-- =====================================================
-- STEP 1: DELETE ALL DATA (in correct order to avoid FK violations)
-- =====================================================

-- Disable foreign key checks temporarily for clean deletion
SET FOREIGN_KEY_CHECKS = 0;

-- Delete in reverse dependency order
DELETE FROM notifications;
DELETE FROM employee_breaks;
DELETE FROM schedule_assignments;
DELETE FROM schedule_shifts;
DELETE FROM schedules;
DELETE FROM shift_requirements;
DELETE FROM shift_requests;
DELETE FROM employees;
DELETE FROM user_roles;
DELETE FROM users WHERE username != 'director';
DELETE FROM weeks;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================
-- STEP 2: INSERT WEEKS (for next 5 weeks)
-- =====================================================

SET @current_date = CURDATE();
SET @monday = DATE_SUB(@current_date, INTERVAL WEEKDAY(@current_date) DAY);

INSERT INTO weeks (week_start_date, week_end_date, is_locked_for_requests) VALUES
(@monday, DATE_ADD(@monday, INTERVAL 6 DAY), 0),
(DATE_ADD(@monday, INTERVAL 7 DAY), DATE_ADD(@monday, INTERVAL 13 DAY), 0),
(DATE_ADD(@monday, INTERVAL 14 DAY), DATE_ADD(@monday, INTERVAL 20 DAY), 0),
(DATE_ADD(@monday, INTERVAL 21 DAY), DATE_ADD(@monday, INTERVAL 27 DAY), 0),
(DATE_ADD(@monday, INTERVAL 28 DAY), DATE_ADD(@monday, INTERVAL 34 DAY), 0);

-- =====================================================
-- STEP 3: INSERT USERS AND EMPLOYEES
-- =====================================================

-- Get role IDs
SET @role_director = (SELECT id FROM roles WHERE role_name = 'Director' LIMIT 1);
SET @role_teamleader = (SELECT id FROM roles WHERE role_name = 'Team Leader' LIMIT 1);
SET @role_supervisor = (SELECT id FROM roles WHERE role_name = 'Supervisor' LIMIT 1);
SET @role_senior = (SELECT id FROM roles WHERE role_name = 'Senior' LIMIT 1);
SET @role_employee = (SELECT id FROM roles WHERE role_name = 'Employee' LIMIT 1);

-- Get section IDs
SET @section_app = (SELECT id FROM sections WHERE section_name = 'App After-Sales' LIMIT 1);
SET @section_agent = (SELECT id FROM sections WHERE section_name = 'Agent After-Sales' LIMIT 1);

-- Password hashes
SET @password_hash = '$2y$12$0vDm1eWKGqjOmAtYQ8zjxevMNuAD4tShO/Omzx/j.EId7ALkEagL6'; -- password123
SET @director_password_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'; -- password

-- Ensure Director account exists with correct password
INSERT INTO users (username, password_hash, email, is_active) 
VALUES ('director', @director_password_hash, 'director@company.com', 1)
ON DUPLICATE KEY UPDATE password_hash = @director_password_hash, is_active = 1;

SET @director_user_id = (SELECT id FROM users WHERE username = 'director');

-- Ensure Director has roles for both sections
DELETE FROM user_roles WHERE user_id = @director_user_id;
INSERT INTO user_roles (user_id, role_id, section_id) VALUES
(@director_user_id, @role_director, @section_app),
(@director_user_id, @role_director, @section_agent);

-- Insert Team Leaders (2 per section)
INSERT INTO users (username, password_hash, email) VALUES
('tl_app_001', @password_hash, 'tl_app_001@test.com'),
('tl_app_002', @password_hash, 'tl_app_002@test.com'),
('tl_agent_001', @password_hash, 'tl_agent_001@test.com'),
('tl_agent_002', @password_hash, 'tl_agent_002@test.com');

SET @tl_app_001_id = (SELECT id FROM users WHERE username = 'tl_app_001');
SET @tl_app_002_id = (SELECT id FROM users WHERE username = 'tl_app_002');
SET @tl_agent_001_id = (SELECT id FROM users WHERE username = 'tl_agent_001');
SET @tl_agent_002_id = (SELECT id FROM users WHERE username = 'tl_agent_002');

INSERT INTO user_roles (user_id, role_id, section_id) VALUES
(@tl_app_001_id, @role_teamleader, @section_app),
(@tl_app_002_id, @role_teamleader, @section_app),
(@tl_agent_001_id, @role_teamleader, @section_agent),
(@tl_agent_002_id, @role_teamleader, @section_agent);

-- Insert Supervisors (1 per section)
INSERT INTO users (username, password_hash, email) VALUES
('sv_app_001', @password_hash, 'sv_app_001@test.com'),
('sv_agent_001', @password_hash, 'sv_agent_001@test.com');

SET @sv_app_001_id = (SELECT id FROM users WHERE username = 'sv_app_001');
SET @sv_agent_001_id = (SELECT id FROM users WHERE username = 'sv_agent_001');

INSERT INTO user_roles (user_id, role_id, section_id) VALUES
(@sv_app_001_id, @role_supervisor, @section_app),
(@sv_agent_001_id, @role_supervisor, @section_agent);

-- Insert Seniors (2 per section)
INSERT INTO users (username, password_hash, email) VALUES
('sr_app_001', @password_hash, 'sr_app_001@test.com'),
('sr_app_002', @password_hash, 'sr_app_002@test.com'),
('sr_agent_001', @password_hash, 'sr_agent_001@test.com'),
('sr_agent_002', @password_hash, 'sr_agent_002@test.com');

SET @sr_app_001_id = (SELECT id FROM users WHERE username = 'sr_app_001');
SET @sr_app_002_id = (SELECT id FROM users WHERE username = 'sr_app_002');
SET @sr_agent_001_id = (SELECT id FROM users WHERE username = 'sr_agent_001');
SET @sr_agent_002_id = (SELECT id FROM users WHERE username = 'sr_agent_002');

INSERT INTO user_roles (user_id, role_id, section_id) VALUES
(@sr_app_001_id, @role_senior, @section_app),
(@sr_app_002_id, @role_senior, @section_app),
(@sr_agent_001_id, @role_senior, @section_agent),
(@sr_agent_002_id, @role_senior, @section_agent);

-- Get user_role IDs for seniors
SET @sr_app_001_ur = (SELECT id FROM user_roles WHERE user_id = @sr_app_001_id);
SET @sr_app_002_ur = (SELECT id FROM user_roles WHERE user_id = @sr_app_002_id);
SET @sr_agent_001_ur = (SELECT id FROM user_roles WHERE user_id = @sr_agent_001_id);
SET @sr_agent_002_ur = (SELECT id FROM user_roles WHERE user_id = @sr_agent_002_id);

INSERT INTO employees (user_role_id, employee_code, full_name, email, is_senior) VALUES
(@sr_app_001_ur, 'SR-APP-001', 'Senior App One', 'sr_app_001@test.com', 1),
(@sr_app_002_ur, 'SR-APP-002', 'Senior App Two', 'sr_app_002@test.com', 1),
(@sr_agent_001_ur, 'SR-AGENT-001', 'Senior Agent One', 'sr_agent_001@test.com', 1),
(@sr_agent_002_ur, 'SR-AGENT-002', 'Senior Agent Two', 'sr_agent_002@test.com', 1);

-- Insert Employees (20 per section)
INSERT INTO users (username, password_hash, email) VALUES
('emp_app_001', @password_hash, 'emp_app_001@test.com'),
('emp_app_002', @password_hash, 'emp_app_002@test.com'),
('emp_app_003', @password_hash, 'emp_app_003@test.com'),
('emp_app_004', @password_hash, 'emp_app_004@test.com'),
('emp_app_005', @password_hash, 'emp_app_005@test.com'),
('emp_app_006', @password_hash, 'emp_app_006@test.com'),
('emp_app_007', @password_hash, 'emp_app_007@test.com'),
('emp_app_008', @password_hash, 'emp_app_008@test.com'),
('emp_app_009', @password_hash, 'emp_app_009@test.com'),
('emp_app_010', @password_hash, 'emp_app_010@test.com'),
('emp_app_011', @password_hash, 'emp_app_011@test.com'),
('emp_app_012', @password_hash, 'emp_app_012@test.com'),
('emp_app_013', @password_hash, 'emp_app_013@test.com'),
('emp_app_014', @password_hash, 'emp_app_014@test.com'),
('emp_app_015', @password_hash, 'emp_app_015@test.com'),
('emp_app_016', @password_hash, 'emp_app_016@test.com'),
('emp_app_017', @password_hash, 'emp_app_017@test.com'),
('emp_app_018', @password_hash, 'emp_app_018@test.com'),
('emp_app_019', @password_hash, 'emp_app_019@test.com'),
('emp_app_020', @password_hash, 'emp_app_020@test.com'),
('emp_agent_001', @password_hash, 'emp_agent_001@test.com'),
('emp_agent_002', @password_hash, 'emp_agent_002@test.com'),
('emp_agent_003', @password_hash, 'emp_agent_003@test.com'),
('emp_agent_004', @password_hash, 'emp_agent_004@test.com'),
('emp_agent_005', @password_hash, 'emp_agent_005@test.com'),
('emp_agent_006', @password_hash, 'emp_agent_006@test.com'),
('emp_agent_007', @password_hash, 'emp_agent_007@test.com'),
('emp_agent_008', @password_hash, 'emp_agent_008@test.com'),
('emp_agent_009', @password_hash, 'emp_agent_009@test.com'),
('emp_agent_010', @password_hash, 'emp_agent_010@test.com'),
('emp_agent_011', @password_hash, 'emp_agent_011@test.com'),
('emp_agent_012', @password_hash, 'emp_agent_012@test.com'),
('emp_agent_013', @password_hash, 'emp_agent_013@test.com'),
('emp_agent_014', @password_hash, 'emp_agent_014@test.com'),
('emp_agent_015', @password_hash, 'emp_agent_015@test.com'),
('emp_agent_016', @password_hash, 'emp_agent_016@test.com'),
('emp_agent_017', @password_hash, 'emp_agent_017@test.com'),
('emp_agent_018', @password_hash, 'emp_agent_018@test.com'),
('emp_agent_019', @password_hash, 'emp_agent_019@test.com'),
('emp_agent_020', @password_hash, 'emp_agent_020@test.com');

-- Insert user_roles for employees
INSERT INTO user_roles (user_id, role_id, section_id)
SELECT id, @role_employee, @section_app FROM users WHERE username LIKE 'emp_app_%';

INSERT INTO user_roles (user_id, role_id, section_id)
SELECT id, @role_employee, @section_agent FROM users WHERE username LIKE 'emp_agent_%';

-- Insert employees (using simpler approach without ROW_NUMBER for compatibility)
INSERT INTO employees (user_role_id, employee_code, full_name, email)
SELECT ur.id, CONCAT('EMP-APP-', SUBSTRING(u.username, 9)), 
       CONCAT('Employee App ', SUBSTRING(u.username, 9)),
       u.email
FROM users u
INNER JOIN user_roles ur ON ur.user_id = u.id
WHERE u.username LIKE 'emp_app_%' AND ur.role_id = @role_employee
ORDER BY u.id;

INSERT INTO employees (user_role_id, employee_code, full_name, email)
SELECT ur.id, CONCAT('EMP-AGENT-', SUBSTRING(u.username, 10)), 
       CONCAT('Employee Agent ', SUBSTRING(u.username, 10)),
       u.email
FROM users u
INNER JOIN user_roles ur ON ur.user_id = u.id
WHERE u.username LIKE 'emp_agent_%' AND ur.role_id = @role_employee
ORDER BY u.id;

-- =====================================================
-- STEP 4: INSERT SHIFT REQUESTS (for next week)
-- =====================================================

SET @next_week_id = (SELECT id FROM weeks ORDER BY week_start_date LIMIT 1 OFFSET 1);
SET @pattern_5x2 = (SELECT id FROM schedule_patterns WHERE name = '5x2' LIMIT 1);
SET @shift_am = (SELECT id FROM shift_definitions WHERE shift_name = 'AM Shift' LIMIT 1);
SET @shift_pm = (SELECT id FROM shift_definitions WHERE shift_name = 'PM Shift' LIMIT 1);
SET @shift_mid = (SELECT id FROM shift_definitions WHERE shift_name = 'Mid Shift' LIMIT 1);

-- Get next week start date
SET @next_week_start = (SELECT week_start_date FROM weeks WHERE id = @next_week_id);

-- Insert requests for employees (15 requests per section)
-- Use deterministic approach instead of RAND() for compatibility
INSERT INTO shift_requests (employee_id, week_id, request_date, shift_definition_id, schedule_pattern_id, importance_level, status)
SELECT e.id, @next_week_id, 
       DATE_ADD(@next_week_start, INTERVAL (MOD(e.id, 7)) DAY),
       CASE MOD(e.id, 3)
           WHEN 0 THEN @shift_am
           WHEN 1 THEN @shift_pm
           ELSE @shift_mid
       END,
       @pattern_5x2,
       CASE MOD(e.id, 3)
           WHEN 0 THEN 'LOW'
           WHEN 1 THEN 'MEDIUM'
           ELSE 'HIGH'
       END,
       'PENDING'
FROM employees e
INNER JOIN user_roles ur ON ur.id = e.user_role_id
WHERE ur.section_id = @section_app AND e.is_senior = 0
ORDER BY e.id
LIMIT 15;

INSERT INTO shift_requests (employee_id, week_id, request_date, shift_definition_id, schedule_pattern_id, importance_level, status)
SELECT e.id, @next_week_id, 
       DATE_ADD(@next_week_start, INTERVAL (MOD(e.id, 7)) DAY),
       CASE MOD(e.id, 3)
           WHEN 0 THEN @shift_am
           WHEN 1 THEN @shift_pm
           ELSE @shift_mid
       END,
       @pattern_5x2,
       CASE MOD(e.id, 3)
           WHEN 0 THEN 'LOW'
           WHEN 1 THEN 'MEDIUM'
           ELSE 'HIGH'
       END,
       'PENDING'
FROM employees e
INNER JOIN user_roles ur ON ur.id = e.user_role_id
WHERE ur.section_id = @section_agent AND e.is_senior = 0
ORDER BY e.id
LIMIT 15;

-- =====================================================
-- STEP 5: INSERT SHIFT REQUIREMENTS
-- =====================================================

SET @shift_type_am = (SELECT id FROM shift_types WHERE code = 'AM' LIMIT 1);
SET @shift_type_pm = (SELECT id FROM shift_types WHERE code = 'PM' LIMIT 1);
SET @shift_type_mid = (SELECT id FROM shift_types WHERE code = 'MID' LIMIT 1);

-- Insert requirements for next week
INSERT INTO shift_requirements (week_id, section_id, shift_date, shift_type_id, required_count)
SELECT @next_week_id, @section_app, DATE_ADD(@next_week_start, INTERVAL day_offset DAY), shift_type, required
FROM (
    SELECT 0 as day_offset, @shift_type_am as shift_type, 3 as required UNION ALL
    SELECT 0, @shift_type_pm, 2 UNION ALL
    SELECT 0, @shift_type_mid, 1 UNION ALL
    SELECT 1, @shift_type_am, 3 UNION ALL
    SELECT 1, @shift_type_pm, 2 UNION ALL
    SELECT 1, @shift_type_mid, 1 UNION ALL
    SELECT 2, @shift_type_am, 3 UNION ALL
    SELECT 2, @shift_type_pm, 2 UNION ALL
    SELECT 2, @shift_type_mid, 1 UNION ALL
    SELECT 3, @shift_type_am, 3 UNION ALL
    SELECT 3, @shift_type_pm, 2 UNION ALL
    SELECT 3, @shift_type_mid, 1 UNION ALL
    SELECT 4, @shift_type_am, 3 UNION ALL
    SELECT 4, @shift_type_pm, 2 UNION ALL
    SELECT 4, @shift_type_mid, 1 UNION ALL
    SELECT 5, @shift_type_am, 2 UNION ALL
    SELECT 5, @shift_type_pm, 2 UNION ALL
    SELECT 5, @shift_type_mid, 1 UNION ALL
    SELECT 6, @shift_type_am, 2 UNION ALL
    SELECT 6, @shift_type_pm, 1 UNION ALL
    SELECT 6, @shift_type_mid, 1
) AS reqs;

INSERT INTO shift_requirements (week_id, section_id, shift_date, shift_type_id, required_count)
SELECT @next_week_id, @section_agent, DATE_ADD(@next_week_start, INTERVAL day_offset DAY), shift_type, required
FROM (
    SELECT 0 as day_offset, @shift_type_am as shift_type, 3 as required UNION ALL
    SELECT 0, @shift_type_pm, 2 UNION ALL
    SELECT 0, @shift_type_mid, 1 UNION ALL
    SELECT 1, @shift_type_am, 3 UNION ALL
    SELECT 1, @shift_type_pm, 2 UNION ALL
    SELECT 1, @shift_type_mid, 1 UNION ALL
    SELECT 2, @shift_type_am, 3 UNION ALL
    SELECT 2, @shift_type_pm, 2 UNION ALL
    SELECT 2, @shift_type_mid, 1 UNION ALL
    SELECT 3, @shift_type_am, 3 UNION ALL
    SELECT 3, @shift_type_pm, 2 UNION ALL
    SELECT 3, @shift_type_mid, 1 UNION ALL
    SELECT 4, @shift_type_am, 3 UNION ALL
    SELECT 4, @shift_type_pm, 2 UNION ALL
    SELECT 4, @shift_type_mid, 1 UNION ALL
    SELECT 5, @shift_type_am, 2 UNION ALL
    SELECT 5, @shift_type_pm, 2 UNION ALL
    SELECT 5, @shift_type_mid, 1 UNION ALL
    SELECT 6, @shift_type_am, 2 UNION ALL
    SELECT 6, @shift_type_pm, 1 UNION ALL
    SELECT 6, @shift_type_mid, 1
) AS reqs;

SELECT 'Test data inserted successfully!' AS message;
