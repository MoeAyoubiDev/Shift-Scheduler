# Shift Scheduler - Multi-Tenant SaaS Platform

Complete workforce management system with multi-tenant architecture.

## Quick Start

### 1. Database Setup (REQUIRED)

**Run this single command to set up everything:**

```bash
php database/setup.php
```

**This script will:**
- ✅ Drop the existing database completely
- ✅ Create a fresh database with utf8mb4_unicode_ci collation
- ✅ Create all base tables (roles, sections, users, employees, etc.)
- ✅ Add multi-tenant tables (companies, company_onboarding)
- ✅ Add company_id columns to all relevant tables
- ✅ Create essential stored procedures (sp_verify_login, sp_create_company, etc.)
- ✅ Seed reference data (roles, shift_types, shift_definitions, schedule_patterns)

**Important:** This is the ONLY script you need. It replaces all previous SQL files and migration scripts.

### 2. Verify Setup

Check that signup.php works:

```bash
curl -I https://shiftscheduler.online/signup.php
```

Should return HTTP 200.

### 3. Production Deployment Commands

**After `git pull` on your server, run these commands:**

```bash
git pull
php database/setup.php
systemctl restart php8.1-fpm
systemctl reload nginx
```

**That's it!** The setup script handles everything.

## Project Structure

```
Shift-Scheduler/
├── app/                    # Application core
│   ├── Controllers/        # Request handlers
│   ├── Core/               # Config, routing
│   ├── Helpers/            # Utility functions
│   ├── Models/             # Data models
│   └── Views/              # View templates
├── database/
│   └── setup.php           # Complete database setup script
├── public/                 # Web root
│   ├── signup.php         # Company sign-up
│   ├── login.php           # User login
│   ├── onboarding.php     # Onboarding wizard
│   └── assets/             # CSS, JS, images
└── README.md               # This file
```

## Database Schema

### Multi-Tenant Tables
- `companies` - Company accounts
- `company_onboarding` - Onboarding progress

### Core Tables
- `roles` - System roles (Director, Team Leader, etc.)
- `sections` - Company departments (company-scoped)
- `users` - User accounts (company-scoped)
- `user_roles` - User-role-section assignments
- `employees` - Employee records
- `weeks` - Week definitions (company-scoped)
- `shift_types` - Shift type definitions
- `shift_definitions` - Shift templates
- `schedule_patterns` - Work patterns
- `shift_requirements` - Required shifts (company-scoped)
- `shift_requests` - Employee requests
- `schedules` - Generated schedules (company-scoped)
- `schedule_shifts` - Individual shifts
- `schedule_assignments` - Employee-shift assignments
- `employee_breaks` - Break tracking
- `notifications` - User notifications (company-scoped)
- `system_settings` - System configuration

## User Flow

1. **Sign Up** (`/signup.php`) - Company registration
2. **Email Verification** (`/verify-email.php`) - Verify email
3. **Onboarding** (`/onboarding.php`) - Setup wizard
4. **Payment** (`/payment.php`) - One-time payment
5. **Login** (`/login.php`) - User authentication
6. **Dashboard** - Role-specific dashboard

## Roles

- **Director** - Read-only access to all sections
- **Team Leader** - Full CRUD for assigned section
- **Supervisor** - Read-only monitoring
- **Senior** - Real-time shift management
- **Employee** - Self-service requests

## Troubleshooting

### HTTP 500 on signup.php

**Solution:** Run the database setup script:

```bash
php database/setup.php
```

### Collation Error (utf8mb4_unicode_ci vs utf8mb4_0900_ai_ci)

**Solution:** The setup script creates the database with `utf8mb4_unicode_ci`. If you see collation errors, the stored procedures are already fixed. Just run:

```bash
php database/setup.php
```

### Login Errors

**Solution:** Verify the database is set up correctly:

```bash
php database/setup.php
```

Then verify stored procedure exists:

```sql
SHOW PROCEDURE STATUS WHERE Db = 'ShiftSchedulerDB' AND Name = 'sp_verify_login';
```

### Database Connection Issues

**Check your configuration in `app/Core/config.php`:**

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'ShiftSchedulerDB');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

### All Tables Missing

**Solution:** Run setup script:

```bash
php database/setup.php
```

## Configuration

Edit `app/Core/config.php` for database settings:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'ShiftSchedulerDB');
define('DB_USER', 'root');
define('DB_PASS', 'your_password');
```

## Security

- Password hashing: Bcrypt
- CSRF protection on all forms
- SQL injection prevention: Prepared statements
- Multi-tenant data isolation
- Session security

## Support

For issues:
1. Check database setup: `php database/setup.php`
2. Verify database connection in `app/Core/config.php`
3. Check PHP error logs
4. Verify all tables exist: `SHOW TABLES;`

---

**Status**: Production-Ready  
**Version**: 2.0 (Multi-Tenant SaaS)
