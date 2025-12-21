-- =====================================================
-- COMPREHENSIVE TEST DATA FOR SHIFT SCHEDULER
-- =====================================================
-- This file contains test data for thorough application testing
-- Includes: Users, Employees, Team Leaders, Supervisors, Seniors
--          Shift Requests, Schedules, Breaks, and more
-- =====================================================

USE ShiftSchedulerDB;

-- Default password hash for all test users: "password123"
-- Generated with: password_hash('password123', PASSWORD_BCRYPT)
SET @default_password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

-- =====================================================
-- SECTION 1: APP AFTER-SALES
-- =====================================================

-- 2 Team Leaders for App After-Sales (Section 1)
INSERT INTO users (username, password_hash, email) VALUES
('tl_app_001', @default_password, 'tl_app_001@test.com'),
('tl_app_002', @default_password, 'tl_app_002@test.com');

SET @tl_app_001_user = LAST_INSERT_ID() - 1;
SET @tl_app_002_user = LAST_INSERT_ID();

INSERT INTO user_roles (user_id, role_id, section_id) VALUES
(@tl_app_001_user, 2, 1), -- Team Leader role_id = 2
(@tl_app_002_user, 2, 1);

-- 1 Supervisor for App After-Sales
INSERT INTO users (username, password_hash, email) VALUES
('sv_app_001', @default_password, 'sv_app_001@test.com');

SET @sv_app_001_user = LAST_INSERT_ID();

INSERT INTO user_roles (user_id, role_id, section_id) VALUES
(@sv_app_001_user, 3, 1); -- Supervisor role_id = 3

-- 2 Seniors for App After-Sales
INSERT INTO users (username, password_hash, email) VALUES
('sr_app_001', @default_password, 'sr_app_001@test.com'),
('sr_app_002', @default_password, 'sr_app_002@test.com');

SET @sr_app_001_user = LAST_INSERT_ID() - 1;
SET @sr_app_002_user = LAST_INSERT_ID();

INSERT INTO user_roles (user_id, role_id, section_id) VALUES
(@sr_app_001_user, 4, 1), -- Senior role_id = 4
(@sr_app_002_user, 4, 1);

SET @sr_app_001_role = LAST_INSERT_ID() - 1;
SET @sr_app_002_role = LAST_INSERT_ID();

INSERT INTO employees (user_role_id, employee_code, full_name, email, is_senior, seniority_level) VALUES
(@sr_app_001_role, 'SR-APP-001', 'Senior App One', 'sr_app_001@test.com', 1, 5),
(@sr_app_002_role, 'SR-APP-002', 'Senior App Two', 'sr_app_002@test.com', 1, 5);

-- 20 Employees for App After-Sales
INSERT INTO users (username, password_hash, email) VALUES
('emp_app_001', @default_password, 'emp_app_001@test.com'),
('emp_app_002', @default_password, 'emp_app_002@test.com'),
('emp_app_003', @default_password, 'emp_app_003@test.com'),
('emp_app_004', @default_password, 'emp_app_004@test.com'),
('emp_app_005', @default_password, 'emp_app_005@test.com'),
('emp_app_006', @default_password, 'emp_app_006@test.com'),
('emp_app_007', @default_password, 'emp_app_007@test.com'),
('emp_app_008', @default_password, 'emp_app_008@test.com'),
('emp_app_009', @default_password, 'emp_app_009@test.com'),
('emp_app_010', @default_password, 'emp_app_010@test.com'),
('emp_app_011', @default_password, 'emp_app_011@test.com'),
('emp_app_012', @default_password, 'emp_app_012@test.com'),
('emp_app_013', @default_password, 'emp_app_013@test.com'),
('emp_app_014', @default_password, 'emp_app_014@test.com'),
('emp_app_015', @default_password, 'emp_app_015@test.com'),
('emp_app_016', @default_password, 'emp_app_016@test.com'),
('emp_app_017', @default_password, 'emp_app_017@test.com'),
('emp_app_018', @default_password, 'emp_app_018@test.com'),
('emp_app_019', @default_password, 'emp_app_019@test.com'),
('emp_app_020', @default_password, 'emp_app_020@test.com');

