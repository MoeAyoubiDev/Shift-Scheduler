-- Shift Scheduler database schema

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_identifier VARCHAR(50),
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('employee','primary_admin','secondary_admin') NOT NULL DEFAULT 'employee',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    requested_day VARCHAR(12) NOT NULL,
    shift_type ENUM('AM','PM','MID') NOT NULL,
    day_off TINYINT(1) NOT NULL DEFAULT 0,
    schedule_option ENUM('5x2','6x1') NOT NULL,
    reason TEXT NOT NULL,
    importance ENUM('low','medium','high') NOT NULL DEFAULT 'low',
    status ENUM('pending','accepted','declined') NOT NULL DEFAULT 'pending',
    flagged TINYINT(1) NOT NULL DEFAULT 0,
    submission_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    week_start DATE NOT NULL,
    previous_week_request TEXT,
    CONSTRAINT fk_requests_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_requests_week (week_start),
    INDEX idx_requests_status (status)
);

CREATE TABLE IF NOT EXISTS submission_controls (
    week_start DATE PRIMARY KEY,
    is_locked TINYINT(1) NOT NULL DEFAULT 0,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS shift_requirements (
    week_start DATE PRIMARY KEY,
    am_required INT NOT NULL DEFAULT 0,
    pm_required INT NOT NULL DEFAULT 0,
    mid_required INT NOT NULL DEFAULT 0,
    senior_staff TEXT,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS schedule_generations (
    week_start DATE PRIMARY KEY,
    generated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS schedule_entries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    employee_name VARCHAR(120) NOT NULL,
    week_start DATE NOT NULL,
    day VARCHAR(12) NOT NULL,
    shift_type VARCHAR(10) NOT NULL,
    status ENUM('assigned','unmatched','no_request') NOT NULL DEFAULT 'assigned',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_schedule_week (week_start),
    CONSTRAINT fk_schedule_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Seed users with static credentials (password: "password123")
INSERT INTO users (employee_identifier, name, email, username, password_hash, role) VALUES
('E-100', 'Alice Employee', 'alice@example.com', 'alice', '$2y$12$bwyeL7CkY1D82t1SxmlL5us74O9tN0k/E0IxudiQ387Lb/1tuI3LS', 'employee'),
('E-200', 'Bob Employee', 'bob@example.com', 'bob', '$2y$12$bwyeL7CkY1D82t1SxmlL5us74O9tN0k/E0IxudiQ387Lb/1tuI3LS', 'employee'),
('ADM-1', 'Primary Admin', 'primary@example.com', 'primaryadmin', '$2y$12$bwyeL7CkY1D82t1SxmlL5us74O9tN0k/E0IxudiQ387Lb/1tuI3LS', 'primary_admin'),
('ADM-2', 'Secondary Admin', 'secondary@example.com', 'secondaryadmin', '$2y$12$bwyeL7CkY1D82t1SxmlL5us74O9tN0k/E0IxudiQ387Lb/1tuI3LS', 'secondary_admin');
