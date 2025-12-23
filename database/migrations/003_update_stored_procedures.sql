-- =====================================================
-- Migration 003: Update Stored Procedures for Multi-Tenant
-- =====================================================
-- This migration updates stored procedures to include
-- company_id filtering for data isolation
-- =====================================================

USE ShiftSchedulerDB;

DELIMITER $$

-- Update sp_verify_login to include company_id
DROP PROCEDURE IF EXISTS sp_verify_login$$
CREATE PROCEDURE sp_verify_login(IN p_username VARCHAR(100), IN p_company_id INT)
BEGIN
    SELECT u.id AS user_id,
           u.username,
           u.password_hash,
           u.email,
           u.is_active,
           u.company_id,
           ur.id AS user_role_id,
           r.id AS role_id,
           r.role_name,
           s.id AS section_id,
           s.section_name,
           e.id AS employee_id,
           e.full_name AS employee_name,
           e.is_senior,
           e.seniority_level,
           e.employee_code
    FROM users u
    INNER JOIN user_roles ur ON ur.user_id = u.id
    INNER JOIN roles r ON r.id = ur.role_id
    INNER JOIN sections s ON s.id = ur.section_id
    LEFT JOIN employees e ON e.user_role_id = ur.id
    WHERE u.username = p_username 
      AND u.company_id = p_company_id
      AND u.is_active = 1
    ORDER BY ur.id;
END$$

-- Add company creation procedure
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

-- Add company verification procedure
DROP PROCEDURE IF EXISTS sp_verify_company_email$$
CREATE PROCEDURE sp_verify_company_email(IN p_token VARCHAR(255))
BEGIN
    UPDATE companies 
    SET status = 'VERIFIED',
        email_verified_at = NOW(),
        verification_token = NULL
    WHERE verification_token = p_token
      AND status = 'PENDING_VERIFICATION';
    
    SELECT ROW_COUNT() AS updated;
END$$

-- Add company payment completion procedure
DROP PROCEDURE IF EXISTS sp_complete_company_payment$$
CREATE PROCEDURE sp_complete_company_payment(
    IN p_company_id INT,
    IN p_payment_token VARCHAR(255),
    IN p_payment_amount DECIMAL(10,2)
)
BEGIN
    UPDATE companies 
    SET payment_status = 'COMPLETED',
        payment_completed_at = NOW(),
        payment_token = p_payment_token,
        payment_amount = p_payment_amount,
        status = 'ACTIVE'
    WHERE id = p_company_id
      AND status IN ('PAYMENT_PENDING', 'ONBOARDING');
    
    SELECT ROW_COUNT() AS updated;
END$$

-- Add get company procedure
DROP PROCEDURE IF EXISTS sp_get_company$$
CREATE PROCEDURE sp_get_company(IN p_company_id INT)
BEGIN
    SELECT * FROM companies WHERE id = p_company_id LIMIT 1;
END$$

DELIMITER ;

