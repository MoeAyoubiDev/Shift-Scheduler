# Shift Scheduler - Multi-Tenant SaaS Workforce Management System

## Project Overview

Shift Scheduler is a comprehensive, enterprise-grade multi-tenant SaaS platform for workforce management. The system enables companies to sign up, verify their accounts, complete onboarding, and manage their workforce with intelligent shift scheduling, break monitoring, and performance analytics.

**Status**: Production-Ready Multi-Tenant SaaS  
**Architecture**: PHP 8+ / MySQL 8+  
**Design Philosophy**: Secure, Scalable, Multi-Tenant by Design

---

## Full Project Structure

```
Shift-Scheduler/
│
├── app/                                    # Application core
│   ├── Controllers/                        # Request handlers
│   │   ├── AdminController.php            # Admin operations
│   │   ├── AuthController.php             # Authentication (login/logout)
│   │   ├── DirectorController.php         # Director dashboard
│   │   ├── EmployeeController.php         # Employee operations
│   │   ├── RequestController.php          # Shift request handling
│   │   ├── SeniorController.php           # Senior shift management
│   │   ├── SupervisorController.php       # Supervisor monitoring
│   │   └── TeamLeaderController.php       # Team leader operations
│   │
│   ├── Core/                               # Core system components
│   │   ├── ActionHandler.php              # Centralized action routing
│   │   ├── config.php                     # Database & app configuration
│   │   └── Router.php                     # URL routing
│   │
│   ├── Helpers/                            # Utility functions
│   │   ├── helpers.php                    # General helpers (CSRF, auth, etc.)
│   │   ├── schedule.php                   # Schedule generation logic
│   │   └── view.php                       # View rendering helpers
│   │
│   ├── Middleware/                         # Middleware (future)
│   │
│   ├── Models/                             # Data models
│   │   ├── BaseModel.php                  # Base model with DB access
│   │   ├── Break.php                      # Break management
│   │   ├── Company.php                    # Company/tenant model
│   │   ├── Employee.php                   # Employee model
│   │   ├── EmployeeBreak.php              # Break tracking
│   │   ├── Notification.php               # Notifications
│   │   ├── Performance.php                # Performance analytics
│   │   ├── Role.php                       # Role management
│   │   ├── Schedule.php                   # Schedule model
│   │   ├── ScheduleAssignment.php         # Shift assignments
│   │   ├── SchedulePattern.php            # Work patterns
│   │   ├── ScheduleShift.php              # Individual shifts
│   │   ├── Section.php                    # Department sections
│   │   ├── ShiftDefinition.php            # Shift types
│   │   ├── ShiftRequest.php               # Employee requests
│   │   ├── ShiftRequirement.php           # Shift requirements
│   │   ├── ShiftType.php                  # Shift type definitions
│   │   ├── SystemSetting.php              # System settings
│   │   ├── User.php                       # User authentication
│   │   ├── UserRole.php                   # User-role assignments
│   │   └── Week.php                       # Week management
│   │
│   ├── Services/                           # Business logic services (future)
│   │
│   └── Views/                              # View templates
│       ├── auth/
│       │   └── login.php                  # Login page
│       ├── dashboard/
│       │   ├── admin.php                  # Admin dashboard
│       │   ├── employee.php               # Employee dashboard
│       │   └── overview.php               # Overview dashboard
│       ├── director/
│       │   ├── choose-section.php         # Section selection
│       │   └── dashboard.php              # Director dashboard
│       ├── employee/
│       │   └── dashboard.php              # Employee view
│       ├── partials/                       # Reusable partials
│       ├── public/
│       │   └── landing.php                # Public landing page
│       ├── senior/
│       │   └── dashboard.php              # Senior dashboard
│       ├── shifts/
│       │   ├── admin-schedule.php         # Admin schedule view
│       │   └── employee-schedule.php      # Employee schedule view
│       ├── supervisor/
│       │   └── dashboard.php              # Supervisor dashboard
│       └── teamleader/
│           └── dashboard.php              # Team leader dashboard
│
├── config/                                  # Configuration files
│   ├── app.php                             # Application config
│   ├── database.php                        # Database config
│   └── schedule.php                        # Schedule config
│
├── database/                                # Database files
│   ├── migrations/                         # Database migrations
│   │   ├── 001_add_companies_table.sql    # Multi-tenant foundation
│   │   ├── 002_add_company_id_to_tables.sql # Add company_id columns
│   │   ├── 002_add_company_id_to_tables_safe.sql # Safe migration
│   │   ├── 003_update_stored_procedures.sql # Update procedures
│   │   ├── fix_migration_002.php          # Migration fix script
│   │   └── README.md                       # Migration docs
│   ├── database.sql                        # Base schema (original)
│   ├── reset_and_seed.php                 # Old reset script
│   ├── reset_database.sql                  # Reset script
│   ├── reset_database_production.php       # Production reset (minimal seed)
│   ├── setup_production.php                # Full production setup
│   ├── test_data.sql                       # Test data (legacy)
│   ├── clean_test_data.sql                # Clean test data
│   └── README.md                           # Database docs
│
├── docs/                                    # Documentation
│   ├── DEPLOYMENT_FIX.md                   # Deployment fixes
│   ├── deployment-guide.md                 # Deployment instructions
│   ├── enhancement-plan.md                 # Enhancement plans
│   ├── enhancements-summary.md             # Enhancement summary
│   ├── migration-checklist.md              # Migration checklist
│   ├── MULTI_TENANT_SETUP.md              # Multi-tenant setup
│   ├── reorganization-plan.md              # Reorganization plan
│   └── structure-guide.md                  # Structure guide
│
├── includes/                                # Shared includes
│   ├── auth.php                            # Authentication helpers
│   ├── footer.php                          # Site footer
│   ├── functions.php                       # Global functions
│   ├── header.php                          # Site header
│   └── middleware.php                      # Middleware functions
│
├── public/                                  # Public web root
│   ├── api/                                 # API endpoints
│   │   ├── auth.php                        # Auth API
│   │   ├── employees.php                   # Employee API
│   │   ├── requests.php                    # Request API
│   │   └── schedules.php                   # Schedule API
│   │
│   ├── assets/                             # Static assets
│   │   ├── css/
│   │   │   └── app.css                     # Main stylesheet
│   │   ├── img/                            # Images
│   │   └── js/                             # JavaScript
│   │       ├── app.js                      # Main JS
│   │       ├── calendar.js                 # Calendar functionality
│   │       ├── dashboard.js                # Dashboard JS
│   │       └── enhanced.js                 # Enhanced features
│   │
│   ├── dashboard/                          # Dashboard routes
│   │
│   ├── index.php                            # Main entry point
│   ├── login.php                            # Login page
│   ├── onboarding.php                      # Company onboarding wizard
│   ├── onboarding-preview.php              # Onboarding preview
│   ├── payment.php                          # Payment processing
│   ├── resend-verification.php             # Resend verification email
│   ├── signup.php                          # Company sign-up
│   └── verify-email.php                    # Email verification
│
├── scripts/                                 # Utility scripts
│   ├── clear-cache.php                     # Cache clearing
│   ├── deploy.sh                            # Deployment script
│   ├── post-deploy.sh                      # Post-deployment tasks
│   ├── reorganize.sh                       # Reorganization script
│   └── update.sh                           # Update script
│
├── .gitignore                               # Git ignore rules
├── composer.json                            # PHP dependencies
├── README.md                                # This file
├── QUICK_FIX.md                             # Quick fix guide
├── MULTI_TENANT_IMPLEMENTATION.md          # Multi-tenant docs
├── SECURITY_CHECKLIST.md                    # Security checklist
└── CLEANUP_RECOMMENDATIONS.md               # Cleanup recommendations

```

