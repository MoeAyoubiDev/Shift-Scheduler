-- =====================================================
-- Migration 002: Add company_id to All Tables
-- =====================================================
-- This migration adds company_id foreign key to all
-- tables that need multi-tenant isolation
-- =====================================================

USE ShiftSchedulerDB;

-- Add company_id to sections (sections are now company-specific)
ALTER TABLE sections 
ADD COLUMN company_id INT NULL AFTER id,
ADD FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
ADD INDEX idx_company (company_id);

-- Update unique constraint to include company_id
ALTER TABLE sections 
DROP INDEX section_name,
ADD UNIQUE KEY unique_section_company (section_name, company_id);

-- Add company_id to users
ALTER TABLE users 
ADD COLUMN company_id INT NULL AFTER id,
ADD FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
ADD INDEX idx_company (company_id);

-- Update unique constraint for username to be company-scoped
ALTER TABLE users 
DROP INDEX username,
ADD UNIQUE KEY unique_username_company (username, company_id);

-- Add company_id to weeks (weeks are company-specific)
ALTER TABLE weeks 
ADD COLUMN company_id INT NULL AFTER id,
ADD FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
ADD INDEX idx_company (company_id);

-- Update unique constraint
ALTER TABLE weeks 
DROP INDEX unique_week_start,
ADD UNIQUE KEY unique_week_company (week_start_date, company_id);

-- Add company_id to shift_requirements
ALTER TABLE shift_requirements 
ADD COLUMN company_id INT NULL AFTER id,
ADD FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
ADD INDEX idx_company (company_id);

-- Add company_id to schedules
ALTER TABLE schedules 
ADD COLUMN company_id INT NULL AFTER id,
ADD FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
ADD INDEX idx_company (company_id);

-- Update unique constraint
ALTER TABLE schedules 
DROP INDEX unique_schedule_week_section,
ADD UNIQUE KEY unique_schedule_company_week_section (week_id, section_id, company_id);

-- Add company_id to notifications
ALTER TABLE notifications 
ADD COLUMN company_id INT NULL AFTER id,
ADD FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
ADD INDEX idx_company (company_id);

-- Note: user_roles, employees, shift_requests, schedule_assignments, employee_breaks
-- inherit company_id through their foreign key relationships (users -> company_id)

