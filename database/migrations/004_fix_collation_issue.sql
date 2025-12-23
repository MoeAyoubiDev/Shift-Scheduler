-- =====================================================
-- Migration 004: Fix Collation Mismatch Issue
-- =====================================================
-- This migration fixes the collation mismatch error
-- by updating the stored procedure to use explicit collation
-- =====================================================

USE ShiftSchedulerDB;

DELIMITER $$

-- Update sp_create_company to use explicit collation
DROP PROCEDURE IF EXISTS sp_create_company$$
CREATE PROCEDURE sp_create_company(
    IN p_company_name VARCHAR(255),
    IN p_admin_email VARCHAR(255),
    IN p_admin_password_hash VARCHAR(255),
    IN p_timezone VARCHAR(50),
    IN p_country VARCHAR(100),
    IN p_company_size VARCHAR(50),
    IN p_verification_token VARCHAR(255)
)
BEGIN
    DECLARE v_company_slug VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
    DECLARE v_slug_exists INT DEFAULT 1;
    DECLARE v_counter INT DEFAULT 0;
    
    -- Generate unique slug (explicitly set collation)
    SET v_company_slug = LOWER(REGEXP_REPLACE(p_company_name COLLATE utf8mb4_unicode_ci, '[^a-zA-Z0-9]+', '-')) COLLATE utf8mb4_unicode_ci;
    SET v_company_slug = TRIM(BOTH '-' FROM v_company_slug);
    
    -- Ensure slug is unique (explicitly set collation for comparison)
    WHILE v_slug_exists > 0 DO
        SELECT COUNT(*) INTO v_slug_exists FROM companies WHERE company_slug COLLATE utf8mb4_unicode_ci = v_company_slug;
        IF v_slug_exists > 0 THEN
            SET v_counter = v_counter + 1;
            SET v_company_slug = CONCAT(v_company_slug, '-', v_counter) COLLATE utf8mb4_unicode_ci;
        END IF;
    END WHILE;
    
    INSERT INTO companies (
        company_name,
        company_slug,
        admin_email,
        admin_password_hash,
        timezone,
        country,
        company_size,
        verification_token,
        status
    ) VALUES (
        p_company_name,
        v_company_slug,
        p_admin_email,
        p_admin_password_hash,
        COALESCE(p_timezone, 'UTC'),
        p_country,
        p_company_size,
        p_verification_token,
        'PENDING_VERIFICATION'
    );
    
    SELECT LAST_INSERT_ID() AS company_id;
END$$

DELIMITER ;