---

## Multi-Tenant Architecture

### Database Schema

The system uses a **multi-tenant architecture** where each company has isolated data:

- **`companies`** - Company accounts (tenants)
- **`company_onboarding`** - Onboarding progress tracking
- All business tables include `company_id` for data isolation:
  - `sections` (company-specific departments)
  - `users` (company-scoped usernames)
  - `weeks`, `schedules`, `shift_requirements` (company-specific)
  - `notifications` (company-scoped)

### Data Isolation

- All queries filter by `company_id`
- Stored procedures include `company_id` parameters
- Foreign keys cascade on company deletion
- Unique constraints are company-scoped (e.g., `username` + `company_id`)

---

## Setup & Installation

### Prerequisites

- PHP 8.1+ with PDO MySQL extension
- MySQL 8.0+ or MariaDB 10.3+
- Web server (Nginx/Apache)
- Composer (optional, for dependencies)

### Database Setup

#### Option 1: Fresh Installation (Recommended)

```bash
# 1. Create database
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS ShiftSchedulerDB;"

# 2. Run full setup script
php database/setup_production.php
```

#### Option 2: Manual Migration

```bash
# 1. Create base schema
mysql -u root -p ShiftSchedulerDB < database/database.sql

# 2. Run migrations in order
mysql -u root -p ShiftSchedulerDB < database/migrations/001_add_companies_table.sql
mysql -u root -p ShiftSchedulerDB < database/migrations/002_add_company_id_to_tables.sql
mysql -u root -p ShiftSchedulerDB < database/migrations/003_update_stored_procedures.sql

# 3. Seed reference data
php database/reset_database_production.php
```

