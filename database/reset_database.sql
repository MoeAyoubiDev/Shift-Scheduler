-- =====================================================
-- RESET DATABASE - REMOVE ALL DATA AND INSERT BASIC DATA
-- =====================================================
-- This script completely resets the database:
-- 1. Removes ALL existing data
-- 2. Inserts fresh basic test data
-- =====================================================

USE ShiftSchedulerDB;

-- =====================================================
-- STEP 1: DELETE ALL DATA
-- =====================================================

SET FOREIGN_KEY_CHECKS = 0;

-- Delete all data in reverse dependency order
DELETE FROM notifications;
DELETE FROM employee_breaks;
DELETE FROM schedule_assignments;
DELETE FROM schedule_shifts;
DELETE FROM schedules;
DELETE FROM shift_requirements;
DELETE FROM shift_requests;
DELETE FROM employees;
DELETE FROM user_roles;
DELETE FROM users;
DELETE FROM weeks;

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================
-- STEP 2: INSERT WEEKS (Current week + next 4 weeks)
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
-- STEP 3: INSERT BASIC USERS AND EMPLOYEES
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
-- password123 for all users except director
SET @password_hash = '$2y$12$0vDm1eWKGqjOmAtYQ8zjxevMNuAD4tShO/Omzx/j.EId7ALkEagL6';
-- password for director
SET @director_password_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

-- =====================================================
-- DIRECTOR (Access to both sections)
-- =====================================================

INSERT INTO users (username, password_hash, email, is_active) 
VALUES ('director', @director_password_hash, 'director@company.com', 1);

SET @director_user_id = LAST_INSERT_ID();

INSERT INTO user_roles (user_id, role_id, section_id) VALUES
(@director_user_id, @role_director, @section_app),
(@director_user_id, @role_director, @section_agent);

-- =====================================================
-- TEAM LEADERS (2 per section = 4 total)
-- =====================================================

INSERT INTO users (username, password_hash, email, is_active) VALUES
('tl_app_001', @password_hash, 'tl_app_001@company.com', 1),
('tl_app_002', @password_hash, 'tl_app_002@company.com', 1),
('tl_agent_001', @password_hash, 'tl_agent_001@company.com', 1),
('tl_agent_002', @password_hash, 'tl_agent_002@company.com', 1);

SET @tl_app_001_id = (SELECT id FROM users WHERE username = 'tl_app_001');
SET @tl_app_002_id = (SELECT id FROM users WHERE username = 'tl_app_002');
SET @tl_agent_001_id = (SELECT id FROM users WHERE username = 'tl_agent_001');
SET @tl_agent_002_id = (SELECT id FROM users WHERE username = 'tl_agent_002');

INSERT INTO user_roles (user_id, role_id, section_id) VALUES
(@tl_app_001_id, @role_teamleader, @section_app),
(@tl_app_002_id, @role_teamleader, @section_app),
(@tl_agent_001_id, @role_teamleader, @section_agent),
(@tl_agent_002_id, @role_teamleader, @section_agent);

-- =====================================================
-- SUPERVISORS (1 per section = 2 total)
-- =====================================================

INSERT INTO users (username, password_hash, email, is_active) VALUES
('sv_app_001', @password_hash, 'sv_app_001@company.com', 1),
('sv_agent_001', @password_hash, 'sv_agent_001@company.com', 1);

SET @sv_app_001_id = (SELECT id FROM users WHERE username = 'sv_app_001');
SET @sv_agent_001_id = (SELECT id FROM users WHERE username = 'sv_agent_001');

INSERT INTO user_roles (user_id, role_id, section_id) VALUES
(@sv_app_001_id, @role_supervisor, @section_app),
(@sv_agent_001_id, @role_supervisor, @section_agent);

-- =====================================================
-- SENIORS (2 per section = 4 total)
-- =====================================================

