-- ============================================
-- STORED PROCEDURES - ALL BUSINESS LOGIC
-- ============================================

USE ShiftSchedulerDB;

-- ============================================
-- 1. LOGIN VERIFICATION
-- ============================================
DELIMITER //

CREATE PROCEDURE IF NOT EXISTS sp_verify_login(
    IN p_username VARCHAR(100),
    IN p_password VARCHAR(255)
)
BEGIN
    DECLARE v_user_id INT;
    DECLARE v_password_hash VARCHAR(255);
    DECLARE v_is_active TINYINT(1);
    
    SELECT id, password_hash, is_active
    INTO v_user_id, v_password_hash, v_is_active
    FROM users
    WHERE username = p_username
    LIMIT 1;
    
    IF v_user_id IS NULL THEN
        SELECT NULL as user_id, NULL as username, 'USER_NOT_FOUND' as status;
    ELSEIF v_is_active = 0 THEN
        SELECT NULL as user_id, NULL as username, 'USER_INACTIVE' as status;
    ELSEIF v_password_hash IS NULL OR NOT (v_password_hash = SHA2(CONCAT(p_password, 'salt'), 256)) THEN
        -- Note: In production, use password_verify() in PHP, but for stored proc demo:
        SELECT NULL as user_id, NULL as username, 'INVALID_PASSWORD' as status;
    ELSE
        SELECT 
            u.id as user_id,
            u.username,
            u.email,
            ur.id as user_role_id,
            r.role_name,
            s.id as section_id,
            s.section_name,
            e.id as employee_id,
            e.employee_code,
            e.full_name,
            e.is_senior,
            'SUCCESS' as status
        FROM users u
        INNER JOIN user_roles ur ON u.id = ur.user_id
        INNER JOIN roles r ON ur.role_id = r.id
        INNER JOIN sections s ON ur.section_id = s.id
        LEFT JOIN employees e ON ur.id = e.user_role_id
        WHERE u.id = v_user_id AND u.is_active = 1
        ORDER BY ur.id
        LIMIT 1;
    END IF;
END //

DELIMITER ;

-- ============================================
-- 2. GET USER ROLES AND SECTIONS
-- ============================================
DELIMITER //

CREATE PROCEDURE IF NOT EXISTS sp_get_user_roles(
    IN p_user_id INT
)
BEGIN
    SELECT 
        ur.id as user_role_id,
        r.role_name,
        s.id as section_id,
        s.section_name,
        e.id as employee_id,
        e.employee_code,
        e.full_name
    FROM user_roles ur
    INNER JOIN roles r ON ur.role_id = r.id
    INNER JOIN sections s ON ur.section_id = s.id
    LEFT JOIN employees e ON ur.id = e.user_role_id
    WHERE ur.user_id = p_user_id
    ORDER BY r.role_name, s.section_name;
END //

DELIMITER ;

-- ============================================
-- 3. CREATE EMPLOYEE
-- ============================================
DELIMITER //

CREATE PROCEDURE IF NOT EXISTS sp_create_employee(
    IN p_user_id INT,
    IN p_role_id INT,
    IN p_section_id INT,
    IN p_employee_code VARCHAR(50),
    IN p_full_name VARCHAR(150),
    IN p_email VARCHAR(150),
    IN p_is_senior TINYINT(1),
    IN p_seniority_level INT,
    OUT p_employee_id INT,
    OUT p_result VARCHAR(50)
)
BEGIN
    DECLARE v_user_role_id INT;
    DECLARE v_existing_code INT;
    
    -- Check if employee code already exists
    SELECT COUNT(*) INTO v_existing_code
    FROM employees
    WHERE employee_code = p_employee_code;
    
    IF v_existing_code > 0 THEN
        SET p_result = 'EMPLOYEE_CODE_EXISTS';
        SET p_employee_id = NULL;
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
        
        SET p_employee_id = LAST_INSERT_ID();
        SET p_result = 'SUCCESS';
    END IF;
END //

DELIMITER ;

