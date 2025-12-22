-- =====================================================
-- Migration 002: Add company_id to All Tables (SAFE VERSION)
-- =====================================================
-- This migration safely adds company_id foreign key to all
-- tables that need multi-tenant isolation
-- Uses IF NOT EXISTS checks to prevent duplicate column errors
-- =====================================================

USE ShiftSchedulerDB;

-- Add company_id to sections (sections are now company-specific)
-- Check if column exists first
SET @dbname = DATABASE();
SET @tablename = 'sections';
SET @columnname = 'company_id';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT 1', -- Column exists, do nothing
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN company_id INT NULL AFTER id, ADD FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE, ADD INDEX idx_company (company_id)')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Update unique constraint to include company_id (only if company_id was just added)
-- First drop old constraint if it exists
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (INDEX_NAME = 'section_name')
  ) > 0,
  CONCAT('ALTER TABLE ', @tablename, ' DROP INDEX section_name'),
  'SELECT 1'
));
PREPARE dropIndex FROM @preparedStatement;
EXECUTE dropIndex;
DEALLOCATE PREPARE dropIndex;

-- Add new unique constraint
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (INDEX_NAME = 'unique_section_company')
  ) > 0,
  'SELECT 1', -- Constraint exists
  CONCAT('ALTER TABLE ', @tablename, ' ADD UNIQUE KEY unique_section_company (section_name, company_id)')
));
PREPARE addUnique FROM @preparedStatement;
EXECUTE addUnique;
DEALLOCATE PREPARE addUnique;

-- Add company_id to users
SET @tablename = 'users';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN company_id INT NULL AFTER id, ADD FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE, ADD INDEX idx_company (company_id)')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Update unique constraint for username
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (INDEX_NAME = 'username')
  ) > 0,
  CONCAT('ALTER TABLE ', @tablename, ' DROP INDEX username'),
  'SELECT 1'
));
PREPARE dropIndex FROM @preparedStatement;
EXECUTE dropIndex;
DEALLOCATE PREPARE dropIndex;

SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (INDEX_NAME = 'unique_username_company')
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD UNIQUE KEY unique_username_company (username, company_id)')
));
PREPARE addUnique FROM @preparedStatement;
EXECUTE addUnique;
DEALLOCATE PREPARE addUnique;

-- Add company_id to weeks
SET @tablename = 'weeks';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN company_id INT NULL AFTER id, ADD FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE, ADD INDEX idx_company (company_id)')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Update unique constraint for weeks
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (INDEX_NAME = 'unique_week_start')
  ) > 0,
  CONCAT('ALTER TABLE ', @tablename, ' DROP INDEX unique_week_start'),
  'SELECT 1'
));
PREPARE dropIndex FROM @preparedStatement;
EXECUTE dropIndex;
DEALLOCATE PREPARE dropIndex;

SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (INDEX_NAME = 'unique_week_company')
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD UNIQUE KEY unique_week_company (week_start_date, company_id)')
));
PREPARE addUnique FROM @preparedStatement;
EXECUTE addUnique;
DEALLOCATE PREPARE addUnique;

-- Add company_id to shift_requirements
SET @tablename = 'shift_requirements';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN company_id INT NULL AFTER id, ADD FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE, ADD INDEX idx_company (company_id)')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add company_id to schedules
SET @tablename = 'schedules';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN company_id INT NULL AFTER id, ADD FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE, ADD INDEX idx_company (company_id)')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Update unique constraint for schedules
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (INDEX_NAME = 'unique_schedule_week_section')
  ) > 0,
  CONCAT('ALTER TABLE ', @tablename, ' DROP INDEX unique_schedule_week_section'),
  'SELECT 1'
));
PREPARE dropIndex FROM @preparedStatement;
EXECUTE dropIndex;
DEALLOCATE PREPARE dropIndex;

SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (INDEX_NAME = 'unique_schedule_company_week_section')
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD UNIQUE KEY unique_schedule_company_week_section (week_id, section_id, company_id)')
));
PREPARE addUnique FROM @preparedStatement;
EXECUTE addUnique;
DEALLOCATE PREPARE addUnique;

-- Add company_id to notifications
SET @tablename = 'notifications';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN company_id INT NULL AFTER id, ADD FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE, ADD INDEX idx_company (company_id)')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

