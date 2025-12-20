-- Initialize database with sample data
USE ShiftSchedulerDB;

-- Create a sample director user (password: director123)
-- Password hash for 'director123'
INSERT INTO users (username, password_hash, email) VALUES
('director', '$2y$12$NtlKf44L3CaGSrdi6WxkN..TN4StFjW3yLOO1lhdCbMFd4Umx8zRK', 'director@example.com')
ON DUPLICATE KEY UPDATE username=username;

-- Assign director role to both sections
INSERT INTO user_roles (user_id, role_id, section_id)
SELECT u.id, r.id, s.id
FROM users u, roles r, sections s
WHERE u.username = 'director' AND r.role_name = 'Director'
ON DUPLICATE KEY UPDATE user_id=user_id;

-- Create a sample team leader user (password: teamleader123)
-- Password hash for 'teamleader123'
INSERT INTO users (username, password_hash, email) VALUES
('teamleader', '$2y$12$tq1pqr5GYICX4jlQVzmgw.wVIuN6f4Xpthza3SDymd7fx2EGAdPsG', 'teamleader@example.com')
ON DUPLICATE KEY UPDATE username=username;

-- Get App After-Sales section ID and Team Leader role ID
SET @section_id = (SELECT id FROM sections WHERE section_name = 'App After-Sales' LIMIT 1);
SET @role_id = (SELECT id FROM roles WHERE role_name = 'Team Leader' LIMIT 1);
SET @user_id = (SELECT id FROM users WHERE username = 'teamleader' LIMIT 1);

-- Assign team leader role
INSERT INTO user_roles (user_id, role_id, section_id) VALUES
(@user_id, @role_id, @section_id)
ON DUPLICATE KEY UPDATE user_id=user_id;

-- Create employee for team leader
INSERT INTO employees (user_role_id, employee_code, full_name, email, is_senior, seniority_level)
SELECT ur.id, 'TL001', 'Team Leader', 'teamleader@example.com', 0, 10
FROM user_roles ur
WHERE ur.user_id = @user_id AND ur.role_id = @role_id
LIMIT 1
ON DUPLICATE KEY UPDATE employee_code=employee_code;

