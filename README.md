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
mysql < database/shift_scheduler.sql
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
mysql < database/shift_scheduler.sql
systemctl restart php8.1-fpm
systemctl reload nginx
```

**That's it!** The setup script handles everything.

### 4. Seed Data (Optional)

The database script ships with expanded reference data (roles, shifts, patterns, and operational settings).
If you need additional tenant-specific seed data, insert it after running:

```bash
mysql < database/shift_scheduler.sql
```

## Project Structure

```
Shift-Scheduler/
├── app/                                    # Application Core (MVC Architecture)
│   ├── Controllers/                        # Request Handlers & Business Logic
│   │   ├── AdminController.php            # Admin operations
│   │   ├── AuthController.php             # Authentication (login/logout)
│   │   ├── DirectorController.php         # Supervisor (multi-section) actions
│   │   ├── EmployeeController.php         # Employee actions (requests, breaks)
│   │   ├── NotificationController.php     # Notification endpoints
│   │   ├── RequestController.php          # Shift request management
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
│   ├── Services/                           # External service integrations
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
│       │   └── dashboard.php              # Supervisor dashboard (multi-section)
│       ├── employee/
│       │   └── dashboard.php              # Employee dashboard
│       ├── partials/                      # Reusable view components
│       ├── public/
│       │   └── landing.php               # Public landing page
│       ├── shifts/
│       │   ├── admin-schedule.php         # Admin schedule view
│       │   └── employee-schedule.php     # Employee schedule view
│       └── teamleader/
│           └── dashboard.php              # Team Leader dashboard
│
├── config/                                 # Configuration Files
│   ├── app.php                            # Application settings
│   ├── database.php                       # Database credentials
│   └── schedule.php                       # Schedule configuration
│
├── database/                               # Database Management
│   ├── shift_scheduler.sql                # Single source of truth schema + procedures + seed data
│
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
│   │   └── index.php                      # Dashboard entry point
│   │
│   ├── notifications/                    # Notification tooling
│   │   └── test.php                       # Notification test endpoint
│   │
│   ├── onboarding/                       # Onboarding step templates
│   │   ├── step-1/                        # Step 1 assets
│   │   ├── step-2/                        # Step 2 assets
│   │   ├── step-3/                        # Step 3 assets
│   │   ├── step-4/                        # Step 4 assets
│   │   └── step-5/                        # Step 5 assets
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
- **shift_scheduler.sql**: Single script that drops/recreates the database
  - Creates all tables with proper dependencies
  - Creates all stored procedures
  - Seeds reference and catalog data
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
- `roles` - System roles (Supervisor, Team Leader, Employee)
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

### Supervisor
- **Access**: Executive oversight across sections
- **Features**:
  - Switch between sections for full visibility
  - Review schedules, requests, and performance reports
  - Manage Team Leaders and supervisors
  - Track leadership coverage and system health

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
mysql < database/shift_scheduler.sql
   ```

### Collation Error (utf8mb4_unicode_ci vs utf8mb4_0900_ai_ci)

**Solution:** The setup script creates the database with `utf8mb4_unicode_ci`. If you see collation errors, the stored procedures are already fixed. Just run:

   ```bash
mysql < database/shift_scheduler.sql
```

### Login Errors

**Solution:** Verify the database is set up correctly:

   ```bash
mysql < database/shift_scheduler.sql
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
mysql < database/shift_scheduler.sql
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
   - Run: `mysql < database/shift_scheduler.sql`

3. **Stored Procedure Errors**
   - Run: `mysql < database/shift_scheduler.sql` to recreate procedures

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

1. Check database setup: `mysql < database/shift_scheduler.sql`
2. Verify database connection in `config/database.php`
3. Check PHP error logs: `/var/log/php8.1-fpm.log`
4. Verify all tables exist: `SHOW TABLES;`
5. Check stored procedures: `SHOW PROCEDURE STATUS;`

---

## Test Credentials & User Accounts

### Database Credentials

**Database Connection:**
- **Host**: `localhost` (or your server IP)
- **Port**: `3306`
- **Database Name**: `ShiftSchedulerDB`
- **Username**: `shift_user`
- **Password**: `StrongPassword123!`

**To create the database user:**
```sql
CREATE USER IF NOT EXISTS 'shift_user'@'localhost' IDENTIFIED BY 'StrongPassword123!';
GRANT ALL PRIVILEGES ON ShiftSchedulerDB.* TO 'shift_user'@'localhost';
FLUSH PRIVILEGES;
```

### Creating Test Company Accounts

Since this is a multi-tenant SaaS platform, test accounts are created through the sign-up process. Each company gets its own isolated environment.

#### Sign-Up Process

