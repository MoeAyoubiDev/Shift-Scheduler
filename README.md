# Shift Scheduler - Multi-Tenant SaaS Platform

Complete workforce management system with multi-tenant architecture.

## Quick Start

### 1. Database Setup (REQUIRED)

**First, create the database user (if not exists):**

   ```bash
   mysql -u root -p
   ```

Then run in MySQL:
```sql
CREATE USER IF NOT EXISTS 'shift_user'@'localhost' IDENTIFIED BY 'StrongPassword123!';
GRANT ALL PRIVILEGES ON ShiftSchedulerDB.* TO 'shift_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

**Then run this single command to set up everything:**

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

**Note:** The database credentials are configured in `config/database.php`:
- User: `shift_user`
- Password: `StrongPassword123!`
- Database: `ShiftSchedulerDB`

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
├── app/                                    # Application Core (MVC Architecture)
│   ├── Controllers/                        # Request Handlers & Business Logic
│   │   ├── AdminController.php            # Admin operations
│   │   ├── AuthController.php             # Authentication (login/logout)
│   │   ├── DirectorController.php         # Director role actions
│   │   ├── EmployeeController.php         # Employee actions (requests, breaks)
│   │   ├── RequestController.php          # Shift request management
│   │   ├── SeniorController.php           # Senior role actions
│   │   ├── SupervisorController.php       # Supervisor monitoring actions
│   │   └── TeamLeaderController.php       # Team Leader scheduling actions
│   │
│   ├── Core/                               # Core System Components
│   │   ├── ActionHandler.php              # Centralized action processing
│   │   ├── config.php                     # Database & app configuration
│   │   └── Router.php                     # Request routing system
│   │
│   ├── Helpers/                            # Utility Functions
│   │   ├── helpers.php                    # General helpers (auth, CSRF, etc.)
│   │   ├── schedule.php                   # Schedule-related utilities
│   │   └── view.php                        # View rendering helpers
│   │
│   ├── Models/                             # Data Models (Database Abstraction)
│   │   ├── BaseModel.php                  # Base model with common methods
│   │   ├── Break.php                      # Employee break tracking
│   │   ├── Company.php                    # Company management (multi-tenant)
│   │   ├── Employee.php                  # Employee records
│   │   ├── EmployeeBreak.php             # Break records
│   │   ├── Notification.php              # User notifications
│   │   ├── Performance.php               # Performance reporting
│   │   ├── Role.php                       # Role management
│   │   ├── Schedule.php                   # Schedule operations
│   │   ├── ScheduleAssignment.php        # Shift assignments
│   │   ├── SchedulePattern.php           # Work patterns
│   │   ├── ScheduleShift.php              # Individual shifts
│   │   ├── Section.php                    # Department/section management
│   │   ├── ShiftDefinition.php           # Shift templates
│   │   ├── ShiftRequest.php              # Shift requests
│   │   ├── ShiftRequirement.php          # Shift requirements
│   │   ├── ShiftType.php                 # Shift types (AM, PM, etc.)
│   │   ├── SystemSetting.php             # System configuration
│   │   ├── User.php                       # User authentication
│   │   ├── UserRole.php                   # User-role assignments
│   │   └── Week.php                       # Week management
│   │
│   └── Views/                              # View Templates (Presentation Layer)
│       ├── auth/
│       │   └── login.php                  # Login form view
│       ├── dashboard/
│       │   ├── admin.php                  # Admin dashboard
│       │   ├── employee.php               # Employee dashboard
│       │   └── overview.php               # Overview dashboard
│       ├── director/
│       │   ├── choose-section.php         # Section selection
│       │   └── dashboard.php              # Director dashboard
│       ├── employee/
│       │   └── dashboard.php              # Employee dashboard
│       ├── partials/                      # Reusable view components
│       ├── public/
│       │   └── landing.php               # Public landing page
│       ├── senior/
│       │   └── dashboard.php             # Senior dashboard
│       ├── shifts/
│       │   ├── admin-schedule.php         # Admin schedule view
│       │   └── employee-schedule.php     # Employee schedule view
│       ├── supervisor/
│       │   └── dashboard.php             # Supervisor dashboard
│       └── teamleader/
│           └── dashboard.php              # Team Leader dashboard
│
├── config/                                 # Configuration Files
│   ├── app.php                            # Application settings
│   ├── database.php                       # Database credentials
│   └── schedule.php                       # Schedule configuration
│
├── database/                               # Database Management
│   ├── migrations/                        # Migration scripts (legacy)
│   └── setup.php                          # Complete database setup script
│                                           # (Drops & recreates entire DB)
│
├── includes/                               # Shared Includes
│   ├── auth.php                          # Authentication middleware
│   ├── footer.php                         # Common footer template
│   ├── functions.php                     # Global functions
│   ├── header.php                        # Common header template
│   └── middleware.php                    # Request middleware
│
├── public/                                 # Web Root (Document Root)
│   ├── api/                               # API Endpoints
│   │   ├── auth.php                      # Authentication API
│   │   ├── employees.php                 # Employee API
│   │   ├── requests.php                  # Request API
│   │   └── schedules.php                 # Schedule API
│   │
│   ├── assets/                            # Static Assets
│   │   ├── css/
│   │   │   └── app.css                   # Main stylesheet
│   │   ├── img/                          # Images
│   │   └── js/                           # JavaScript Files
│   │       ├── app.js                    # Main application JS
│   │       ├── calendar.js               # Calendar functionality
│   │       ├── dashboard.js              # Dashboard interactions
│   │       └── enhanced.js               # Enhanced features
│   │
│   ├── dashboard/                        # Dashboard routes
│   │
│   ├── index.php                         # Main entry point (routing)
│   ├── login.php                         # Login page
│   ├── onboarding.php                   # Onboarding wizard (5 steps)
│   ├── onboarding-preview.php           # Preview before payment
│   ├── payment.php                       # Payment processing
│   ├── resend-verification.php          # Resend email verification
│   ├── signup.php                        # Company sign-up page
│   └── verify-email.php                 # Email verification handler
│
├── scripts/                               # Utility Scripts
│   ├── clear-cache.php                   # Clear application cache
│   ├── deploy.sh                         # Deployment script
│   ├── post-deploy.sh                    # Post-deployment tasks
│   ├── reorganize.sh                     # Project reorganization
│   └── update.sh                         # Update script
│
├── composer.json                          # PHP Dependencies (if any)
└── README.md                              # This file
```