SET @emp_app_start_user = LAST_INSERT_ID() - 19;

INSERT INTO user_roles (user_id, role_id, section_id)
SELECT id, 5, 1 FROM users WHERE id >= @emp_app_start_user AND id <= @emp_app_start_user + 19;

INSERT INTO employees (user_role_id, employee_code, full_name, email, is_senior, seniority_level) VALUES
((SELECT id FROM user_roles WHERE section_id = 1 AND role_id = 5 ORDER BY id LIMIT 1 OFFSET 0), 'EMP-APP-001', 'Employee App One', 'emp_app_001@test.com', 0, 1),
((SELECT id FROM user_roles WHERE section_id = 1 AND role_id = 5 ORDER BY id LIMIT 1 OFFSET 1), 'EMP-APP-002', 'Employee App Two', 'emp_app_002@test.com', 0, 2),
((SELECT id FROM user_roles WHERE section_id = 1 AND role_id = 5 ORDER BY id LIMIT 1 OFFSET 2), 'EMP-APP-003', 'Employee App Three', 'emp_app_003@test.com', 0, 1),
((SELECT id FROM user_roles WHERE section_id = 1 AND role_id = 5 ORDER BY id LIMIT 1 OFFSET 3), 'EMP-APP-004', 'Employee App Four', 'emp_app_004@test.com', 0, 3),
((SELECT id FROM user_roles WHERE section_id = 1 AND role_id = 5 ORDER BY id LIMIT 1 OFFSET 4), 'EMP-APP-005', 'Employee App Five', 'emp_app_005@test.com', 0, 2),
((SELECT id FROM user_roles WHERE section_id = 1 AND role_id = 5 ORDER BY id LIMIT 1 OFFSET 5), 'EMP-APP-006', 'Employee App Six', 'emp_app_006@test.com', 0, 1),
((SELECT id FROM user_roles WHERE section_id = 1 AND role_id = 5 ORDER BY id LIMIT 1 OFFSET 6), 'EMP-APP-007', 'Employee App Seven', 'emp_app_007@test.com', 0, 2),
((SELECT id FROM user_roles WHERE section_id = 1 AND role_id = 5 ORDER BY id LIMIT 1 OFFSET 7), 'EMP-APP-008', 'Employee App Eight', 'emp_app_008@test.com', 0, 3),
((SELECT id FROM user_roles WHERE section_id = 1 AND role_id = 5 ORDER BY id LIMIT 1 OFFSET 8), 'EMP-APP-009', 'Employee App Nine', 'emp_app_009@test.com', 0, 1),
((SELECT id FROM user_roles WHERE section_id = 1 AND role_id = 5 ORDER BY id LIMIT 1 OFFSET 9), 'EMP-APP-010', 'Employee App Ten', 'emp_app_010@test.com', 0, 2),
((SELECT id FROM user_roles WHERE section_id = 1 AND role_id = 5 ORDER BY id LIMIT 1 OFFSET 10), 'EMP-APP-011', 'Employee App Eleven', 'emp_app_011@test.com', 0, 3),
((SELECT id FROM user_roles WHERE section_id = 1 AND role_id = 5 ORDER BY id LIMIT 1 OFFSET 11), 'EMP-APP-012', 'Employee App Twelve', 'emp_app_012@test.com', 0, 1),
((SELECT id FROM user_roles WHERE section_id = 1 AND role_id = 5 ORDER BY id LIMIT 1 OFFSET 12), 'EMP-APP-013', 'Employee App Thirteen', 'emp_app_013@test.com', 0, 2),
((SELECT id FROM user_roles WHERE section_id = 1 AND role_id = 5 ORDER BY id LIMIT 1 OFFSET 13), 'EMP-APP-014', 'Employee App Fourteen', 'emp_app_014@test.com', 0, 1),
((SELECT id FROM user_roles WHERE section_id = 1 AND role_id = 5 ORDER BY id LIMIT 1 OFFSET 14), 'EMP-APP-015', 'Employee App Fifteen', 'emp_app_015@test.com', 0, 3),
((SELECT id FROM user_roles WHERE section_id = 1 AND role_id = 5 ORDER BY id LIMIT 1 OFFSET 15), 'EMP-APP-016', 'Employee App Sixteen', 'emp_app_016@test.com', 0, 2),
((SELECT id FROM user_roles WHERE section_id = 1 AND role_id = 5 ORDER BY id LIMIT 1 OFFSET 16), 'EMP-APP-017', 'Employee App Seventeen', 'emp_app_017@test.com', 0, 1),
((SELECT id FROM user_roles WHERE section_id = 1 AND role_id = 5 ORDER BY id LIMIT 1 OFFSET 17), 'EMP-APP-018', 'Employee App Eighteen', 'emp_app_018@test.com', 0, 2),
((SELECT id FROM user_roles WHERE section_id = 1 AND role_id = 5 ORDER BY id LIMIT 1 OFFSET 18), 'EMP-APP-019', 'Employee App Nineteen', 'emp_app_019@test.com', 0, 3),
((SELECT id FROM user_roles WHERE section_id = 1 AND role_id = 5 ORDER BY id LIMIT 1 OFFSET 19), 'EMP-APP-020', 'Employee App Twenty', 'emp_app_020@test.com', 0, 1);