-- ============================================
-- 4. SUBMIT SHIFT REQUEST
-- ============================================
DELIMITER //

CREATE PROCEDURE IF NOT EXISTS sp_submit_shift_request(
    IN p_employee_id INT,
    IN p_week_start_date DATE,
    IN p_submit_date DATE,
    IN p_shift_definition_id INT,
    IN p_is_day_off TINYINT(1),
    IN p_schedule_pattern_id INT,
    IN p_reason TEXT,
    IN p_importance_level VARCHAR(10),
    OUT p_request_id INT,
    OUT p_result VARCHAR(50)
)
BEGIN
    DECLARE v_week_id INT;
    DECLARE v_day_of_week INT;
    DECLARE v_allow_sunday INT;
    
    -- Check if Sunday (blocked)
    SET v_day_of_week = DAYOFWEEK(p_submit_date);
    SELECT CAST(setting_value AS UNSIGNED) INTO v_allow_sunday
    FROM system_settings
    WHERE setting_key = 'allow_sunday_requests'
    LIMIT 1;
    
    IF v_day_of_week = 1 AND (v_allow_sunday IS NULL OR v_allow_sunday = 0) THEN
        SET p_result = 'SUNDAY_BLOCKED';
        SET p_request_id = NULL;
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
            SET p_result = 'WEEK_LOCKED';
            SET p_request_id = NULL;
        ELSE
            -- Insert request
            INSERT INTO shift_requests (
                employee_id, week_id, submit_date, shift_definition_id,
                is_day_off, schedule_pattern_id, reason, importance_level, status
            ) VALUES (
                p_employee_id, v_week_id, p_submit_date, p_shift_definition_id,
                p_is_day_off, p_schedule_pattern_id, p_reason, p_importance_level, 'PENDING'
            );
            
            SET p_request_id = LAST_INSERT_ID();
            SET p_result = 'SUCCESS';
        END IF;
    END IF;
END //

DELIMITER ;

-- ============================================
-- 5. APPROVE/DECLINE SHIFT REQUEST
-- ============================================
DELIMITER //

CREATE PROCEDURE IF NOT EXISTS sp_review_shift_request(
    IN p_request_id INT,
    IN p_reviewed_by_employee_id INT,
    IN p_status VARCHAR(10),
    OUT p_result VARCHAR(50)
)
BEGIN
    DECLARE v_exists INT;
    
    SELECT COUNT(*) INTO v_exists
    FROM shift_requests
    WHERE id = p_request_id;
    
    IF v_exists = 0 THEN
        SET p_result = 'REQUEST_NOT_FOUND';
    ELSEIF p_status NOT IN ('APPROVED', 'DECLINED') THEN
        SET p_result = 'INVALID_STATUS';
    ELSE
        UPDATE shift_requests
        SET status = p_status,
            reviewed_by_employee_id = p_reviewed_by_employee_id,
            reviewed_at = NOW()
        WHERE id = p_request_id;
        
        SET p_result = 'SUCCESS';
    END IF;
END //

DELIMITER ;

-- ============================================
-- 6. GENERATE WEEKLY SCHEDULE
-- ============================================
DELIMITER //

CREATE PROCEDURE IF NOT EXISTS sp_generate_weekly_schedule(
    IN p_week_start_date DATE,
    IN p_section_id INT,
    IN p_generated_by_employee_id INT,
    OUT p_schedule_id INT,
    OUT p_result VARCHAR(50)
)
BEGIN
    DECLARE v_week_id INT;
    DECLARE v_existing_schedule INT;
    
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
        SET p_schedule_id = v_existing_schedule;
        SET p_result = 'SCHEDULE_EXISTS';
    ELSE
        -- Create schedule
        INSERT INTO schedules (week_id, section_id, generated_by_employee_id, status)
        VALUES (v_week_id, p_section_id, p_generated_by_employee_id, 'DRAFT');
        SET p_schedule_id = LAST_INSERT_ID();
        
        -- Generate schedule shifts from requirements
        INSERT INTO schedule_shifts (schedule_id, date, shift_definition_id, required_count)
        SELECT 
            p_schedule_id,
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
        WHERE ss.schedule_id = p_schedule_id
            AND ur.section_id = p_section_id
        ON DUPLICATE KEY UPDATE assignment_source = 'MATCHED_REQUEST';
        
        SET p_result = 'SUCCESS';
    END IF;