### Directory Descriptions

#### `/app` - Application Core
- **MVC Architecture**: Follows Model-View-Controller pattern
- **Controllers**: Handle HTTP requests, validate input, call models, return responses
- **Models**: Database abstraction layer, business logic, data validation
- **Views**: Presentation layer, HTML templates, user interface
- **Core**: System-level components (config, routing, action handling)
- **Helpers**: Reusable utility functions used across the application

#### `/config` - Configuration
- **app.php**: Application settings (timezone, app name, etc.)
- **database.php**: Database connection credentials
- **schedule.php**: Schedule-related configuration

#### `/database` - Database Management
- **setup.php**: Single script that drops and recreates entire database
  - Creates all tables with proper dependencies
  - Creates all stored procedures
  - Seeds reference data
  - Handles multi-tenant setup

#### `/includes` - Shared Components
- **header.php**: Common HTML header (navigation, meta tags)
- **footer.php**: Common HTML footer
- **auth.php**: Authentication middleware
- **middleware.php**: Request processing middleware
- **functions.php**: Global utility functions

#### `/public` - Web Root
- **Entry Points**: All PHP files accessible via web server
- **index.php**: Main application router, handles authentication and role-based routing
- **API Endpoints**: RESTful API for AJAX requests
- **Assets**: Static files (CSS, JS, images) served directly