-- =====================================================
-- SECTION 2: AGENT AFTER-SALES
-- =====================================================

-- 2 Team Leaders for Agent After-Sales (Section 2)
INSERT INTO users (username, password_hash, email) VALUES
('tl_agent_001', @default_password, 'tl_agent_001@test.com'),
('tl_agent_002', @default_password, 'tl_agent_002@test.com');

SET @tl_agent_001_user = LAST_INSERT_ID() - 1;
SET @tl_agent_002_user = LAST_INSERT_ID();

INSERT INTO user_roles (user_id, role_id, section_id) VALUES
(@tl_agent_001_user, 2, 2),
(@tl_agent_002_user, 2, 2);

-- 1 Supervisor for Agent After-Sales
INSERT INTO users (username, password_hash, email) VALUES
('sv_agent_001', @default_password, 'sv_agent_001@test.com');

SET @sv_agent_001_user = LAST_INSERT_ID();

INSERT INTO user_roles (user_id, role_id, section_id) VALUES
(@sv_agent_001_user, 3, 2);

-- 2 Seniors for Agent After-Sales
INSERT INTO users (username, password_hash, email) VALUES
('sr_agent_001', @default_password, 'sr_agent_001@test.com'),
('sr_agent_002', @default_password, 'sr_agent_002@test.com');

SET @sr_agent_001_user = LAST_INSERT_ID() - 1;
SET @sr_agent_002_user = LAST_INSERT_ID();

INSERT INTO user_roles (user_id, role_id, section_id) VALUES
(@sr_agent_001_user, 4, 2),
(@sr_agent_002_user, 4, 2);

SET @sr_agent_001_role = LAST_INSERT_ID() - 1;
SET @sr_agent_002_role = LAST_INSERT_ID();

INSERT INTO employees (user_role_id, employee_code, full_name, email, is_senior, seniority_level) VALUES
(@sr_agent_001_role, 'SR-AGT-001', 'Senior Agent One', 'sr_agent_001@test.com', 1, 5),
(@sr_agent_002_role, 'SR-AGT-002', 'Senior Agent Two', 'sr_agent_002@test.com', 1, 5);