END //

DELIMITER ;

-- ============================================
-- 7. GET SCHEDULES
-- ============================================
DELIMITER //

CREATE PROCEDURE IF NOT EXISTS sp_get_schedules(
    IN p_week_start_date DATE,
    IN p_section_id INT
)
BEGIN
    SELECT 
        s.id as schedule_id,
        s.week_id,
        s.section_id,
        s.status,
        s.generated_at,
        ss.id as schedule_shift_id,
        ss.date,
        sd.id as shift_definition_id,
        sd.shift_name,
        sd.category,
        sd.start_time,
        sd.end_time,
        e.id as employee_id,
        e.employee_code,
        e.full_name,
        sa.assignment_source
    FROM schedules s
    INNER JOIN schedule_shifts ss ON s.id = ss.schedule_id
    INNER JOIN shift_definitions sd ON ss.shift_definition_id = sd.id
    LEFT JOIN schedule_assignments sa ON ss.id = sa.schedule_shift_id
    LEFT JOIN employees e ON sa.employee_id = e.id
    WHERE s.week_id = (SELECT id FROM weeks WHERE week_start_date = p_week_start_date LIMIT 1)
        AND (p_section_id IS NULL OR s.section_id = p_section_id)
    ORDER BY ss.date, sd.start_time, e.full_name;
END //

DELIMITER ;

-- ============================================
-- 8. START BREAK
-- ============================================
DELIMITER //

CREATE PROCEDURE IF NOT EXISTS sp_start_break(
    IN p_employee_id INT,
    IN p_worked_date DATE,
    IN p_schedule_shift_id INT,
    OUT p_break_id INT,
    OUT p_result VARCHAR(50)
)
BEGIN
    DECLARE v_existing_break INT;
    DECLARE v_existing_active INT;
    
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
            SET p_result = 'BREAK_ALREADY_STARTED';
            SET p_break_id = v_existing_break;
        ELSE
            -- Update existing break
            UPDATE employee_breaks
            SET break_start = NOW(),
                is_active = 1,
                schedule_shift_id = COALESCE(p_schedule_shift_id, schedule_shift_id)
            WHERE id = v_existing_break;
            
            SET p_break_id = v_existing_break;
            SET p_result = 'SUCCESS';
        END IF;
    ELSE
        -- Create new break
        INSERT INTO employee_breaks (employee_id, worked_date, break_start, is_active, schedule_shift_id)
        VALUES (p_employee_id, p_worked_date, NOW(), 1, p_schedule_shift_id);
        
        SET p_break_id = LAST_INSERT_ID();
        SET p_result = 'SUCCESS';
    END IF;
END //

DELIMITER ;

-- ============================================
-- 9. END BREAK
-- ============================================
DELIMITER //

CREATE PROCEDURE IF NOT EXISTS sp_end_break(
    IN p_employee_id INT,
    IN p_worked_date DATE,
    OUT p_break_id INT,
    OUT p_result VARCHAR(50)
)
BEGIN
    DECLARE v_break_id INT;
    
    SELECT id INTO v_break_id
    FROM employee_breaks
    WHERE employee_id = p_employee_id 
        AND worked_date = p_worked_date
        AND is_active = 1
        AND break_start IS NOT NULL
        AND break_end IS NULL
    LIMIT 1;
    
    IF v_break_id IS NULL THEN
        SET p_result = 'NO_ACTIVE_BREAK';
        SET p_break_id = NULL;
    ELSE
        UPDATE employee_breaks
        SET break_end = NOW(),
            is_active = 0
        WHERE id = v_break_id;
        
        SET p_break_id = v_break_id;
        SET p_result = 'SUCCESS';
    END IF;