#### `/scripts` - Utility Scripts
- Deployment and maintenance scripts
- Cache clearing utilities
- Project management tools

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

### Company Onboarding Flow

1. **Sign Up** (`/signup.php`)
   - Company name, admin email, password
   - Timezone, country, company size
   - Creates company with status: `VERIFIED` (auto-verified)
   - No email verification required
   - Redirects directly to onboarding

3. **Onboarding Wizard** (`/onboarding.php`) - 5 Steps
   - **Step 1**: Company details (name, timezone, etc.)
   - **Step 2**: Work rules (shift duration, work days)
   - **Step 3**: Employees setup (add employees)
   - **Step 4**: Scheduling preferences
   - **Step 5**: Review and confirm
   - Creates initial section, admin user, and employees
   - Updates status to: `ONBOARDING`

4. **Preview** (`/onboarding-preview.php`)
   - Shows dashboard preview based on onboarding data
   - Requires confirmation before payment

5. **Payment** (`/payment.php`)
   - One-time payment processing
   - Updates status to: `ACTIVE`
   - Unlocks full functionality

6. **Login** (`/login.php`)
   - User authentication
   - Multi-tenant login (username + company context)

7. **Dashboard** (`/index.php`)
   - Role-based dashboard routing
   - Full application access

## Roles & Permissions

### Director
- **Access**: Read-only access to all sections
- **Features**:
  - View all sections (can switch between sections)
  - View schedules, requests, performance reports
  - View analytics and metrics
  - No editing capabilities

### Team Leader
- **Access**: Full CRUD for assigned section
- **Features**:
  - Create and manage schedules
  - Approve/decline shift requests
  - Set shift requirements
  - Assign employees to shifts
  - Generate weekly schedules
  - View performance reports
  - Manage employees

### Supervisor
- **Access**: Read-only monitoring for assigned section
- **Features**:
  - View schedules and assignments
  - Monitor break status
  - View shift requests (read-only)
  - Track employee performance
  - View coverage gaps

### Senior
- **Access**: Real-time shift management for assigned section
- **Features**:
  - View today's schedule
  - Monitor active breaks
  - View weekly schedule
  - Cannot submit shift requests

### Employee
- **Access**: Self-service for own data
- **Features**:
  - View personal schedule
  - Submit shift requests
  - Request days off
  - Track personal breaks
  - View request status

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

**The application uses credentials from (in order of priority):**
1. Environment variables (`.env` file or system env vars)
2. `config/database.php` file
3. Default fallback values

**To fix connection issues:**

1. **Check for `.env` file** (it overrides config):
```bash
cat .env | grep DB_
```

If it exists and has `DB_USER=root`, either:
- Delete the `.env` file, OR
- Update it to use `shift_user`:
```bash
DB_USER=shift_user
DB_PASSWORD=StrongPassword123!
```

2. **Verify `config/database.php` has correct credentials:**
```php
return [
    'user' => 'shift_user',
    'pass' => 'StrongPassword123!',
];
```

3. **Check your configuration in `app/Core/config.php`:**

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

## Technology Stack

### Backend
- **Language**: PHP 8.1+
- **Database**: MySQL 8.0+ with stored procedures
- **Architecture**: MVC (Model-View-Controller)
- **Pattern**: Multi-tenant SaaS

### Frontend
- **HTML5**: Semantic markup
- **CSS3**: Modern styling with responsive design
- **JavaScript**: Vanilla JS (no frameworks)
- **Design**: Mobile-first, responsive layout

### Database
- **Engine**: InnoDB
- **Charset**: utf8mb4
- **Collation**: utf8mb4_unicode_ci
- **Features**: Stored procedures, foreign keys, transactions

## Key Features

### Multi-Tenant Architecture
- Complete data isolation per company
- Company-specific sections, users, schedules
- Secure tenant separation