INSERT INTO users (username, password_hash, email, is_active) VALUES
('senior_app_001', @password_hash, 'senior_app_001@company.com', 1),
('senior_app_002', @password_hash, 'senior_app_002@company.com', 1),
('senior_agent_001', @password_hash, 'senior_agent_001@company.com', 1),
('senior_agent_002', @password_hash, 'senior_agent_002@company.com', 1);

SET @senior_app_001_id = (SELECT id FROM users WHERE username = 'senior_app_001');
SET @senior_app_002_id = (SELECT id FROM users WHERE username = 'senior_app_002');
SET @senior_agent_001_id = (SELECT id FROM users WHERE username = 'senior_agent_001');
SET @senior_agent_002_id = (SELECT id FROM users WHERE username = 'senior_agent_002');

INSERT INTO user_roles (user_id, role_id, section_id) VALUES
(@senior_app_001_id, @role_senior, @section_app),
(@senior_app_002_id, @role_senior, @section_app),
(@senior_agent_001_id, @role_senior, @section_agent),
(@senior_agent_002_id, @role_senior, @section_agent);

-- =====================================================
-- EMPLOYEES (10 per section = 20 total)
-- =====================================================

INSERT INTO users (username, password_hash, email, is_active) VALUES
-- App After-Sales Employees
('emp_app_001', @password_hash, 'emp_app_001@company.com', 1),
('emp_app_002', @password_hash, 'emp_app_002@company.com', 1),
('emp_app_003', @password_hash, 'emp_app_003@company.com', 1),
('emp_app_004', @password_hash, 'emp_app_004@company.com', 1),
('emp_app_005', @password_hash, 'emp_app_005@company.com', 1),
('emp_app_006', @password_hash, 'emp_app_006@company.com', 1),
('emp_app_007', @password_hash, 'emp_app_007@company.com', 1),
('emp_app_008', @password_hash, 'emp_app_008@company.com', 1),
('emp_app_009', @password_hash, 'emp_app_009@company.com', 1),
('emp_app_010', @password_hash, 'emp_app_010@company.com', 1),
-- Agent After-Sales Employees
('emp_agent_001', @password_hash, 'emp_agent_001@company.com', 1),
('emp_agent_002', @password_hash, 'emp_agent_002@company.com', 1),
('emp_agent_003', @password_hash, 'emp_agent_003@company.com', 1),
('emp_agent_004', @password_hash, 'emp_agent_004@company.com', 1),
('emp_agent_005', @password_hash, 'emp_agent_005@company.com', 1),
('emp_agent_006', @password_hash, 'emp_agent_006@company.com', 1),
('emp_agent_007', @password_hash, 'emp_agent_007@company.com', 1),
('emp_agent_008', @password_hash, 'emp_agent_008@company.com', 1),
('emp_agent_009', @password_hash, 'emp_agent_009@company.com', 1),
('emp_agent_010', @password_hash, 'emp_agent_010@company.com', 1);

-- Get employee user IDs and create employees
SET @emp_app_001_id = (SELECT id FROM users WHERE username = 'emp_app_001');
SET @emp_app_002_id = (SELECT id FROM users WHERE username = 'emp_app_002');
SET @emp_app_003_id = (SELECT id FROM users WHERE username = 'emp_app_003');
SET @emp_app_004_id = (SELECT id FROM users WHERE username = 'emp_app_004');
SET @emp_app_005_id = (SELECT id FROM users WHERE username = 'emp_app_005');
SET @emp_app_006_id = (SELECT id FROM users WHERE username = 'emp_app_006');
SET @emp_app_007_id = (SELECT id FROM users WHERE username = 'emp_app_007');
SET @emp_app_008_id = (SELECT id FROM users WHERE username = 'emp_app_008');
SET @emp_app_009_id = (SELECT id FROM users WHERE username = 'emp_app_009');
SET @emp_app_010_id = (SELECT id FROM users WHERE username = 'emp_app_010');
SET @emp_agent_001_id = (SELECT id FROM users WHERE username = 'emp_agent_001');
SET @emp_agent_002_id = (SELECT id FROM users WHERE username = 'emp_agent_002');
SET @emp_agent_003_id = (SELECT id FROM users WHERE username = 'emp_agent_003');
SET @emp_agent_004_id = (SELECT id FROM users WHERE username = 'emp_agent_004');
SET @emp_agent_005_id = (SELECT id FROM users WHERE username = 'emp_agent_005');
SET @emp_agent_006_id = (SELECT id FROM users WHERE username = 'emp_agent_006');
SET @emp_agent_007_id = (SELECT id FROM users WHERE username = 'emp_agent_007');
SET @emp_agent_008_id = (SELECT id FROM users WHERE username = 'emp_agent_008');
SET @emp_agent_009_id = (SELECT id FROM users WHERE username = 'emp_agent_009');
SET @emp_agent_010_id = (SELECT id FROM users WHERE username = 'emp_agent_010');