END //

DELIMITER ;

-- ============================================
-- 10. CALCULATE DELAYS
-- ============================================
DELIMITER //

CREATE PROCEDURE IF NOT EXISTS sp_calculate_delays(
    IN p_employee_id INT,
    IN p_start_date DATE,
    IN p_end_date DATE
)
BEGIN
    SELECT 
        e.id as employee_id,
        e.full_name,
        e.employee_code,
        eb.worked_date,
        eb.break_start,
        eb.break_end,
        TIMESTAMPDIFF(MINUTE, eb.break_start, eb.break_end) as break_duration_minutes,
        GREATEST(0, TIMESTAMPDIFF(MINUTE, 
            DATE_ADD(eb.break_start, INTERVAL 30 MINUTE), 
            eb.break_end
        )) as delay_minutes
    FROM employee_breaks eb
    INNER JOIN employees e ON eb.employee_id = e.id
    WHERE eb.employee_id = COALESCE(p_employee_id, eb.employee_id)
        AND eb.worked_date BETWEEN COALESCE(p_start_date, eb.worked_date) AND COALESCE(p_end_date, eb.worked_date)
        AND eb.break_start IS NOT NULL
        AND eb.break_end IS NOT NULL
    ORDER BY eb.worked_date DESC;
END //

DELIMITER ;

-- ============================================
-- 11. PERFORMANCE REPORTS
-- ============================================
DELIMITER //

CREATE PROCEDURE IF NOT EXISTS sp_get_performance_report(
    IN p_section_id INT,
    IN p_employee_id INT,
    IN p_start_date DATE,
    IN p_end_date DATE
)
BEGIN
    SELECT 
        e.id as employee_id,
        e.full_name,
        e.employee_code,
        s.section_name,
        COUNT(DISTINCT eb.worked_date) as days_worked,
        COALESCE(SUM(GREATEST(0, TIMESTAMPDIFF(MINUTE, 
            DATE_ADD(eb.break_start, INTERVAL 30 MINUTE), 
            eb.break_end
        ))), 0) as total_delay_minutes,
        CASE 
            WHEN COUNT(DISTINCT eb.worked_date) > 0 THEN
                COALESCE(SUM(GREATEST(0, TIMESTAMPDIFF(MINUTE, 
                    DATE_ADD(eb.break_start, INTERVAL 30 MINUTE), 
                    eb.break_end
                ))) / COUNT(DISTINCT eb.worked_date), 0)
            ELSE 0
        END as average_delay_minutes
    FROM employees e
    INNER JOIN user_roles ur ON e.user_role_id = ur.id
    INNER JOIN sections s ON ur.section_id = s.id
    LEFT JOIN employee_breaks eb ON e.id = eb.employee_id
        AND eb.worked_date BETWEEN COALESCE(p_start_date, '1900-01-01') AND COALESCE(p_end_date, '9999-12-31')
        AND eb.break_start IS NOT NULL
        AND eb.break_end IS NOT NULL
    WHERE (p_section_id IS NULL OR s.id = p_section_id)
        AND (p_employee_id IS NULL OR e.id = p_employee_id)
        AND e.is_active = 1
    GROUP BY e.id, e.full_name, e.employee_code, s.section_name
    ORDER BY average_delay_minutes ASC, total_delay_minutes ASC;
END //

DELIMITER ;

-- ============================================
-- 12. DIRECTOR DASHBOARD DATA
-- ============================================
DELIMITER //

