-- ============================================================
-- Migration: Remove Sections, Enforce Multi-Tenant Isolation
-- ============================================================
-- This migration:
-- 1. Removes section_id from all tables
-- 2. Drops sections table
-- 3. Updates roles to only 3: Admin, Team Leader, Employee
-- 4. Ensures all data is scoped by company_id
-- 5. Updates all foreign keys and constraints
-- ============================================================

USE ShiftSchedulerDB;

-- ===============================
-- STEP 1: Update Roles
-- ===============================
-- Remove old roles, keep only: Admin, Team Leader, Employee
DELETE FROM roles WHERE role_name NOT IN ('Admin', 'Team Leader', 'Employee');

-- Update role names if they exist with different names
UPDATE roles SET role_name = 'Admin', description = 'Company owner and administrator' WHERE role_name IN ('Director', 'Supervisor');
UPDATE roles SET role_name = 'Team Leader' WHERE role_name = 'Team Leader';
UPDATE roles SET role_name = 'Employee' WHERE role_name = 'Employee';

-- Insert roles if they don't exist
INSERT IGNORE INTO roles (role_name, description) VALUES
    ('Admin', 'Company owner and administrator'),
    ('Team Leader', 'Full scheduling and employee management'),
    ('Employee', 'Shift requests and schedule access');

-- ===============================
-- STEP 2: Remove section_id from user_roles
-- ===============================
-- Add company_id to user_roles if it doesn't exist
ALTER TABLE user_roles 
ADD COLUMN company_id INT NULL AFTER role_id,
ADD INDEX idx_company (company_id);

-- Populate company_id from users table
UPDATE user_roles ur
INNER JOIN users u ON u.id = ur.user_id
SET ur.company_id = u.company_id
WHERE ur.company_id IS NULL;

-- Make company_id NOT NULL after population
ALTER TABLE user_roles 
MODIFY COLUMN company_id INT NOT NULL,
ADD FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE;

-- Drop section_id foreign key and column
ALTER TABLE user_roles 
DROP FOREIGN KEY user_roles_ibfk_3,
DROP COLUMN section_id,
DROP INDEX unique_user_role_section,
ADD UNIQUE KEY unique_user_role_company (user_id, role_id, company_id);

-- ===============================
-- STEP 3: Remove section_id from shift_requirements
-- ===============================
-- Drop section_id foreign key and column
ALTER TABLE shift_requirements 
DROP FOREIGN KEY shift_requirements_ibfk_3,
DROP COLUMN section_id,
DROP INDEX unique_shift_requirement,
ADD UNIQUE KEY unique_shift_requirement (week_id, shift_date, shift_type_id, company_id);

-- Ensure company_id is NOT NULL
ALTER TABLE shift_requirements 
MODIFY COLUMN company_id INT NOT NULL;

-- ===============================
-- STEP 4: Remove section_id from schedules
-- ===============================
-- Drop section_id foreign key and column
ALTER TABLE schedules 
DROP FOREIGN KEY schedules_ibfk_3,
DROP COLUMN section_id,
DROP INDEX unique_schedule_company_week_section,
ADD UNIQUE KEY unique_schedule_company_week (week_id, company_id);

-- Ensure company_id is NOT NULL
ALTER TABLE schedules 
MODIFY COLUMN company_id INT NOT NULL;

-- ===============================
-- STEP 5: Drop sections table
-- ===============================
DROP TABLE IF EXISTS sections;

-- ===============================
-- STEP 6: Update employees table
-- ===============================
-- Add company_id directly to employees for easier queries
ALTER TABLE employees 
ADD COLUMN company_id INT NULL AFTER id,
ADD INDEX idx_company (company_id);

-- Populate company_id from user_roles -> users
UPDATE employees e
INNER JOIN user_roles ur ON ur.id = e.user_role_id
INNER JOIN users u ON u.id = ur.user_id
SET e.company_id = u.company_id
WHERE e.company_id IS NULL;

-- Make company_id NOT NULL after population
ALTER TABLE employees 
MODIFY COLUMN company_id INT NOT NULL,
ADD FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE;

-- ===============================
-- STEP 7: Add company_id to shift_requests if missing
-- ===============================
ALTER TABLE shift_requests 
ADD COLUMN company_id INT NULL AFTER id,
ADD INDEX idx_company (company_id);

-- Populate company_id from employees
UPDATE shift_requests sr
INNER JOIN employees e ON e.id = sr.employee_id
SET sr.company_id = e.company_id
WHERE sr.company_id IS NULL;

-- Make company_id NOT NULL
ALTER TABLE shift_requests 
MODIFY COLUMN company_id INT NOT NULL,
ADD FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE;

-- ===============================
-- STEP 8: Add company_id to employee_breaks if missing
-- ===============================
ALTER TABLE employee_breaks 
ADD COLUMN company_id INT NULL AFTER id,
ADD INDEX idx_company (company_id);

-- Populate company_id from employees
UPDATE employee_breaks eb
INNER JOIN employees e ON e.id = eb.employee_id
SET eb.company_id = e.company_id
WHERE eb.company_id IS NULL;

-- Make company_id NOT NULL
ALTER TABLE employee_breaks 
MODIFY COLUMN company_id INT NOT NULL,
ADD FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE;

-- ===============================
-- STEP 9: Add company_id to schedule_shifts if missing
-- ===============================
ALTER TABLE schedule_shifts 
ADD COLUMN company_id INT NULL AFTER id,
ADD INDEX idx_company (company_id);

-- Populate company_id from schedules
UPDATE schedule_shifts ss
INNER JOIN schedules s ON s.id = ss.schedule_id
SET ss.company_id = s.company_id
WHERE ss.company_id IS NULL;

-- Make company_id NOT NULL
ALTER TABLE schedule_shifts 
MODIFY COLUMN company_id INT NOT NULL,
ADD FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE;

-- ===============================
-- STEP 10: Add company_id to schedule_assignments if missing
-- ===============================
ALTER TABLE schedule_assignments 
ADD COLUMN company_id INT NULL AFTER id,
ADD INDEX idx_company (company_id);

-- Populate company_id from schedule_shifts
UPDATE schedule_assignments sa
INNER JOIN schedule_shifts ss ON ss.id = sa.schedule_shift_id
SET sa.company_id = ss.company_id
WHERE sa.company_id IS NULL;

-- Make company_id NOT NULL
ALTER TABLE schedule_assignments 
MODIFY COLUMN company_id INT NOT NULL,
ADD FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE;

-- ===============================
-- COMPLETION
-- ===============================
SELECT 'Migration completed: Sections removed, multi-tenant isolation enforced' AS status;