### Configuration

1. **Database Configuration**: Edit `app/Core/config.php` or `config/database.php`
2. **Application Settings**: Edit `config/app.php`
3. **Web Server**: Point document root to `/public` directory

### Production Deployment Commands

After `git pull` on the server:

```bash
git pull
php database/setup_production.php
systemctl restart php8.1-fpm
systemctl reload nginx
```

---

## User Flow

### Company Sign-Up Flow

1. **Landing Page** (`/` or `/index.php`)
   - Public overview of the platform
   - "Get Started" → `/signup.php`
   - "Sign In" → `/login.php`

2. **Sign Up** (`/signup.php`)
   - Company name, admin email, password
   - Timezone, country, company size
   - Creates company with status `PENDING_VERIFICATION`

3. **Email Verification** (`/verify-email.php`)
   - Verifies email token
   - Updates status to `VERIFIED`
   - Redirects to onboarding

4. **Onboarding Wizard** (`/onboarding.php`)
   - Step 1: Company details
   - Step 2: Work rules (shift duration, patterns)
   - Step 3: Employees setup
   - Step 4: Scheduling preferences
   - Step 5: Review & confirm
   - Creates initial section, admin user, and employees

5. **Preview Dashboard** (`/onboarding-preview.php`)
   - Shows preview of dashboard
   - Requires confirmation to proceed

6. **Payment** (`/payment.php`)
   - One-time payment processing
   - Updates status to `ACTIVE`
   - Unlocks full functionality

7. **Login** (`/login.php`)
   - Username/password authentication
   - Multi-tenant aware (filters by `company_id`)
   - Redirects to role-specific dashboard

---

## Role-Based Access Control

### Roles

1. **Director** - Read-only access to all sections
2. **Team Leader** - Full CRUD for assigned section
3. **Supervisor** - Read-only monitoring
4. **Senior** - Real-time shift management (today only)
5. **Employee** - Self-service requests and schedule viewing

### Permissions

- **Data Isolation**: All data filtered by `company_id`
- **Section Isolation**: Users see only their assigned section(s)
- **Role-Based Views**: Each role has a dedicated dashboard
- **CSRF Protection**: All forms protected
- **Session Management**: Secure session handling

---

## Key Features

### 1. Intelligent Shift Scheduling
- Automated schedule generation from employee requests
- Seniority-based assignment
- Conflict resolution
- Manual override capabilities

