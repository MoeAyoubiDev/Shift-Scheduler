-- ============================================
-- FIXED STORED PROCEDURES - PHP COMPATIBLE
-- ============================================
-- These procedures return result sets instead of OUT parameters
-- for better PHP/PDO compatibility

USE ShiftSchedulerDB;

-- Drop existing procedures if they exist
DROP PROCEDURE IF EXISTS sp_create_employee;
DROP PROCEDURE IF EXISTS sp_submit_shift_request;
DROP PROCEDURE IF EXISTS sp_review_shift_request;
DROP PROCEDURE IF EXISTS sp_generate_weekly_schedule;
DROP PROCEDURE IF EXISTS sp_start_break;
DROP PROCEDURE IF EXISTS sp_end_break;
DROP PROCEDURE IF EXISTS sp_update_schedule_assignment;

-- ============================================
-- CREATE EMPLOYEE (Returns result set)
-- ============================================
DELIMITER //

CREATE PROCEDURE sp_create_employee(
    IN p_user_id INT,
    IN p_role_id INT,
    IN p_section_id INT,
    IN p_employee_code VARCHAR(50),
    IN p_full_name VARCHAR(150),
    IN p_email VARCHAR(150),
    IN p_is_senior TINYINT(1),
    IN p_seniority_level INT
)
BEGIN
    DECLARE v_user_role_id INT;
    DECLARE v_existing_code INT;
    DECLARE v_employee_id INT;
    DECLARE v_result VARCHAR(50);
    
    -- Check if employee code already exists
    SELECT COUNT(*) INTO v_existing_code
    FROM employees
    WHERE employee_code = p_employee_code;
    
    IF v_existing_code > 0 THEN
        SET v_result = 'EMPLOYEE_CODE_EXISTS';
        SET v_employee_id = NULL;
    ELSE
        -- Get or create user_role
        SELECT id INTO v_user_role_id
        FROM user_roles
        WHERE user_id = p_user_id AND role_id = p_role_id AND section_id = p_section_id
        LIMIT 1;
        
        IF v_user_role_id IS NULL THEN
            INSERT INTO user_roles (user_id, role_id, section_id)
            VALUES (p_user_id, p_role_id, p_section_id);
            SET v_user_role_id = LAST_INSERT_ID();
        END IF;
        
        -- Create employee
        INSERT INTO employees (
            user_role_id, employee_code, full_name, email, 
            is_senior, seniority_level, is_active
        ) VALUES (
            v_user_role_id, p_employee_code, p_full_name, p_email,
            p_is_senior, p_seniority_level, 1
        );
        
        SET v_employee_id = LAST_INSERT_ID();
        SET v_result = 'SUCCESS';
    END IF;
    
    -- Return result as result set
    SELECT v_employee_id as employee_id, v_result as result;
END //

-- ============================================
-- SUBMIT SHIFT REQUEST (Returns result set)
-- ============================================
CREATE PROCEDURE sp_submit_shift_request(
    IN p_employee_id INT,
    IN p_week_start_date DATE,
    IN p_submit_date DATE,
    IN p_shift_definition_id INT,
    IN p_is_day_off TINYINT(1),
    IN p_schedule_pattern_id INT,
    IN p_reason TEXT,
    IN p_importance_level VARCHAR(10)
)
BEGIN
    DECLARE v_week_id INT;
    DECLARE v_day_of_week INT;
    DECLARE v_allow_sunday INT;
    DECLARE v_request_id INT;
    DECLARE v_result VARCHAR(50);
    
    -- Check if Sunday (blocked)
    SET v_day_of_week = DAYOFWEEK(p_submit_date);
    SELECT CAST(setting_value AS UNSIGNED) INTO v_allow_sunday
    FROM system_settings
    WHERE setting_key = 'allow_sunday_requests'
    LIMIT 1;
    
    IF v_day_of_week = 1 AND (v_allow_sunday IS NULL OR v_allow_sunday = 0) THEN
        SET v_result = 'SUNDAY_BLOCKED';
        SET v_request_id = NULL;
    ELSE
        -- Get or create week
        SELECT id INTO v_week_id
        FROM weeks
        WHERE week_start_date = p_week_start_date
        LIMIT 1;
        
        IF v_week_id IS NULL THEN
            INSERT INTO weeks (week_start_date, week_end_date)
            VALUES (p_week_start_date, DATE_ADD(p_week_start_date, INTERVAL 6 DAY));
            SET v_week_id = LAST_INSERT_ID();
        END IF;
        
        -- Check if week is locked
        IF EXISTS (SELECT 1 FROM weeks WHERE id = v_week_id AND is_locked_for_requests = 1) THEN
            SET v_result = 'WEEK_LOCKED';
            SET v_request_id = NULL;
        ELSE
            -- Insert request
            INSERT INTO shift_requests (
                employee_id, week_id, submit_date, shift_definition_id,
                is_day_off, schedule_pattern_id, reason, importance_level, status
            ) VALUES (
                p_employee_id, v_week_id, p_submit_date, p_shift_definition_id,
                p_is_day_off, p_schedule_pattern_id, p_reason, p_importance_level, 'PENDING'
            );
            
            SET v_request_id = LAST_INSERT_ID();
            SET v_result = 'SUCCESS';
        END IF;
    END IF;
    
    SELECT v_request_id as request_id, v_result as result;
