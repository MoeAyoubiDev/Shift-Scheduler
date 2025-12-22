# Quick Fix for "Get Started" Button 500 Error

## Issue
The "Get Started" button shows a 500 error because the database migrations haven't been run yet.

## Solution

### Step 1: Run Database Migrations

```bash
# Connect to your database
mysql -u root -p ShiftSchedulerDB

# Or if using a different user:
mysql -u your_user -p ShiftSchedulerDB
```

Then run these SQL files in order:

```sql
-- Run migration 1
SOURCE database/migrations/001_add_companies_table.sql;

-- Run migration 2
SOURCE database/migrations/002_add_company_id_to_tables.sql;

-- Run migration 3
SOURCE database/migrations/003_update_stored_procedures.sql;
```

Or from command line:

```bash
mysql -u root -p ShiftSchedulerDB < database/migrations/001_add_companies_table.sql
mysql -u root -p ShiftSchedulerDB < database/migrations/002_add_company_id_to_tables.sql
mysql -u root -p ShiftSchedulerDB < database/migrations/003_update_stored_procedures.sql
```

### Step 2: Verify Tables Exist

```sql
SHOW TABLES LIKE 'companies';
SHOW TABLES LIKE 'company_onboarding';
```

### Step 3: Test the Sign-Up Page

1. Visit `/signup.php` in your browser
2. Fill out the form
3. Submit

## If You Still Get Errors

### Check Error Logs

```bash
# Check PHP error log
tail -f /var/log/php/error.log

# Or check web server error log
tail -f /var/log/nginx/error.log
# or
tail -f /var/log/apache2/error.log
```

### Common Issues

1. **Table doesn't exist**: Run migrations (see Step 1)
2. **Permission denied**: Check database user permissions
3. **Connection error**: Verify database credentials in `.env` or `config/database.php`

## Alternative: Manual Table Creation

If migrations fail, you can manually create the companies table:

```sql
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
```

## After Fixing

Once migrations are run, the signup page should work correctly. The error message will now be more helpful if there are other issues.