-- 20 Employees for Agent After-Sales
INSERT INTO users (username, password_hash, email) VALUES
('emp_agent_001', @default_password, 'emp_agent_001@test.com'),
('emp_agent_002', @default_password, 'emp_agent_002@test.com'),
('emp_agent_003', @default_password, 'emp_agent_003@test.com'),
('emp_agent_004', @default_password, 'emp_agent_004@test.com'),
('emp_agent_005', @default_password, 'emp_agent_005@test.com'),
('emp_agent_006', @default_password, 'emp_agent_006@test.com'),
('emp_agent_007', @default_password, 'emp_agent_007@test.com'),
('emp_agent_008', @default_password, 'emp_agent_008@test.com'),
('emp_agent_009', @default_password, 'emp_agent_009@test.com'),
('emp_agent_010', @default_password, 'emp_agent_010@test.com'),
('emp_agent_011', @default_password, 'emp_agent_011@test.com'),
('emp_agent_012', @default_password, 'emp_agent_012@test.com'),
('emp_agent_013', @default_password, 'emp_agent_013@test.com'),
('emp_agent_014', @default_password, 'emp_agent_014@test.com'),
('emp_agent_015', @default_password, 'emp_agent_015@test.com'),
('emp_agent_016', @default_password, 'emp_agent_016@test.com'),
('emp_agent_017', @default_password, 'emp_agent_017@test.com'),
('emp_agent_018', @default_password, 'emp_agent_018@test.com'),
('emp_agent_019', @default_password, 'emp_agent_019@test.com'),
('emp_agent_020', @default_password, 'emp_agent_020@test.com');

SET @emp_agent_start_user = LAST_INSERT_ID() - 19;

INSERT INTO user_roles (user_id, role_id, section_id)
SELECT id, 5, 2 FROM users WHERE id >= @emp_agent_start_user AND id <= @emp_agent_start_user + 19;

INSERT INTO employees (user_role_id, employee_code, full_name, email, is_senior, seniority_level) VALUES
((SELECT id FROM user_roles WHERE section_id = 2 AND role_id = 5 ORDER BY id LIMIT 1 OFFSET 0), 'EMP-AGT-001', 'Employee Agent One', 'emp_agent_001@test.com', 0, 1),
((SELECT id FROM user_roles WHERE section_id = 2 AND role_id = 5 ORDER BY id LIMIT 1 OFFSET 1), 'EMP-AGT-002', 'Employee Agent Two', 'emp_agent_002@test.com', 0, 2),
((SELECT id FROM user_roles WHERE section_id = 2 AND role_id = 5 ORDER BY id LIMIT 1 OFFSET 2), 'EMP-AGT-003', 'Employee Agent Three', 'emp_agent_003@test.com', 0, 1),
((SELECT id FROM user_roles WHERE section_id = 2 AND role_id = 5 ORDER BY id LIMIT 1 OFFSET 3), 'EMP-AGT-004', 'Employee Agent Four', 'emp_agent_004@test.com', 0, 3),
((SELECT id FROM user_roles WHERE section_id = 2 AND role_id = 5 ORDER BY id LIMIT 1 OFFSET 4), 'EMP-AGT-005', 'Employee Agent Five', 'emp_agent_005@test.com', 0, 2),
((SELECT id FROM user_roles WHERE section_id = 2 AND role_id = 5 ORDER BY id LIMIT 1 OFFSET 5), 'EMP-AGT-006', 'Employee Agent Six', 'emp_agent_006@test.com', 0, 1),
((SELECT id FROM user_roles WHERE section_id = 2 AND role_id = 5 ORDER BY id LIMIT 1 OFFSET 6), 'EMP-AGT-007', 'Employee Agent Seven', 'emp_agent_007@test.com', 0, 2),
((SELECT id FROM user_roles WHERE section_id = 2 AND role_id = 5 ORDER BY id LIMIT 1 OFFSET 7), 'EMP-AGT-008', 'Employee Agent Eight', 'emp_agent_008@test.com', 0, 3),
((SELECT id FROM user_roles WHERE section_id = 2 AND role_id = 5 ORDER BY id LIMIT 1 OFFSET 8), 'EMP-AGT-009', 'Employee Agent Nine', 'emp_agent_009@test.com', 0, 1),
((SELECT id FROM user_roles WHERE section_id = 2 AND role_id = 5 ORDER BY id LIMIT 1 OFFSET 9), 'EMP-AGT-010', 'Employee Agent Ten', 'emp_agent_010@test.com', 0, 2),
((SELECT id FROM user_roles WHERE section_id = 2 AND role_id = 5 ORDER BY id LIMIT 1 OFFSET 10), 'EMP-AGT-011', 'Employee Agent Eleven', 'emp_agent_011@test.com', 0, 3),
((SELECT id FROM user_roles WHERE section_id = 2 AND role_id = 5 ORDER BY id LIMIT 1 OFFSET 11), 'EMP-AGT-012', 'Employee Agent Twelve', 'emp_agent_012@test.com', 0, 1),
((SELECT id FROM user_roles WHERE section_id = 2 AND role_id = 5 ORDER BY id LIMIT 1 OFFSET 12), 'EMP-AGT-013', 'Employee Agent Thirteen', 'emp_agent_013@test.com', 0, 2),
((SELECT id FROM user_roles WHERE section_id = 2 AND role_id = 5 ORDER BY id LIMIT 1 OFFSET 13), 'EMP-AGT-014', 'Employee Agent Fourteen', 'emp_agent_014@test.com', 0, 1),
((SELECT id FROM user_roles WHERE section_id = 2 AND role_id = 5 ORDER BY id LIMIT 1 OFFSET 14), 'EMP-AGT-015', 'Employee Agent Fifteen', 'emp_agent_015@test.com', 0, 3),
((SELECT id FROM user_roles WHERE section_id = 2 AND role_id = 5 ORDER BY id LIMIT 1 OFFSET 15), 'EMP-AGT-016', 'Employee Agent Sixteen', 'emp_agent_016@test.com', 0, 2),
((SELECT id FROM user_roles WHERE section_id = 2 AND role_id = 5 ORDER BY id LIMIT 1 OFFSET 16), 'EMP-AGT-017', 'Employee Agent Seventeen', 'emp_agent_017@test.com', 0, 1),
((SELECT id FROM user_roles WHERE section_id = 2 AND role_id = 5 ORDER BY id LIMIT 1 OFFSET 17), 'EMP-AGT-018', 'Employee Agent Eighteen', 'emp_agent_018@test.com', 0, 2),
((SELECT id FROM user_roles WHERE section_id = 2 AND role_id = 5 ORDER BY id LIMIT 1 OFFSET 18), 'EMP-AGT-019', 'Employee Agent Nineteen', 'emp_agent_019@test.com', 0, 3),
((SELECT id FROM user_roles WHERE section_id = 2 AND role_id = 5 ORDER BY id LIMIT 1 OFFSET 19), 'EMP-AGT-020', 'Employee Agent Twenty', 'emp_agent_020@test.com', 0, 1);