END //

-- ============================================
-- REVIEW SHIFT REQUEST (Returns result set)
-- ============================================
CREATE PROCEDURE sp_review_shift_request(
    IN p_request_id INT,
    IN p_reviewed_by_employee_id INT,
    IN p_status VARCHAR(10)
)
BEGIN
    DECLARE v_exists INT;
    DECLARE v_result VARCHAR(50);
    
    SELECT COUNT(*) INTO v_exists
    FROM shift_requests
    WHERE id = p_request_id;
    
    IF v_exists = 0 THEN
        SET v_result = 'REQUEST_NOT_FOUND';
    ELSEIF p_status NOT IN ('APPROVED', 'DECLINED') THEN
        SET v_result = 'INVALID_STATUS';
    ELSE
        UPDATE shift_requests
        SET status = p_status,
            reviewed_by_employee_id = p_reviewed_by_employee_id,
            reviewed_at = NOW()
        WHERE id = p_request_id;
        
        SET v_result = 'SUCCESS';
    END IF;
    
    SELECT v_result as result;
END //

-- ============================================
-- GENERATE WEEKLY SCHEDULE (Returns result set)
-- ============================================
CREATE PROCEDURE sp_generate_weekly_schedule(
    IN p_week_start_date DATE,
    IN p_section_id INT,
    IN p_generated_by_employee_id INT
)
BEGIN
    DECLARE v_week_id INT;
    DECLARE v_existing_schedule INT;
    DECLARE v_schedule_id INT;
    DECLARE v_result VARCHAR(50);
    
    -- Get or create week
    SELECT id INTO v_week_id
    FROM weeks
    WHERE week_start_date = p_week_start_date
    LIMIT 1;
    
    IF v_week_id IS NULL THEN
        INSERT INTO weeks (week_start_date, week_end_date)
        VALUES (p_week_start_date, DATE_ADD(p_week_start_date, INTERVAL 6 DAY));
        SET v_week_id = LAST_INSERT_ID();
    END IF;
    
    -- Check if schedule already exists
    SELECT id INTO v_existing_schedule
    FROM schedules
    WHERE week_id = v_week_id AND section_id = p_section_id
    LIMIT 1;
    
    IF v_existing_schedule IS NOT NULL THEN
        SET v_schedule_id = v_existing_schedule;
        SET v_result = 'SCHEDULE_EXISTS';
    ELSE
        -- Create schedule
        INSERT INTO schedules (week_id, section_id, generated_by_employee_id, status)
        VALUES (v_week_id, p_section_id, p_generated_by_employee_id, 'DRAFT');
        SET v_schedule_id = LAST_INSERT_ID();
        
        -- Generate schedule shifts from requirements
        INSERT INTO schedule_shifts (schedule_id, date, shift_definition_id, required_count)
        SELECT 
            v_schedule_id,
            sr.date,
            sr.shift_definition_id,
            sr.required_count
        FROM shift_requirements sr
        WHERE sr.week_id = v_week_id AND sr.section_id = p_section_id;
        
        -- Auto-assign employees based on approved requests
        INSERT INTO schedule_assignments (schedule_shift_id, employee_id, assignment_source)
        SELECT DISTINCT
            ss.id,
            sr.employee_id,
            'MATCHED_REQUEST'
        FROM schedule_shifts ss
        INNER JOIN shift_requirements req ON ss.date = req.date 
            AND ss.shift_definition_id = req.shift_definition_id
            AND req.section_id = p_section_id
        INNER JOIN shift_requests sr ON sr.week_id = req.week_id
            AND sr.submit_date = req.date
            AND sr.shift_definition_id = req.shift_definition_id
            AND sr.status = 'APPROVED'
        INNER JOIN employees e ON sr.employee_id = e.id
        INNER JOIN user_roles ur ON e.user_role_id = ur.id
        WHERE ss.schedule_id = v_schedule_id
            AND ur.section_id = p_section_id
        ON DUPLICATE KEY UPDATE assignment_source = 'MATCHED_REQUEST';
        
        SET v_result = 'SUCCESS';
    END IF;
    
    SELECT v_schedule_id as schedule_id, v_result as result;