CREATE PROCEDURE IF NOT EXISTS sp_get_director_dashboard(
    IN p_section_id INT
)
BEGIN
    SELECT 
        s.id as section_id,
        s.section_name,
        COUNT(DISTINCT e.id) as total_employees,
        COUNT(DISTINCT CASE WHEN e.is_active = 1 THEN e.id END) as active_employees,
        COUNT(DISTINCT sr.id) as pending_requests,
        COUNT(DISTINCT sch.id) as schedules_count
    FROM sections s
    LEFT JOIN user_roles ur ON s.id = ur.section_id
    LEFT JOIN employees e ON ur.id = e.user_role_id
    LEFT JOIN shift_requests sr ON e.id = sr.employee_id AND sr.status = 'PENDING'
    LEFT JOIN schedules sch ON s.id = sch.section_id
    WHERE p_section_id IS NULL OR s.id = p_section_id
    GROUP BY s.id, s.section_name;
END //

DELIMITER ;

-- ============================================
-- 13. GET TODAY'S SHIFT (FOR SENIOR)
-- ============================================
DELIMITER //

CREATE PROCEDURE IF NOT EXISTS sp_get_today_shift(
    IN p_section_id INT,
    IN p_current_date DATE
)
BEGIN
    DECLARE v_week_start DATE;
    
    SET v_week_start = DATE_SUB(p_current_date, INTERVAL WEEKDAY(p_current_date) DAY);
    
    SELECT 
        ss.id as schedule_shift_id,
        ss.date,
        sd.id as shift_definition_id,
        sd.shift_name,
        sd.category,
        sd.start_time,
        sd.end_time,
        e.id as employee_id,
        e.employee_code,
        e.full_name,
        e.is_senior,
        eb.id as break_id,
        eb.break_start,
        eb.break_end,
        eb.is_active as break_active,
        CASE 
            WHEN eb.break_start IS NOT NULL AND eb.break_end IS NULL THEN 'ON_BREAK'
            WHEN eb.break_start IS NOT NULL AND eb.break_end IS NOT NULL THEN 'BREAK_COMPLETED'
            ELSE 'NO_BREAK'
        END as break_status,
        TIMESTAMPDIFF(MINUTE, eb.break_start, NOW()) as break_duration_minutes,
        GREATEST(0, TIMESTAMPDIFF(MINUTE, 
            DATE_ADD(eb.break_start, INTERVAL 30 MINUTE), 
            COALESCE(eb.break_end, NOW())
        )) as delay_minutes
    FROM schedule_shifts ss
    INNER JOIN schedules sch ON ss.schedule_id = sch.id
    INNER JOIN shift_definitions sd ON ss.shift_definition_id = sd.id
    INNER JOIN schedule_assignments sa ON ss.id = sa.schedule_shift_id
    INNER JOIN employees e ON sa.employee_id = e.id
    INNER JOIN user_roles ur ON e.user_role_id = ur.id
    LEFT JOIN employee_breaks eb ON e.id = eb.employee_id AND eb.worked_date = p_current_date
    WHERE ss.date = p_current_date
        AND sch.section_id = p_section_id
        AND sd.category != 'OFF'
    ORDER BY sd.start_time, e.full_name;
END //

DELIMITER ;

-- ============================================
-- 14. UPDATE SCHEDULE ASSIGNMENT
-- ============================================
DELIMITER //

CREATE PROCEDURE IF NOT EXISTS sp_update_schedule_assignment(
    IN p_schedule_shift_id INT,
    IN p_employee_id INT,
    IN p_action VARCHAR(20), -- 'ADD' or 'REMOVE'
    OUT p_result VARCHAR(50)
)
BEGIN
    IF p_action = 'ADD' THEN
        INSERT INTO schedule_assignments (schedule_shift_id, employee_id, assignment_source)
        VALUES (p_schedule_shift_id, p_employee_id, 'MANUALLY_ADJUSTED')
        ON DUPLICATE KEY UPDATE assignment_source = 'MANUALLY_ADJUSTED';
        SET p_result = 'SUCCESS';
    ELSEIF p_action = 'REMOVE' THEN
        DELETE FROM schedule_assignments
        WHERE schedule_shift_id = p_schedule_shift_id AND employee_id = p_employee_id;
        SET p_result = 'SUCCESS';
    ELSE
        SET p_result = 'INVALID_ACTION';
    END IF;
END //

DELIMITER ;