-- =====================================================
-- CREATE WEEKS FOR TESTING
-- =====================================================

-- Create weeks for current and next 4 weeks
SET @current_date = CURDATE();
SET @monday_current = DATE_SUB(@current_date, INTERVAL WEEKDAY(@current_date) DAY);
SET @sunday_current = DATE_ADD(@monday_current, INTERVAL 6 DAY);

INSERT INTO weeks (week_start_date, week_end_date) VALUES
(@monday_current, @sunday_current),
(DATE_ADD(@monday_current, INTERVAL 7 DAY), DATE_ADD(@sunday_current, INTERVAL 7 DAY)),
(DATE_ADD(@monday_current, INTERVAL 14 DAY), DATE_ADD(@sunday_current, INTERVAL 14 DAY)),
(DATE_ADD(@monday_current, INTERVAL 21 DAY), DATE_ADD(@sunday_current, INTERVAL 21 DAY)),
(DATE_ADD(@monday_current, INTERVAL 28 DAY), DATE_ADD(@sunday_current, INTERVAL 28 DAY));

-- =====================================================
-- SHIFT REQUESTS FOR TESTING
-- =====================================================

-- Get current week ID
SET @current_week_id = (SELECT id FROM weeks WHERE week_start_date = @monday_current LIMIT 1);
SET @next_week_id = (SELECT id FROM weeks WHERE week_start_date = DATE_ADD(@monday_current, INTERVAL 7 DAY) LIMIT 1);

-- Get employee IDs for both sections
SET @app_employees = (SELECT GROUP_CONCAT(e.id) FROM employees e 
                      INNER JOIN user_roles ur ON ur.id = e.user_role_id 
                      WHERE ur.section_id = 1 AND ur.role_id = 5 LIMIT 20);
SET @agent_employees = (SELECT GROUP_CONCAT(e.id) FROM employees e 
                         INNER JOIN user_roles ur ON ur.id = e.user_role_id 
                         WHERE ur.section_id = 2 AND ur.role_id = 5 LIMIT 20);

