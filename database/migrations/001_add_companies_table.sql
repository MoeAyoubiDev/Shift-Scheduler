-- =====================================================
-- Migration 001: Add Companies Table (Multi-Tenant Foundation)
-- =====================================================
-- This migration adds the companies table and prepares
-- the database for multi-tenant architecture
-- =====================================================

USE ShiftSchedulerDB;

-- Create companies table
CREATE TABLE IF NOT EXISTS companies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(255) NOT NULL,
    company_slug VARCHAR(255) NOT NULL UNIQUE,
    admin_email VARCHAR(255) NOT NULL,
    admin_password_hash VARCHAR(255) NOT NULL,
    timezone VARCHAR(50) DEFAULT 'UTC',
    country VARCHAR(100),
    company_size VARCHAR(50),
    status ENUM('PENDING_VERIFICATION', 'VERIFIED', 'ONBOARDING', 'PAYMENT_PENDING', 'ACTIVE', 'SUSPENDED') DEFAULT 'PENDING_VERIFICATION',
    email_verified_at DATETIME NULL,
    payment_completed_at DATETIME NULL,
    onboarding_completed_at DATETIME NULL,
    verification_token VARCHAR(255) NULL,
    payment_token VARCHAR(255) NULL,
    payment_amount DECIMAL(10,2) DEFAULT 0.00,
    payment_status ENUM('PENDING', 'COMPLETED', 'FAILED', 'REFUNDED') DEFAULT 'PENDING',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (company_slug),
    INDEX idx_status (status),
    INDEX idx_email (admin_email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create company onboarding progress table
CREATE TABLE IF NOT EXISTS company_onboarding (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    step VARCHAR(50) NOT NULL,
    step_data JSON NULL,
    completed TINYINT(1) DEFAULT 0,
    completed_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    UNIQUE KEY unique_company_step (company_id, step),
    INDEX idx_company (company_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