### 2. Break Management
- Real-time break tracking
- 30-minute break compliance
- Delay monitoring
- Senior dashboard for break oversight

### 3. Performance Analytics
- Employee performance metrics
- Break compliance tracking
- Section comparison
- Exportable reports

### 4. Multi-Tenant Isolation
- Complete data separation per company
- Company-scoped usernames
- Isolated sections and employees
- Secure tenant boundaries

---

## Database Schema

### Core Tables

- **`companies`** - Tenant accounts
- **`company_onboarding`** - Onboarding progress
- **`roles`** - System roles (Director, Team Leader, etc.)
- **`sections`** - Company departments (company-scoped)
- **`users`** - User accounts (company-scoped)
- **`user_roles`** - User-role-section assignments
- **`employees`** - Employee records
- **`weeks`** - Week definitions (company-scoped)
- **`shift_types`** - Shift type definitions
- **`shift_definitions`** - Shift templates
- **`schedule_patterns`** - Work patterns (5x2, 6x1)
- **`shift_requirements`** - Required shifts per day (company-scoped)
- **`shift_requests`** - Employee shift requests
- **`schedules`** - Generated schedules (company-scoped)
- **`schedule_shifts`** - Individual shifts in schedules
- **`schedule_assignments`** - Employee-shift assignments
- **`employee_breaks`** - Break tracking
- **`notifications`** - User notifications (company-scoped)
- **`system_settings`** - System configuration

### Stored Procedures

All business logic is implemented in MySQL stored procedures:
- `sp_verify_login` - Authentication with company_id
- `sp_create_company` - Company creation
- `sp_verify_company_email` - Email verification
- `sp_complete_company_payment` - Payment processing
- `sp_create_employee` - Employee creation
- `sp_submit_shift_request` - Shift request submission
- `sp_generate_weekly_schedule` - Schedule generation
- `sp_get_today_shift` - Today's shift for Seniors
- `sp_start_break` / `sp_end_break` - Break management
- `sp_performance_report` - Performance analytics
- And many more...

---

## Security

- **Password Hashing**: Bcrypt with PHP `password_hash()`
- **CSRF Protection**: Token-based form protection
- **SQL Injection Prevention**: Prepared statements only
- **Session Security**: Secure session handling
- **Input Validation**: Comprehensive validation on all inputs
- **Data Isolation**: Strict multi-tenant boundaries
- **Error Handling**: No sensitive data in error messages

---

## Development

### Code Style

- PHP 8.1+ with strict types
- PSR-12 coding standards (where applicable)
- MVC architecture
- Stored procedures for business logic
- Prepared statements for all queries

### Testing

- Manual testing recommended
- Test all role-based workflows
- Verify multi-tenant isolation
- Test onboarding flow end-to-end

---

## Troubleshooting

### Common Issues

1. **HTTP 500 on `/signup.php`**
   - Ensure database migrations are run
   - Check `companies` table exists
   - Verify database connection in `app/Core/config.php`

2. **Login Errors**
   - Verify `sp_verify_login` procedure exists
   - Check procedure signature (requires `company_id`)
   - Ensure user has `company_id` set

3. **Multi-Tenant Issues**
   - Verify all tables have `company_id` column
   - Check foreign key constraints
   - Ensure stored procedures include `company_id` filtering

### Database Reset

To reset database (keeps schema, clears data):

```bash
php database/reset_database_production.php
```

---

## License & Support

**Status**: Production-Ready  
**Version**: 2.0 (Multi-Tenant SaaS)  
**Last Updated**: 2024

For issues or questions, refer to:
- `QUICK_FIX.md` - Quick troubleshooting
- `docs/MULTI_TENANT_SETUP.md` - Multi-tenant setup guide
- `docs/deployment-guide.md` - Deployment instructions

---

## Project Concept & Vision

See the original README content below for detailed business model, workflows, and architectural decisions.

---

*[Original README content continues...]*