-- Get shift definitions (assuming IDs 1-5 exist)
-- Create shift requests for next week
-- App After-Sales employees
INSERT INTO shift_requests (week_id, employee_id, request_date, shift_definition_id, schedule_pattern_id, importance_level, status, reason, submitted_at)
SELECT 
    @next_week_id,
    e.id,
    DATE_ADD(@monday_current, INTERVAL 7 + FLOOR(RAND() * 7) DAY),
    FLOOR(1 + RAND() * 5),
    FLOOR(1 + RAND() * 3),
    ELT(FLOOR(1 + RAND() * 3), 'LOW', 'NORMAL', 'HIGH'),
    ELT(FLOOR(1 + RAND() * 3), 'PENDING', 'APPROVED', 'DECLINED'),
    CONCAT('Test request for ', e.full_name),
    DATE_SUB(NOW(), INTERVAL FLOOR(RAND() * 7) DAY)
FROM employees e
INNER JOIN user_roles ur ON ur.id = e.user_role_id
WHERE ur.section_id = 1 AND ur.role_id = 5
LIMIT 15;

-- Agent After-Sales employees
INSERT INTO shift_requests (week_id, employee_id, request_date, shift_definition_id, schedule_pattern_id, importance_level, status, reason, submitted_at)
SELECT 
    @next_week_id,
    e.id,
    DATE_ADD(@monday_current, INTERVAL 7 + FLOOR(RAND() * 7) DAY),
    FLOOR(1 + RAND() * 5),
    FLOOR(1 + RAND() * 3),
    ELT(FLOOR(1 + RAND() * 3), 'LOW', 'NORMAL', 'HIGH'),
    ELT(FLOOR(1 + RAND() * 3), 'PENDING', 'APPROVED', 'DECLINED'),
    CONCAT('Test request for ', e.full_name),
    DATE_SUB(NOW(), INTERVAL FLOOR(RAND() * 7) DAY)
FROM employees e
INNER JOIN user_roles ur ON ur.id = e.user_role_id
WHERE ur.section_id = 2 AND ur.role_id = 5
LIMIT 15;

-- =====================================================
-- SCHEDULE ASSIGNMENTS FOR CURRENT WEEK
-- =====================================================

-- Create some schedule assignments for current week
-- Use Senior employees as generators since they have employee records
INSERT INTO schedules (week_id, section_id, generated_by_admin_id, generated_at, status)
VALUES
(@current_week_id, 1, (SELECT e.id FROM employees e INNER JOIN user_roles ur ON ur.id = e.user_role_id WHERE ur.section_id = 1 AND ur.role_id = 4 LIMIT 1), NOW(), 'FINAL'),
(@current_week_id, 2, (SELECT e.id FROM employees e INNER JOIN user_roles ur ON ur.id = e.user_role_id WHERE ur.section_id = 2 AND ur.role_id = 4 LIMIT 1), NOW(), 'FINAL');

SET @schedule_app_id = LAST_INSERT_ID() - 1;
SET @schedule_agent_id = LAST_INSERT_ID();

-- Create schedule shifts for the week (Monday to Sunday)
INSERT INTO schedule_shifts (schedule_id, shift_date, shift_definition_id)
SELECT 
    @schedule_app_id,
    DATE_ADD(@monday_current, INTERVAL day_offset DAY),
    FLOOR(1 + RAND() * 5)
FROM (
    SELECT 0 AS day_offset UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6
) AS days
CROSS JOIN (SELECT 1 AS dummy UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5) AS shifts_per_day
LIMIT 30;

INSERT INTO schedule_shifts (schedule_id, shift_date, shift_definition_id)
SELECT 
    @schedule_agent_id,
    DATE_ADD(@monday_current, INTERVAL day_offset DAY),
    FLOOR(1 + RAND() * 5)
FROM (
    SELECT 0 AS day_offset UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6
) AS days
CROSS JOIN (SELECT 1 AS dummy UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5) AS shifts_per_day
LIMIT 30;