### Scheduling System
- Weekly schedule generation
- Automatic shift assignment
- Request-based scheduling
- Coverage gap detection

### Break Management
- Real-time break tracking
- Delay monitoring
- Break status dashboard

### Request Management
- Employee shift requests
- Approval workflow
- Importance levels
- Pattern-based requests

### Reporting & Analytics
- Performance metrics
- Coverage analysis
- Employee statistics
- Dashboard widgets

## Development

### Code Structure
- **MVC Pattern**: Clear separation of concerns
- **DRY Principle**: Reusable components
- **Clean Code**: Well-documented, maintainable
- **Error Handling**: Comprehensive exception handling

### Best Practices
- Prepared statements for all database queries
- Input validation and sanitization
- CSRF protection on all forms
- Role-based access control
- Multi-tenant data isolation

## Support & Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Check `config/database.php` credentials
   - Verify database user exists and has permissions
   - Check for `.env` file overriding config

2. **Missing Tables**
   - Run: `php database/setup.php`

3. **Stored Procedure Errors**
   - Run: `php database/setup.php` to recreate procedures

4. **Permission Errors**
   - Verify database user has all privileges
   - Check file permissions on server

### Debugging

**Enable Development Mode**:
```bash
export APP_ENV=development
```

**Check PHP Error Logs**:
```bash
tail -f /var/log/php8.1-fpm.log
```

**Check Database**:
```sql
SHOW TABLES;
SHOW PROCEDURE STATUS WHERE Db = 'ShiftSchedulerDB';
```

### Getting Help

1. Check database setup: `php database/setup.php`
2. Verify database connection in `config/database.php`
3. Check PHP error logs: `/var/log/php8.1-fpm.log`
4. Verify all tables exist: `SHOW TABLES;`
5. Check stored procedures: `SHOW PROCEDURE STATUS;`

---

## Test Credentials

### Creating Test Accounts

Since this is a multi-tenant SaaS platform, test accounts are created through the sign-up process. Each company gets its own isolated environment.

### Sign-Up Process

1. **Go to Sign-Up Page**: `/signup.php`
2. **Fill in Company Information**:
   - Company Name: `Test Company` (or any name)
   - Admin Email: `admin@testcompany.com` (or any email)
   - Password: `TestPassword123!` (minimum 8 characters)
   - Timezone: Select your timezone
   - Country: Select your country
   - Company Size: Select appropriate size

3. **After Sign-Up**:
   - Company is automatically verified (no email verification needed)
   - You'll be redirected to onboarding wizard
   - Complete the 5-step onboarding process
   - Complete payment (simulated)
   - You can then log in

### Login Credentials

After completing sign-up and onboarding, use these credentials to log in:

**Format:**
- **Username**: The username you created during onboarding (usually the admin email or a custom username)
- **Password**: The password you set during sign-up

**Example:**
```
Username: admin@testcompany.com
Password: TestPassword123!
```

### Creating Multiple Test Companies

You can create multiple test companies by:
1. Signing up with different company names and emails
2. Each company will have its own isolated data
3. Each company can have multiple users with different roles

### Test User Roles

After onboarding, you can create users with different roles:
- **Director**: Read-only access to all sections
- **Team Leader**: Full CRUD for assigned section
- **Supervisor**: Read-only monitoring
- **Senior**: Real-time shift management
- **Employee**: Self-service requests

### Quick Test Setup

For quick testing, you can create a test company with:
- Company Name: `Demo Company`
- Admin Email: `demo@example.com`
- Password: `Demo123!`
- Complete onboarding with minimal data
- Complete payment
- Login and test the dashboard

---

## Project Status

**Status**: ✅ Production-Ready  
**Version**: 2.0 (Multi-Tenant SaaS)  
**Last Updated**: 2024

### Version History
- **v2.0**: Multi-tenant SaaS architecture
- **v1.0**: Single-tenant application

---

**License**: Proprietary  
**Maintained By**: Development Team