-- Create user roles for employees
INSERT INTO user_roles (user_id, role_id, section_id) VALUES
-- App After-Sales Employees
(@emp_app_001_id, @role_employee, @section_app),
(@emp_app_002_id, @role_employee, @section_app),
(@emp_app_003_id, @role_employee, @section_app),
(@emp_app_004_id, @role_employee, @section_app),
(@emp_app_005_id, @role_employee, @section_app),
(@emp_app_006_id, @role_employee, @section_app),
(@emp_app_007_id, @role_employee, @section_app),
(@emp_app_008_id, @role_employee, @section_app),
(@emp_app_009_id, @role_employee, @section_app),
(@emp_app_010_id, @role_employee, @section_app),
-- Agent After-Sales Employees
(@emp_agent_001_id, @role_employee, @section_agent),
(@emp_agent_002_id, @role_employee, @section_agent),
(@emp_agent_003_id, @role_employee, @section_agent),
(@emp_agent_004_id, @role_employee, @section_agent),
(@emp_agent_005_id, @role_employee, @section_agent),
(@emp_agent_006_id, @role_employee, @section_agent),
(@emp_agent_007_id, @role_employee, @section_agent),
(@emp_agent_008_id, @role_employee, @section_agent),
(@emp_agent_009_id, @role_employee, @section_agent),
(@emp_agent_010_id, @role_employee, @section_agent);

-- Get user_role IDs for employees and create employee records
SET @emp_app_001_ur = (SELECT id FROM user_roles WHERE user_id = @emp_app_001_id);
SET @emp_app_002_ur = (SELECT id FROM user_roles WHERE user_id = @emp_app_002_id);
SET @emp_app_003_ur = (SELECT id FROM user_roles WHERE user_id = @emp_app_003_id);
SET @emp_app_004_ur = (SELECT id FROM user_roles WHERE user_id = @emp_app_004_id);
SET @emp_app_005_ur = (SELECT id FROM user_roles WHERE user_id = @emp_app_005_id);
SET @emp_app_006_ur = (SELECT id FROM user_roles WHERE user_id = @emp_app_006_id);
SET @emp_app_007_ur = (SELECT id FROM user_roles WHERE user_id = @emp_app_007_id);
SET @emp_app_008_ur = (SELECT id FROM user_roles WHERE user_id = @emp_app_008_id);
SET @emp_app_009_ur = (SELECT id FROM user_roles WHERE user_id = @emp_app_009_id);
SET @emp_app_010_ur = (SELECT id FROM user_roles WHERE user_id = @emp_app_010_id);
SET @emp_agent_001_ur = (SELECT id FROM user_roles WHERE user_id = @emp_agent_001_id);
SET @emp_agent_002_ur = (SELECT id FROM user_roles WHERE user_id = @emp_agent_002_id);
SET @emp_agent_003_ur = (SELECT id FROM user_roles WHERE user_id = @emp_agent_003_id);
SET @emp_agent_004_ur = (SELECT id FROM user_roles WHERE user_id = @emp_agent_004_id);
SET @emp_agent_005_ur = (SELECT id FROM user_roles WHERE user_id = @emp_agent_005_id);
SET @emp_agent_006_ur = (SELECT id FROM user_roles WHERE user_id = @emp_agent_006_id);
SET @emp_agent_007_ur = (SELECT id FROM user_roles WHERE user_id = @emp_agent_007_id);
SET @emp_agent_008_ur = (SELECT id FROM user_roles WHERE user_id = @emp_agent_008_id);
SET @emp_agent_009_ur = (SELECT id FROM user_roles WHERE user_id = @emp_agent_009_id);
SET @emp_agent_010_ur = (SELECT id FROM user_roles WHERE user_id = @emp_agent_010_id);