-- Assign employees to some shifts
INSERT INTO schedule_assignments (schedule_shift_id, employee_id, assignment_source, notes)
SELECT 
    ss.id,
    e.id,
    ELT(FLOOR(1 + RAND() * 3), 'AUTO', 'MANUAL', 'REQUEST'),
    CONCAT('Test assignment for ', e.full_name)
FROM schedule_shifts ss
INNER JOIN schedules s ON s.id = ss.schedule_id
INNER JOIN user_roles ur ON ur.section_id = s.section_id AND ur.role_id = 5
INNER JOIN employees e ON e.user_role_id = ur.id
WHERE ss.schedule_id IN (@schedule_app_id, @schedule_agent_id)
LIMIT 40;

-- =====================================================
-- EMPLOYEE BREAKS FOR TODAY
-- =====================================================

-- Create some active breaks for today
INSERT INTO employee_breaks (employee_id, worked_date, break_start, break_end, delay_minutes, is_active)
SELECT 
    e.id,
    CURDATE(),
    DATE_SUB(NOW(), INTERVAL FLOOR(30 + RAND() * 60) MINUTE),
    NULL,
    FLOOR(RAND() * 10),
    1
FROM employees e
INNER JOIN user_roles ur ON ur.id = e.user_role_id
WHERE ur.role_id IN (4, 5) -- Seniors and Employees
LIMIT 5;

-- Create some completed breaks
INSERT INTO employee_breaks (employee_id, worked_date, break_start, break_end, delay_minutes, is_active)
SELECT 
    e.id,
    CURDATE(),
    DATE_SUB(NOW(), INTERVAL FLOOR(120 + RAND() * 180) MINUTE),
    DATE_SUB(NOW(), INTERVAL FLOOR(60 + RAND() * 120) MINUTE),
    FLOOR(RAND() * 15),
    0
FROM employees e
INNER JOIN user_roles ur ON ur.id = e.user_role_id
WHERE ur.role_id IN (4, 5)
LIMIT 10;

-- =====================================================
-- SHIFT REQUIREMENTS FOR NEXT WEEK
-- =====================================================

-- Create shift requirements for next week
INSERT INTO shift_requirements (week_id, section_id, shift_date, shift_type_id, required_count)
SELECT 
    @next_week_id,
    section_id,
    DATE_ADD(@monday_current, INTERVAL 7 + day_offset DAY),
    shift_type_id,
    FLOOR(3 + RAND() * 5)
FROM (
    SELECT 1 AS section_id UNION SELECT 2
) AS sections
CROSS JOIN (
    SELECT 0 AS day_offset UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6
) AS days
CROSS JOIN (
    SELECT id AS shift_type_id FROM shift_types LIMIT 5
) AS shifts
LIMIT 70;

-- =====================================================
-- NOTIFICATIONS FOR TESTING
-- =====================================================

-- Create some notifications for employees
INSERT INTO notifications (user_id, type, title, body, is_read)
SELECT 
    u.id,
    ELT(FLOOR(1 + RAND() * 3), 'SHIFT_REMINDER', 'SCHEDULE_PUBLISHED', 'REQUEST_STATUS'),
    ELT(FLOOR(1 + RAND() * 3), 'Shift Reminder', 'Schedule Published', 'Request Status Updated'),
    CONCAT('Test notification for ', u.username),
    FLOOR(RAND() * 2)
FROM users u
INNER JOIN user_roles ur ON ur.user_id = u.id
WHERE ur.role_id IN (4, 5)
LIMIT 20;

-- =====================================================
-- SUMMARY
-- =====================================================
-- Test Data Created:
-- - 2 Sections (App After-Sales, Agent After-Sales)
-- - 4 Team Leaders (2 per section)
-- - 2 Supervisors (1 per section)
-- - 4 Seniors (2 per section)
-- - 40 Employees (20 per section)
-- - 5 Weeks of data
-- - 30 Shift Requests (15 per section)
-- - 2 Schedules (1 per section)
-- - 60 Schedule Shifts
-- - 40 Schedule Assignments
-- - 15 Employee Breaks
-- - 70 Shift Requirements
-- - 20 Notifications
-- =====================================================