END //

-- ============================================
-- START BREAK (Returns result set)
-- ============================================
CREATE PROCEDURE sp_start_break(
    IN p_employee_id INT,
    IN p_worked_date DATE,
    IN p_schedule_shift_id INT
)
BEGIN
    DECLARE v_existing_break INT;
    DECLARE v_existing_active INT;
    DECLARE v_break_id INT;
    DECLARE v_result VARCHAR(50);
    
    -- Check if break already exists for this date
    SELECT id INTO v_existing_break
    FROM employee_breaks
    WHERE employee_id = p_employee_id AND worked_date = p_worked_date
    LIMIT 1;
    
    IF v_existing_break IS NOT NULL THEN
        -- Check if already active
        SELECT COUNT(*) INTO v_existing_active
        FROM employee_breaks
        WHERE id = v_existing_break AND is_active = 1 AND break_start IS NOT NULL AND break_end IS NULL;
        
        IF v_existing_active > 0 THEN
            SET v_result = 'BREAK_ALREADY_STARTED';
            SET v_break_id = v_existing_break;
        ELSE
            -- Update existing break
            UPDATE employee_breaks
            SET break_start = NOW(),
                is_active = 1,
                schedule_shift_id = COALESCE(p_schedule_shift_id, schedule_shift_id)
            WHERE id = v_existing_break;
            
            SET v_break_id = v_existing_break;
            SET v_result = 'SUCCESS';
        END IF;
    ELSE
        -- Create new break
        INSERT INTO employee_breaks (employee_id, worked_date, break_start, is_active, schedule_shift_id)
        VALUES (p_employee_id, p_worked_date, NOW(), 1, p_schedule_shift_id);
        
        SET v_break_id = LAST_INSERT_ID();
        SET v_result = 'SUCCESS';
    END IF;
    
    SELECT v_break_id as break_id, v_result as result;
END //

-- ============================================
-- END BREAK (Returns result set)
-- ============================================
CREATE PROCEDURE sp_end_break(
    IN p_employee_id INT,
    IN p_worked_date DATE
)
BEGIN
    DECLARE v_break_id INT;
    DECLARE v_result VARCHAR(50);
    
    SELECT id INTO v_break_id
    FROM employee_breaks
    WHERE employee_id = p_employee_id 
        AND worked_date = p_worked_date
        AND is_active = 1
        AND break_start IS NOT NULL
        AND break_end IS NULL
    LIMIT 1;
    
    IF v_break_id IS NULL THEN
        SET v_result = 'NO_ACTIVE_BREAK';
        SET v_break_id = NULL;
    ELSE
        UPDATE employee_breaks
        SET break_end = NOW(),
            is_active = 0
        WHERE id = v_break_id;
        
        SET v_result = 'SUCCESS';
    END IF;
    
    SELECT v_break_id as break_id, v_result as result;
END //

-- ============================================
-- UPDATE SCHEDULE ASSIGNMENT (Returns result set)
-- ============================================
CREATE PROCEDURE sp_update_schedule_assignment(
    IN p_schedule_shift_id INT,
    IN p_employee_id INT,
    IN p_action VARCHAR(20)
)
BEGIN
    DECLARE v_result VARCHAR(50);
    
    IF p_action = 'ADD' THEN
        INSERT INTO schedule_assignments (schedule_shift_id, employee_id, assignment_source)
        VALUES (p_schedule_shift_id, p_employee_id, 'MANUALLY_ADJUSTED')
        ON DUPLICATE KEY UPDATE assignment_source = 'MANUALLY_ADJUSTED';
        SET v_result = 'SUCCESS';
    ELSEIF p_action = 'REMOVE' THEN
        DELETE FROM schedule_assignments
        WHERE schedule_shift_id = p_schedule_shift_id AND employee_id = p_employee_id;
        SET v_result = 'SUCCESS';
    ELSE
        SET v_result = 'INVALID_ACTION';
    END IF;
    
    SELECT v_result as result;
END //

DELIMITER ;