1. **Go to Sign-Up Page**: `/signup.php`
2. **Fill in Company Information**:
   - **Company Name**: `Test Company` (or any name)
   - **Admin Email**: `admin@testcompany.com` (or any valid email)
   - **Password**: `TestPassword123!` (minimum 8 characters)
   - **Timezone**: Select your timezone (e.g., `America/New_York`)
   - **Country**: Select your country
   - **Company Size**: Select appropriate size (e.g., `11-50`)

3. **After Sign-Up**:
   - Company is automatically verified (no email verification needed)
   - You'll be redirected to onboarding wizard (`/onboarding.php`)
   - Complete the 5-step onboarding process:
     - Step 1: Company Details
     - Step 2: Work Rules & Shifts
     - Step 3: Add Initial Employees
     - Step 4: Scheduling Preferences
     - Step 5: Review & Confirm
   - Complete payment (simulated one-time payment)
   - You can then log in

#### Login Credentials After Sign-Up

After completing sign-up and onboarding, your login credentials are automatically generated:

**Username Format:**
- The username is automatically generated from your company name
- Format: `{company_name}_admin` (all lowercase, special characters removed)
- Example: If company name is "Test Company", username becomes `testcompany_admin`

**Password:**
- The password you set during sign-up (the one you entered in the signup form)

**Example Credentials:**
```
Company Name: Test Company
Admin Email: admin@testcompany.com
Username: testcompany_admin
Password: TestPassword123! (the password you entered during signup)
```

**Login URL:** `/login.php`

#### Complete Example

**Sign-Up Information:**
```
Company Name: Acme Corporation
Admin Email: admin@acme.com
Password: SecurePass123!
Timezone: America/New_York
Country: United States
Company Size: 51-200
```

**Generated Login Credentials:**
```
Username: acmecorporation_admin
Password: SecurePass123!
Email: admin@acme.com
```

**Login Steps:**
1. Go to `/login.php`
2. Enter username: `acmecorporation_admin`
3. Enter password: `SecurePass123!`
4. Click "Sign In"
5. You'll be redirected to your dashboard

### Employee Credentials (Created During Onboarding)

When you add employees in Step 3 of onboarding, they are automatically created with:

**Username Format:**
- Format: `{employee_name}_{index}` (all lowercase, special characters removed)
- Example: If employee name is "John Doe", username becomes `johndoe_1`

**Default Password:**
- All employees get the same default password: `TempPass123!`
- **Important**: Employees should change this password after first login

**Example Employee Credentials:**
```
Employee Name: John Doe
Username: johndoe_1
Password: TempPass123!
Role: Employee (or Team Leader based on selection)
```

### Creating Multiple Test Companies

You can create multiple test companies by:
1. Signing up with different company names and emails
2. Each company will have its own isolated data
3. Each company can have multiple users with different roles
4. Each company has its own admin account

**Example Multiple Companies:**
```
Company 1:
- Company: Acme Corp
- Username: acmecorp_admin
- Email: admin@acme.com

Company 2:
- Company: Tech Solutions
- Username: techsolutions_admin
- Email: admin@techsolutions.com
```

### User Roles & Access

After onboarding, users are created with different roles:

- **Supervisor**: Executive oversight across sections, analytics, and reports
- **Team Leader**: Full CRUD for assigned section, schedule management
- **Employee**: Self-service shift requests, view own schedule

### Quick Test Setup

For quick testing, use these exact credentials:

**Sign-Up:**
```
Company Name: Demo Company
Admin Email: demo@example.com
Password: Demo123!
Timezone: UTC
Country: United States
Company Size: 1-10
```

**After Onboarding, Login With:**
```
Username: democompany_admin
Password: Demo123!
```

### Dashboard Access

After successful login:
- **Supervisor**: Full dashboard with all sections visible
- **Team Leader**: Dashboard for assigned section only
- **Employee**: Personal schedule and requests dashboard

### Quick Start Data Workflow

To generate data quickly:
1. Sign up once to create a Supervisor account.
2. Use the Supervisor dashboard to add Team Leaders.
3. Have Team Leaders add Employees.

The database script already includes robust reference data (roles, shift types, patterns, and settings) for immediate use.

### Password Reset

Currently, password reset functionality is not implemented. To reset:
1. Access the database directly
2. Update the `password_hash` in the `users` table
3. Use PHP's `password_hash()` function to generate new hash

**Example SQL:**
```sql
UPDATE users 
SET password_hash = '$2y$10$YourHashedPasswordHere' 
WHERE username = 'testcompany_admin';
```

**Or use PHP to generate hash:**
```php
<?php
echo password_hash('NewPassword123!', PASSWORD_BCRYPT);
?>
```

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