-- Create employee records directly
INSERT INTO employees (user_role_id, employee_code, full_name, email, is_senior, seniority_level) VALUES
-- App After-Sales Employees
(@emp_app_001_ur, 'APP001', 'Employee App 001', 'emp_app_001@company.com', 0, 0),
(@emp_app_002_ur, 'APP002', 'Employee App 002', 'emp_app_002@company.com', 0, 0),
(@emp_app_003_ur, 'APP003', 'Employee App 003', 'emp_app_003@company.com', 0, 0),
(@emp_app_004_ur, 'APP004', 'Employee App 004', 'emp_app_004@company.com', 0, 0),
(@emp_app_005_ur, 'APP005', 'Employee App 005', 'emp_app_005@company.com', 0, 0),
(@emp_app_006_ur, 'APP006', 'Employee App 006', 'emp_app_006@company.com', 0, 0),
(@emp_app_007_ur, 'APP007', 'Employee App 007', 'emp_app_007@company.com', 0, 0),
(@emp_app_008_ur, 'APP008', 'Employee App 008', 'emp_app_008@company.com', 0, 0),
(@emp_app_009_ur, 'APP009', 'Employee App 009', 'emp_app_009@company.com', 0, 0),
(@emp_app_010_ur, 'APP010', 'Employee App 010', 'emp_app_010@company.com', 0, 0),
-- Agent After-Sales Employees
(@emp_agent_001_ur, 'AGT001', 'Employee Agent 001', 'emp_agent_001@company.com', 0, 0),
(@emp_agent_002_ur, 'AGT002', 'Employee Agent 002', 'emp_agent_002@company.com', 0, 0),
(@emp_agent_003_ur, 'AGT003', 'Employee Agent 003', 'emp_agent_003@company.com', 0, 0),
(@emp_agent_004_ur, 'AGT004', 'Employee Agent 004', 'emp_agent_004@company.com', 0, 0),
(@emp_agent_005_ur, 'AGT005', 'Employee Agent 005', 'emp_agent_005@company.com', 0, 0),
(@emp_agent_006_ur, 'AGT006', 'Employee Agent 006', 'emp_agent_006@company.com', 0, 0),
(@emp_agent_007_ur, 'AGT007', 'Employee Agent 007', 'emp_agent_007@company.com', 0, 0),
(@emp_agent_008_ur, 'AGT008', 'Employee Agent 008', 'emp_agent_008@company.com', 0, 0),
(@emp_agent_009_ur, 'AGT009', 'Employee Agent 009', 'emp_agent_009@company.com', 0, 0),
(@emp_agent_010_ur, 'AGT010', 'Employee Agent 010', 'emp_agent_010@company.com', 0, 0);

-- =====================================================
-- SUMMARY
-- =====================================================
-- Total Users Created:
-- - 1 Director (access to both sections)
-- - 4 Team Leaders (2 per section)
-- - 2 Supervisors (1 per section)
-- - 4 Seniors (2 per section)
-- - 20 Employees (10 per section)
-- Total: 31 users
-- 
-- Weeks Created: 5 weeks (current + next 4)
-- 
-- All passwords: "password123" (except director: "password")
-- =====================================================

SELECT 'Database reset completed successfully!' AS Status;
SELECT COUNT(*) AS 'Total Users' FROM users;
SELECT COUNT(*) AS 'Total Employees' FROM employees;
SELECT COUNT(*) AS 'Total Weeks' FROM weeks;

