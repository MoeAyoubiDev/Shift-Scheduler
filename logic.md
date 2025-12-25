# Shift Scheduler - Complete Business Logic Documentation

**Version:** 1.0  
**Last Updated:** Current  
**Status:** Production Ready

---

## Table of Contents

1. [System Architecture](#1-system-architecture)
2. [Multi-Tenant Data Isolation](#2-multi-tenant-data-isolation)
3. [Authentication & Authorization](#3-authentication--authorization)
4. [User Roles & Permissions](#4-user-roles--permissions)
5. [Company Onboarding Flow](#5-company-onboarding-flow)
6. [Employee Management](#6-employee-management)
7. [Shift Scheduling System](#7-shift-scheduling-system)
8. [Shift Request Workflow](#8-shift-request-workflow)
9. [Break Tracking & Monitoring](#9-break-tracking--monitoring)
10. [Performance Analytics](#10-performance-analytics)
11. [Payment Processing](#11-payment-processing)
12. [Business Rules & Constraints](#12-business-rules--constraints)
13. [Data Models & Relationships](#13-data-models--relationships)
14. [Workflow Diagrams](#14-workflow-diagrams)

---

## 1. System Architecture

### 1.1 Overview
Shift Scheduler is a **multi-tenant SaaS platform** for workforce management and shift scheduling. Each company operates in complete data isolation with its own employees, schedules, and configurations.

### 1.2 Core Principles
- **Multi-tenant isolation**: Every data record belongs to a company (`company_id`)
- **Role-based access control**: Permissions based on user roles
- **company-based organization**: Employees belong to companies/departments
- **Week-based scheduling**: All schedules operate on weekly cycles
- **Request-driven workflow**: Employees submit requests, Team Leaders approve/decline

### 1.3 Technology Stack
- **Backend**: PHP 8.0+ with PDO
- **Database**: MySQL/MariaDB with stored procedures
- **Authentication**: Firebase Authentication + local session management
- **Frontend**: HTML5, CSS3, Vanilla JavaScript
- **Architecture**: MVC pattern with service layer

---

## 2. Multi-Tenant Data Isolation

### 2.1 Core Concept
Every company operates in complete isolation. All data queries must filter by `company_id` to prevent cross-company data leakage.

### 2.2 Data Isolation Rules

#### 2.2.1 Company Creation
- Each company gets a unique `company_id` (auto-increment)
- Company slug generated from company name (URL-friendly, unique)
- Auto-verification on creation (no email verification required)
- Status flow: `VERIFIED` → `ONBOARDING` → `PAYMENT_PENDING` → `ACTIVE`

#### 2.2.2 Data Filtering
**All queries MUST include `company_id` filter:**
```sql
SELECT * FROM employees WHERE company_id = :company_id AND company_id = :company_id
```

**Stored procedures enforce company isolation:**
- `sp_verify_login(p_username, p_company_id)` - Authenticates within company context
- `sp_create_employee(...)` - Creates employee for specific company
- `sp_get_employees_by_company(p_company_id)` - Returns only company's employees

#### 2.2.3 Foreign Key Constraints
All tables with `company_id` have foreign key constraints:
- `employees.company_id` → `companies.id`
- `users.company_id` → `companies.id`
- `companies.company_id` → `companies.id`
- `schedules.company_id` → `companies.id`
- `shift_requests.company_id` → `companies.id`

### 2.3 Company Status Lifecycle

```
SIGNUP → VERIFIED → ONBOARDING → PAYMENT_PENDING → ACTIVE
```

**Status Definitions:**
- **VERIFIED**: Company created, email auto-verified
- **ONBOARDING**: Company completing setup wizard (steps 1-5)
- **PAYMENT_PENDING**: Onboarding complete, awaiting payment
- **ACTIVE**: Payment received, full access granted

---

## 3. Authentication & Authorization

### 3.1 Authentication Flow

#### 3.1.1 Firebase Authentication
1. User signs up/logs in via Firebase (client-side)
2. Firebase returns ID token
3. Backend verifies token using Firebase Admin SDK
4. Backend creates/updates local user record
5. Backend sets PHP session with user data
6. User redirected to dashboard

#### 3.1.2 Local Authentication (Fallback)
- Username/password stored in `users` table
- Password hashed with Bcrypt
- Session stored in PHP `$_SESSION`

### 3.2 Authorization System

#### 3.2.1 Role-Based Access Control (RBAC)
All routes and actions check user role:
```php
require_role(['Team Leader', 'Supervisor']);
```

#### 3.2.2 company-Based Access
- Team Leaders, Supervisors, Employees: Limited to their company
- Supervisors: Access to all companies in their company
- company ID stored in session: `current_company_id()`

#### 3.2.3 CSRF Protection
All state-changing operations require CSRF token:
```php
require_csrf($_POST);
```

### 3.3 Session Management
- Session stored in PHP `$_SESSION`
- Contains: `user_id`, `company_id`, `company_id`, `role`, `employee_id`
- Session timeout: PHP default (typically 24 minutes)
- Logout clears all session data

---

## 4. User Roles & Permissions

### 4.1 Role Hierarchy

```
Supervisor (Highest Authority)
    ↓
Team Leader
    ↓
Supervisor
    ↓
Employee Employee
    ↓
Employee (Base Level)
```

### 4.2 Role Definitions & Capabilities

#### 4.2.1 Supervisor
**Access Level:** Company-wide  
**Primary Responsibilities:**
- Full company oversight
- View all companies and departments
- Manage company settings
- View company-wide reports and analytics
- Manage all employees across companies
- Access payment and billing information

**Permissions:**
- ✅ Create/edit/delete employees (all companies)
- ✅ Create/edit/delete companies/departments
- ✅ View all schedules (all companies)
- ✅ View all shift requests (all companies)
- ✅ View company-wide performance metrics
- ✅ Manage company configuration
- ✅ Access payment dashboard
- ❌ Cannot submit shift requests
- ❌ Cannot take breaks (not an employee)

**Dashboard Features:**
- Company overview with KPIs
- Employee directory (all companies)
- Department management
- Company-wide reports
- Settings and configuration

#### 4.2.2 Team Leader
**Access Level:** company-specific  
**Primary Responsibilities:**
- Manage employees in their company
- Create and manage schedules
- Approve/decline shift requests
- Monitor break times and attendance
- Generate weekly schedules
- Track performance metrics

**Permissions:**
- ✅ Create/edit employees (within company)
- ✅ Generate weekly schedules
- ✅ Set shift requirements
- ✅ Approve/decline shift requests
- ✅ Assign shifts to employees
- ✅ Swap shifts between employees
- ✅ View break monitoring
- ✅ View performance reports (company)
- ✅ Export schedules (CSV)
- ❌ Cannot create companies
- ❌ Cannot access other companies
- ❌ Cannot submit shift requests

**Dashboard Features:**
- company overview
- Schedule management
- Shift request approval queue
- Break monitoring
- Performance analytics
- Employee management

#### 4.2.3 Supervisor
**Access Level:** company-specific (Read-only for requests)  
**Primary Responsibilities:**
- Monitor company operations
- View shift requests (read-only)
- Monitor break times
- View schedules
- Track attendance

**Permissions:**
- ✅ View schedules (company)
- ✅ View shift requests (read-only, cannot approve/decline)
- ✅ Monitor breaks
- ✅ View performance metrics
- ❌ Cannot create employees
- ❌ Cannot generate schedules
- ❌ Cannot approve requests
- ❌ Cannot submit shift requests

**Dashboard Features:**
- company monitoring
- Request overview (read-only)
- Break monitoring
- Schedule view

#### 4.2.4 Employee Employee
**Access Level:** company-specific (Limited)  
**Primary Responsibilities:**
- View shift coverage
- Monitor company activity
- Cannot submit shift requests

**Permissions:**
- ✅ View schedules (company)
- ✅ View shift coverage
- ✅ View company activity
- ❌ Cannot submit shift requests
- ❌ Cannot take breaks (system limitation)
- ❌ Cannot manage schedules

**Dashboard Features:**
- Schedule view
- Coverage overview
- company activity

#### 4.2.5 Employee
**Access Level:** Personal  
**Primary Responsibilities:**
- Submit shift requests
- View personal schedule
- Track break times
- View request status

**Permissions:**
- ✅ Submit shift requests (within submission window)
- ✅ View personal schedule
- ✅ Start/end breaks
- ✅ View request status
- ❌ Cannot view other employees' schedules
- ❌ Cannot approve requests
- ❌ Cannot manage schedules

**Dashboard Features:**
- Personal schedule view
- Shift request submission
- Break tracking
- Request history

### 4.3 Permission Matrix

| Action | Supervisor | Team Leader | Supervisor | Employee | Employee |
|--------|----------|-------------|------------|-------|----------|
| View all companies | ✅ | ❌ | ❌ | ❌ | ❌ |
| View own company | ✅ | ✅ | ✅ | ✅ | ❌ |
| Create employees | ✅ | ✅ (company) | ❌ | ❌ | ❌ |
| Generate schedules | ✅ | ✅ (company) | ❌ | ❌ | ❌ |
| Approve requests | ✅ | ✅ (company) | ❌ | ❌ | ❌ |
| Submit requests | ❌ | ❌ | ❌ | ❌ | ✅ |
| View breaks | ✅ | ✅ (company) | ✅ (company) | ❌ | ✅ (own) |
| View performance | ✅ (all) | ✅ (company) | ✅ (company) | ❌ | ❌ |
| Manage companies | ✅ | ❌ | ❌ | ❌ | ❌ |
| Payment access | ✅ | ❌ | ❌ | ❌ | ❌ |

---

## 5. Company Onboarding Flow

### 5.1 Overview
New companies complete a 5-step onboarding wizard to configure their account before accessing the dashboard.

### 5.2 Onboarding Steps

#### Step 1: Company Details
**Purpose:** Collect basic company information

**Required Fields:**
- Company name (min 2 characters)
- Timezone (dropdown selection)
- Country (dropdown selection)
- Company size (dropdown: 1-10, 11-50, 51-200, 201-500, 500+)

**Business Rules:**
- Company name must be unique (checked via stored procedure)
- Timezone defaults to UTC if not specified
- Data saved to `companies` table
- Progress saved to `company_onboarding` table

**Validation:**
- Company name: 2-255 characters
- Timezone: Valid timezone identifier
- Country: Valid country code
- Company size: One of predefined options

#### Step 2: Work Rules
**Purpose:** Configure shift definitions and work patterns

**Required Fields:**
- Default shift hours (4-12 hours)
- Shift types (AM, PM, Mid, Night, etc.)
- Work patterns (5x2, 4x3, etc.)
- Break duration (optional)

**Business Rules:**
- Shift hours must be between 4 and 12
- At least one shift type must be defined
- Work patterns stored in `schedule_patterns` table
- Shift definitions stored in `shift_definitions` table

**Validation:**
- Shift hours: Integer between 4 and 12
- Shift types: Array of valid shift type IDs
- Work patterns: Array of valid pattern IDs

#### Step 3: Employees Setup
**Purpose:** Create initial employees and assign roles

**Required Fields (per employee):**
- Full name (required)
- Employee code (required, unique)
- Email (optional)
- Role (Employee or Employee)
- Employeeity level (0-10, optional)

**Business Rules:**
- At least one employee must be created
- Employee codes must be unique within company
- First employee typically becomes Team Leader
- Employees can be marked as "Employee" (cannot submit requests)
- Employeeity level affects scheduling priority

**Validation:**
- Full name: 2-255 characters
- Employee code: Unique within company
- Email: Valid email format (if provided)
- Role: Must be "Employee" or "Employee"
- Employeeity level: Integer 0-10

#### Step 4: Scheduling Preferences
**Purpose:** Configure schedule generation rules

**Required Fields:**
- Coverage requirements per shift type
- Minimum employees per shift
- Preferred work patterns
- Schedule generation algorithm preferences

**Business Rules:**
- Coverage requirements stored per shift type
- Minimum employees enforced during generation
- Work patterns affect schedule distribution
- Preferences saved for future schedule generation

**Validation:**
- Coverage requirements: Positive integers
- Minimum employees: Positive integer
- Work patterns: Array of valid pattern IDs

#### Step 5: Review & Confirm
**Purpose:** Review all entered data and confirm setup

**Display:**
- Summary of all steps
- Company information
- Employee list
- Work rules summary
- Scheduling preferences

**Business Rules:**
- All previous steps must be completed
- User must confirm before proceeding
- On completion, company status changes to `PAYMENT_PENDING`
- User redirected to payment page

**Actions:**
- "Edit Step X" - Return to specific step
- "Confirm & Proceed to Payment" - Complete onboarding

### 5.3 Onboarding Progress Tracking

**Database Table:** `company_onboarding`
- `company_id` (FK to companies)
- `step` (step_1, step_2, step_3, step_4, step_5)
- `step_data` (JSON of step data)
- `completed` (boolean)
- `completed_at` (timestamp)

**Stored Procedures:**
- `sp_upsert_onboarding_step` - Save/update step data
- `sp_get_onboarding_progress` - Retrieve all step data

**Business Rules:**
- Steps cannot be skipped (must complete in order)
- Step data persists if user navigates away
- Progress saved incrementally
- Completed steps cannot be modified (must edit via review step)

### 5.4 Onboarding Completion

**When Step 5 is confirmed:**
1. Validate all step data
2. Create initial companies (if not created)
3. Create initial users for employees
4. Assign Supervisor role to company admin
5. Update company status to `PAYMENT_PENDING`
6. Redirect to payment page

**Post-Onboarding:**
- Company cannot access dashboard until payment
- Onboarding data preserved
- Can return to review step to edit

---

## 6. Employee Management

### 6.1 Employee Creation

#### 6.1.1 Who Can Create Employees
- **Supervisor**: Can create employees in any company
- **Team Leader**: Can create employees in their company only
- **Supervisor**: Cannot create employees
- **Employee/Employee**: Cannot create employees

#### 6.1.2 Required Fields
- **Username** (unique within company)
- **Email** (unique within company, optional)
- **Employee Code** (unique within company)
- **Full Name** (required)
- **Role** (Employee or Employee)
- **company** (assigned automatically for Team Leaders)
- **Password** (min 8 characters)
- **Employeeity Level** (0-10, optional)

#### 6.1.3 Business Rules
- Username must be unique within company
- Email must be unique within company (if provided)
- Employee code must be unique within company
- Password hashed with Bcrypt before storage
- Employee automatically assigned to creator's company (Team Leader)
- User account created in `users` table
- Employee record created in `employees` table
- Role assigned via `user_roles` table

#### 6.1.4 Validation Rules
```php
- Username: 3-50 characters, alphanumeric + underscore
- Email: Valid email format (if provided)
- Employee Code: 3-20 characters, alphanumeric
- Full Name: 2-255 characters
- Password: Minimum 8 characters
- Role: Must be "Employee" or "Employee"
- Employeeity Level: Integer 0-10
```

### 6.2 Employee Updates

#### 6.2.1 Editable Fields
- Full Name
- Email
- Role (Employee ↔ Employee)
- Employeeity Level
- company (Supervisor only)

#### 6.2.2 Business Rules
- Cannot change username (immutable)
- Cannot change employee code (immutable)
- Role changes affect permissions immediately
- company changes require Supervisor approval
- Updates logged in audit trail

### 6.3 Employee Deletion

**Current Status:** Not implemented (soft delete recommended)

**Business Rules (if implemented):**
- Cannot delete employee with active schedules
- Cannot delete employee with pending requests
- Soft delete (mark as inactive)
- Preserve historical data

### 6.4 Employee Listing

#### 6.4.1 By company
- Team Leaders: See employees in their company
- Supervisors: See all employees (all companies)
- Filtered by `company_id` and `company_id`

#### 6.4.2 Available Employees
- Employees available for specific date
- Excludes employees with:
  - Existing shift assignments
  - Approved day-off requests
  - Medical/vacation leave

---

## 7. Shift Scheduling System

### 7.1 Week Management

#### 7.1.1 Week Structure
- Weeks run Monday to Sunday
- Week start date: Monday (00:00)
- Week end date: Sunday (23:59)
- Weeks stored in `weeks` table

#### 7.1.2 Week Creation
- Automatically created when needed
- Stored procedure: `sp_upsert_week`
- Returns `week_id` for schedule operations
- Weeks are company-agnostic (shared across all companies)

### 7.2 Shift Types

#### 7.2.1 Predefined Types
- **AM** (Morning): Typically 6:00-14:00
- **PM** (Afternoon): Typically 14:00-22:00
- **Mid** (Midday): Typically 10:00-18:00
- **Night**: Typically 22:00-06:00
- **Default**: Flexible hours

#### 7.2.2 Custom Types
- Companies can define custom shift types during onboarding
- Stored in `shift_types` table
- Linked to `shift_definitions` table

### 7.3 Shift Definitions

**Purpose:** Define shift templates with time ranges

**Fields:**
- Shift name (e.g., "Morning Shift")
- Start time (HH:MM)
- End time (HH:MM)
- Break duration (minutes)
- Shift type ID

**Business Rules:**
- Shift definitions are company-specific
- Used as templates for schedule generation
- Can be modified by Supervisors/Team Leaders

### 7.4 Schedule Patterns

**Purpose:** Define work patterns (e.g., 5 days on, 2 days off)

**Common Patterns:**
- **5x2**: 5 days work, 2 days off
- **4x3**: 4 days work, 3 days off
- **Rotating**: Custom rotation schedule

**Business Rules:**
- Patterns stored in `schedule_patterns` table
- Used during schedule generation
- Employees can request specific patterns

### 7.5 Shift Requirements

#### 7.5.1 Setting Requirements
- Team Leaders set coverage requirements per shift
- Requirements stored in `shift_requirements` table
- Fields: `week_id`, `company_id`, `date`, `shift_type_id`, `required_count`

#### 7.5.2 Business Rules
- Requirements set before schedule generation
- Can be updated after generation
- System tracks coverage gaps (required vs. assigned)

### 7.6 Schedule Generation

#### 7.6.1 Generation Process
1. Team Leader sets shift requirements
2. Team Leader clicks "Generate Schedule"
3. System calls `sp_generate_weekly_schedule`
4. Algorithm assigns employees based on:
   - Availability
   - Employeeity level
   - Previous week assignments
   - Shift requests (approved)
   - Work patterns

#### 7.6.2 Generation Algorithm
**Priority Order:**
1. Approved shift requests
2. Employeeity level (higher = priority)
3. Previous week balance (avoid over-assignment)
4. Work pattern preferences
5. Random assignment (if needed)

**Constraints:**
- Cannot exceed required count per shift
- Cannot assign same employee to overlapping shifts
- Respects day-off requests
- Balances workload across employees

#### 7.6.3 Post-Generation
- Schedule stored in `schedules` table
- Assignments stored in `schedule_assignments` table
- Coverage gaps calculated and displayed
- Team Leader can manually adjust

### 7.7 Manual Schedule Editing

#### 7.7.1 Assign Shift
- Team Leader selects empty shift slot
- Chooses employee from available list
- System validates:
  - Employee not already assigned
  - No overlapping shifts
  - Employee available (no conflicts)

#### 7.7.2 Remove Shift
- Team Leader removes assignment
- System updates coverage gap
- Employee notified (if implemented)

#### 7.7.3 Swap Shifts
- Team Leader selects two assignments
- System swaps employees
- Validates:
  - Both employees available
  - No conflicts
  - Same shift type

### 7.8 Schedule Viewing

#### 7.8.1 Team Leader View
- Full weekly schedule for company
- All employees visible
- Color-coded by shift type
- Coverage gaps highlighted
- Export to CSV

#### 7.8.2 Employee View
- Personal schedule only
- Shows assigned shifts
- Shows approved requests
- Shows pending requests

#### 7.8.3 Supervisor View
- All companies visible
- Company-wide overview
- Aggregated metrics

---

## 8. Shift Request Workflow

### 8.1 Request Submission

#### 8.1.1 Who Can Submit
- **Employees**: Can submit requests
- **Employee Employees**: Cannot submit requests (system restriction)
- **Team Leaders**: Cannot submit requests
- **Supervisors**: Cannot submit requests

#### 8.1.2 Submission Window
**Business Rules:**
- Requests can only be submitted during **current week** (Monday-Saturday)
- **Sunday is blocked** - no submissions allowed
- Requests are for **next week** only
- Late submissions blocked (contact Team Leader)

**Validation:**
```php
$today = new DateTimeImmutable();
$currentWeekStart = $today->modify('monday this week');
$currentWeekEnd = $currentWeekStart->modify('+6 days');
$todayDayOfWeek = (int) $today->format('N'); // 1=Monday, 7=Sunday

// Block Sunday
if ($todayDayOfWeek === 7) {
    return 'Submissions not allowed on Sunday';
}

// Block outside current week
if ($todayDate < $currentWeekStart || $todayDate > $currentWeekEnd) {
    return 'Submissions only allowed during current week';
}
```

#### 8.1.3 Request Types

**Day Off Request:**
- Employee requests specific day off
- Must provide reason
- Importance level: LOW, MEDIUM, HIGH

**Shift Change Request:**
- Employee requests specific shift
- Must specify shift type (AM, PM, etc.)
- Must provide reason
- Can request schedule pattern (5x2, 4x3, etc.)

**Special Requests:**
- Vacation
- Medical leave
- Moving day
- Other (with reason)

#### 8.1.4 Required Fields
- **Request Date** (date for next week)
- **Shift Definition** (if not day off)
- **Is Day Off** (boolean)
- **Schedule Pattern** (optional)
- **Reason** (required, min 10 characters)
- **Importance Level** (LOW, MEDIUM, HIGH)

#### 8.1.5 Business Rules
- One request per employee per day (next week)
- Duplicate requests rejected
- Reason required (min 10 characters)
- Importance level affects approval priority
- Previous week request summary stored (for context)

### 8.2 Request Status

#### 8.2.1 Status Values
- **PENDING**: Awaiting Team Leader review
- **APPROVED**: Request approved, will be considered in schedule
- **DECLINED**: Request rejected

#### 8.2.2 Status Flow
```
SUBMITTED → PENDING → APPROVED/DECLINED
```

### 8.3 Request Approval/Decline

#### 8.3.1 Who Can Approve
- **Team Leader**: Can approve/decline requests in their company
- **Supervisor**: Can approve/decline requests (all companies)
- **Supervisor**: Cannot approve (read-only access)

#### 8.3.2 Approval Process
1. Team Leader views pending requests
2. Reviews request details (date, reason, importance)
3. Clicks "Approve" or "Decline"
4. System updates request status
5. Employee notified (if implemented)

#### 8.3.3 Business Rules
- Approved requests considered during schedule generation
- Declined requests ignored during generation
- Status change logged with reviewer ID
- Cannot change status after schedule generation

### 8.4 Request Listing

#### 8.4.1 By Week
- Team Leaders: See all requests for company
- Employees: See own requests only
- Filtered by `week_id` and `company_id`

#### 8.4.2 By Status
- Pending requests (priority queue)
- Approved requests
- Declined requests

#### 8.4.3 By Importance
- HIGH priority requests highlighted
- MEDIUM priority requests
- LOW priority requests

### 8.5 Request History
- Employees can view request history
- Shows status changes
- Shows reviewer information
- Historical data preserved

---

## 9. Break Tracking & Monitoring

### 9.1 Break System

#### 9.1.1 Break Types
- **Regular Break**: Standard break time
- **Lunch Break**: Extended break for meals
- **Emergency Break**: Unplanned break

#### 9.1.2 Break Lifecycle
```
NOT_STARTED → ON_BREAK → COMPLETED → DELAYED (if late)
```

### 9.2 Break Submission

#### 9.2.1 Who Can Submit
- **Employees**: Can start/end breaks
- **Employee Employees**: Cannot submit breaks (system limitation)
- **Team Leaders**: Cannot submit breaks (not employees)

#### 9.2.2 Break Start
- Employee clicks "Start Break"
- System records:
  - `employee_id`
  - `break_start_time` (current timestamp)
  - `break_type`
  - `status` = 'ON_BREAK'

#### 9.2.3 Break End
- Employee clicks "End Break"
- System records:
  - `break_end_time` (current timestamp)
  - `break_duration` (calculated)
  - `status` = 'COMPLETED'
  - `delay_minutes` (if late)

### 9.3 Break Monitoring

#### 9.3.1 Team Leader View
- Real-time break status for company
- Shows employees currently on break
- Shows break duration
- Highlights delayed breaks

#### 9.3.2 Supervisor View
- Read-only break monitoring
- company-wide view
- Real-time updates

#### 9.3.3 Break Delays
- System calculates expected break end time
- Compares with actual end time
- Flags delays (> 5 minutes default)
- Logs delay duration

### 9.4 Break Analytics
- Average break duration per employee
- Delay frequency
- Break patterns
- Performance impact

---

## 10. Performance Analytics

### 10.1 Performance Metrics

#### 10.1.1 Employee Metrics
- **Attendance Rate**: Percentage of scheduled shifts attended
- **Punctuality**: On-time arrival percentage
- **Break Compliance**: Adherence to break schedules
- **Request Approval Rate**: Percentage of approved requests

#### 10.1.2 company Metrics
- **Coverage Rate**: Percentage of required shifts filled
- **Request Volume**: Number of requests per week
- **Approval Rate**: Percentage of approved requests
- **Break Delays**: Average delay time

#### 10.1.3 Company Metrics
- **Overall Coverage**: Company-wide coverage percentage
- **Employee Utilization**: Average shifts per employee
- **Request Trends**: Request patterns over time

### 10.2 Performance Calculation

#### 10.2.1 Attendance Rate
```
Attendance Rate = (Attended Shifts / Assigned Shifts) × 100
```

#### 10.2.2 Punctuality
```
Punctuality = (On-Time Arrivals / Total Shifts) × 100
```

#### 10.2.3 Coverage Rate
```
Coverage Rate = (Filled Shifts / Required Shifts) × 100
```

### 10.3 Performance Reports

#### 10.3.1 Time Periods
- Daily reports
- Weekly reports
- Monthly reports
- Custom date ranges

#### 10.3.2 Report Types
- Employee performance report
- company performance report
- Company-wide performance report
- Comparative reports (week-over-week)

### 10.4 Performance Tracking
- Historical data preserved
- Trends calculated
- Alerts for performance issues
- Export to CSV

---

## 11. Payment Processing

### 11.1 Payment Flow

#### 11.1.1 Payment Trigger
- Company completes onboarding (Step 5)
- Status changes to `PAYMENT_PENDING`
- User redirected to payment page
- Dashboard access blocked until payment

#### 11.1.2 Payment Options
- **One-time payment** (no subscription)
- Payment gateway integration (Stripe/PayPal ready)
- Payment amount configured per company

#### 11.1.3 Payment Processing
1. User selects payment method
2. Payment gateway processes payment
3. Backend receives payment confirmation
4. System calls `sp_complete_company_payment`
5. Company status changes to `ACTIVE`
6. Dashboard access granted

### 11.2 Payment Data

#### 11.2.1 Stored Information
- `payment_token` (gateway transaction ID)
- `payment_amount` (decimal)
- `payment_completed_at` (timestamp)
- `payment_status` (PENDING, COMPLETED, FAILED)

#### 11.2.2 Business Rules
- Payment required before dashboard access
- Payment amount fixed (one-time)
- Payment status tracked
- Failed payments can be retried

### 11.3 Post-Payment

#### 11.3.1 Account Activation
- Company status: `PAYMENT_PENDING` → `ACTIVE`
- Full dashboard access granted
- All features unlocked
- Onboarding data preserved

#### 11.3.2 Access Control
- Dashboard checks payment status
- Blocks access if payment not completed
- Shows payment reminder if pending

---

## 12. Business Rules & Constraints

### 12.1 Data Validation Rules

#### 12.1.1 Company
- Company name: 2-255 characters, unique
- Email: Valid format, unique
- Timezone: Valid timezone identifier
- Country: Valid country code

#### 12.1.2 User
- Username: 3-50 characters, alphanumeric + underscore, unique within company
- Email: Valid format, unique within company
- Password: Minimum 8 characters, hashed with Bcrypt

#### 12.1.3 Employee
- Employee code: 3-20 characters, alphanumeric, unique within company
- Full name: 2-255 characters
- Employeeity level: Integer 0-10

#### 12.1.4 Shift Request
- Request date: Must be in next week (Monday-Sunday)
- Reason: Minimum 10 characters
- Importance level: LOW, MEDIUM, or HIGH
- Submission window: Current week only (Monday-Saturday, no Sunday)

### 12.2 Business Constraints

#### 12.2.1 Scheduling Constraints
- Cannot assign same employee to overlapping shifts
- Cannot exceed required count per shift
- Must respect approved day-off requests
- Must balance workload across employees

#### 12.2.2 Request Constraints
- One request per employee per day (next week)
- Cannot submit requests on Sunday
- Cannot submit requests outside current week
- Employee employees cannot submit requests

#### 12.2.3 Access Constraints
- Employees can only view own schedule
- Team Leaders limited to their company
- Supervisors have company-wide access
- Supervisors have read-only access

### 12.3 Data Integrity Rules

#### 12.3.1 Foreign Key Constraints
- All `company_id` references must exist
- All `company_id` references must exist
- All `employee_id` references must exist
- All `user_id` references must exist

#### 12.3.2 Unique Constraints
- Username unique within company
- Email unique within company
- Employee code unique within company
- Company slug unique globally

#### 12.3.3 Cascade Rules
- Deleting company cascades to all related data (if implemented)
- Deleting company requires employee reassignment
- Deleting employee preserves historical data (soft delete)

### 12.4 Workflow Constraints

#### 12.4.1 Onboarding
- Steps must be completed in order
- Cannot skip steps
- Previous steps cannot be modified after completion
- Must complete all steps before payment

#### 12.4.2 Scheduling
- Requirements must be set before generation
- Schedule cannot be generated without requirements
- Manual edits allowed after generation
- Cannot delete week with active schedules

#### 12.4.3 Requests
- Cannot approve request after schedule generation
- Cannot change request status after generation
- Duplicate requests rejected
- Late submissions blocked

---

## 13. Data Models & Relationships

### 13.1 Core Entities

#### 13.1.1 Company
```
companies
├── id (PK)
├── company_name
├── company_slug (UNIQUE)
├── admin_email
├── timezone
├── country
├── company_size
├── status (VERIFIED, ONBOARDING, PAYMENT_PENDING, ACTIVE)
├── firebase_uid
├── email_verified_at
├── payment_completed_at
├── created_at
└── updated_at
```

#### 13.1.2 User
```
users
├── id (PK)
├── company_id (FK → companies.id)
├── username (UNIQUE within company)
├── email (UNIQUE within company)
├── password_hash
├── firebase_uid
├── created_at
└── updated_at
```

#### 13.1.3 Employee
```
employees
├── id (PK)
├── company_id (FK → companies.id)
├── user_id (FK → users.id)
├── company_id (FK → companies.id)
├── employee_code (UNIQUE within company)
├── full_name
├── is_senior (boolean)
├── seniority_level (0-10)
├── created_at
└── updated_at
```

#### 13.1.4 company
```
companies
├── id (PK)
├── company_id (FK → companies.id)
├── company_name
├── created_at
└── updated_at
```

#### 13.1.5 Week
```
weeks
├── id (PK)
├── week_start (DATE)
├── week_end (DATE)
├── created_at
└── updated_at
```

#### 13.1.6 Schedule
```
schedules
├── id (PK)
├── company_id (FK → companies.id)
├── week_id (FK → weeks.id)
├── company_id (FK → companies.id)
├── generated_by_employee_id (FK → employees.id)
├── created_at
└── updated_at
```

#### 13.1.7 Schedule Assignment
```
schedule_assignments
├── id (PK)
├── schedule_id (FK → schedules.id)
├── employee_id (FK → employees.id)
├── shift_date (DATE)
├── shift_definition_id (FK → shift_definitions.id)
├── created_at
└── updated_at
```

#### 13.1.8 Shift Request
```
shift_requests
├── id (PK)
├── company_id (FK → companies.id)
├── employee_id (FK → employees.id)
├── week_id (FK → weeks.id)
├── request_date (DATE)
├── shift_definition_id (FK → shift_definitions.id)
├── is_day_off (boolean)
├── schedule_pattern_id (FK → schedule_patterns.id)
├── reason (TEXT)
├── importance_level (LOW, MEDIUM, HIGH)
├── status (PENDING, APPROVED, DECLINED)
├── reviewer_id (FK → employees.id)
├── reviewed_at (TIMESTAMP)
├── created_at
└── updated_at
```

#### 13.1.9 Shift Requirement
```
shift_requirements
├── id (PK)
├── week_id (FK → weeks.id)
├── company_id (FK → companies.id)
├── date (DATE)
├── shift_type_id (FK → shift_types.id)
├── required_count (INT)
├── created_at
└── updated_at
```

#### 13.1.10 Employee Break
```
employee_breaks
├── id (PK)
├── company_id (FK → companies.id)
├── employee_id (FK → employees.id)
├── break_start_time (TIMESTAMP)
├── break_end_time (TIMESTAMP)
├── break_duration (INT, minutes)
├── break_type
├── delay_minutes (INT)
├── status (ON_BREAK, COMPLETED, DELAYED)
├── created_at
└── updated_at
```

### 13.2 Relationship Diagram

```
companies (1) ──< (N) users
companies (1) ──< (N) employees
companies (1) ──< (N) companies
companies (1) ──< (N) schedules
companies (1) ──< (N) shift_requests
companies (1) ──< (N) employee_breaks

users (1) ──< (1) employees
users (1) ──< (N) user_roles

companies (1) ──< (N) employees
companies (1) ──< (N) schedules

weeks (1) ──< (N) schedules
weeks (1) ──< (N) shift_requests
weeks (1) ──< (N) shift_requirements

schedules (1) ──< (N) schedule_assignments

employees (1) ──< (N) schedule_assignments
employees (1) ──< (N) shift_requests
employees (1) ──< (N) employee_breaks

shift_types (1) ──< (N) shift_definitions
shift_definitions (1) ──< (N) schedule_assignments
shift_definitions (1) ──< (N) shift_requests

schedule_patterns (1) ──< (N) shift_requests
```

---

## 14. Workflow Diagrams

### 14.1 Company Signup & Onboarding Flow

```
[Landing Page]
    ↓
[Sign Up] → [Company Registration]
    ↓
[Firebase Authentication]
    ↓
[Company Created] → Status: VERIFIED
    ↓
[Onboarding Step 1: Company Details]
    ↓
[Onboarding Step 2: Work Rules]
    ↓
[Onboarding Step 3: Employees Setup]
    ↓
[Onboarding Step 4: Scheduling Preferences]
    ↓
[Onboarding Step 5: Review & Confirm]
    ↓
[Payment Page] → Status: PAYMENT_PENDING
    ↓
[Payment Processing]
    ↓
[Payment Complete] → Status: ACTIVE
    ↓
[Dashboard Access Granted]
```

### 14.2 Shift Request Workflow

```
[Employee Dashboard]
    ↓
[Submit Request] → Validate Submission Window
    ↓
[Fill Request Form]
    - Request Date (next week)
    - Shift Type / Day Off
    - Reason
    - Importance Level
    ↓
[Submit] → Status: PENDING
    ↓
[Team Leader Dashboard]
    ↓
[View Pending Requests]
    ↓
[Review Request]
    ↓
[Approve] → Status: APPROVED
    OR
[Decline] → Status: DECLINED
    ↓
[Schedule Generation]
    - Approved requests considered
    - Declined requests ignored
    ↓
[Schedule Created]
```

### 14.3 Schedule Generation Workflow

```
[Team Leader Dashboard]
    ↓
[Set Shift Requirements]
    - Per shift type
    - Per date
    - Required count
    ↓
[Generate Schedule]
    ↓
[Algorithm Execution]
    1. Load approved requests
    2. Load available employees
    3. Assign based on priority:
       - Approved requests
       - Employeeity level
       - Work patterns
       - Previous week balance
    ↓
[Schedule Created]
    ↓
[Coverage Gaps Calculated]
    ↓
[Manual Adjustments Allowed]
    - Assign shift
    - Remove shift
    - Swap shifts
    ↓
[Schedule Finalized]
```

### 14.4 Break Tracking Workflow

```
[Employee Dashboard]
    ↓
[Start Break]
    ↓
[System Records]
    - break_start_time
    - status: ON_BREAK
    ↓
[Break Monitoring]
    - Team Leader sees break status
    - Supervisor sees break status
    ↓
[Employee Ends Break]
    ↓
[System Records]
    - break_end_time
    - break_duration (calculated)
    - delay_minutes (if late)
    - status: COMPLETED
    ↓
[Break Analytics Updated]
```

### 14.5 Authentication Flow

```
[Login Page]
    ↓
[Firebase Authentication]
    - Email/Password
    - Google Sign-In (optional)
    ↓
[ID Token Received]
    ↓
[Backend Verification]
    - Verify token with Firebase Admin SDK
    - Get Firebase UID
    ↓
[Local User Lookup]
    - Find user by firebase_uid
    - Or create new user
    ↓
[Session Created]
    - user_id
    - company_id
    - company_id
    - role
    - employee_id
    ↓
[Dashboard Redirect]
```

---

## 15. Stored Procedures Reference

### 15.1 Company Procedures
- `sp_create_company` - Create new company account
- `sp_get_company_by_email` - Find company by admin email
- `sp_get_company_by_id` - Get company details
- `sp_mark_company_verified` - Mark company as verified
- `sp_activate_company` - Activate company account
- `sp_upsert_onboarding_step` - Save/update onboarding step
- `sp_get_onboarding_progress` - Get all onboarding steps
- `sp_complete_company_payment` - Process payment completion

### 15.2 User Procedures
- `sp_create_director` - Create director user
- `sp_create_employee` - Create employee user
- `sp_verify_login` - Authenticate user (multi-tenant)
- `sp_get_user_by_email` - Find user by email
- `sp_get_user_by_identifier` - Find user by username/email
- `sp_user_email_exists` - Check email existence

### 15.3 Schedule Procedures
- `sp_upsert_week` - Create/update week
- `sp_get_shift_types` - Get all shift types
- `sp_get_shift_definitions` - Get shift definitions
- `sp_get_schedule_patterns` - Get work patterns
- `sp_get_shift_requirements` - Get requirements for week/company
- `sp_set_shift_requirement` - Set requirement
- `sp_generate_weekly_schedule` - Generate schedule
- `sp_get_weekly_schedule` - Get schedule for week/company
- `sp_get_today_shift` - Get today's shifts
- `sp_get_coverage_gaps` - Calculate coverage gaps

### 15.4 Request Procedures
- `sp_submit_shift_request` - Submit new request
- `sp_get_shift_requests` - Get requests for week/company
- `sp_update_shift_request_status` - Approve/decline request

### 15.5 Employee Procedures
- `sp_get_employees_by_company` - List employees in company
- `sp_get_available_employees` - Get available employees for date
- `sp_update_employee` - Update employee details

---

## 16. API Endpoints

### 16.1 Authentication
- `POST /api/auth/firebase-login` - Firebase login
- `POST /api/auth/firebase-signup` - Firebase signup
- `POST /index.php?action=logout` - Logout

### 16.2 Employee Management
- `POST /index.php?action=create_employee` - Create employee
- `POST /index.php?action=update_employee` - Update employee

### 16.3 Schedule Management
- `POST /index.php?action=save_requirements` - Save requirements
- `POST /index.php?action=generate_schedule` - Generate schedule
- `POST /index.php?action=assign_shift` - Assign shift
- `POST /index.php?action=remove_shift` - Remove shift
- `POST /index.php?action=swap_shifts` - Swap shifts

### 16.4 Request Management
- `POST /index.php?action=submit_request` - Submit request
- `POST /index.php?action=update_request_status` - Approve/decline

### 16.5 Break Tracking
- `POST /index.php?action=start_break` - Start break
- `POST /index.php?action=end_break` - End break

---

## 17. Error Handling

### 17.1 Validation Errors
- Field-level validation errors
- Business rule violations
- User-friendly error messages
- Error logging for debugging

### 17.2 Database Errors
- Foreign key violations
- Unique constraint violations
- Connection errors
- Transaction rollback on failure

### 17.3 Authentication Errors
- Invalid credentials: "Invalid username or password"
- Other errors: "Error 400"
- Session expiration handling
- CSRF token validation

---

## 18. Security Considerations

### 18.1 Data Security
- All passwords hashed with Bcrypt
- SQL injection prevention (PDO prepared statements)
- XSS protection (input sanitization)
- CSRF protection on all forms

### 18.2 Access Security
- Role-based access control
- company-based data filtering
- Company data isolation
- Session management

### 18.3 API Security
- Firebase token verification
- CSRF token validation
- Rate limiting (recommended)
- Input validation

---

## 19. Performance Optimization

### 19.1 Database Optimization
- Indexes on foreign keys
- Indexes on frequently queried columns
- Stored procedures for complex queries
- Connection pooling

### 19.2 Caching Strategy
- Session caching
- Query result caching (recommended)
- Static asset caching
- CDN for assets (recommended)

---

## 20. Future Enhancements

### 20.1 Planned Features
- Email notifications
- Mobile app
- Advanced reporting
- Integration APIs
- Bulk operations
- Schedule templates
- Automated schedule generation
- Shift swapping between employees
- Time clock integration

### 20.2 Scalability Considerations
- Database sharding (if needed)
- Load balancing
- Caching layer
- Queue system for background jobs

---

**Document End**

This document provides a comprehensive overview of all business logic, workflows, and functionality in the Shift Scheduler platform. For implementation details, refer to the source code and database schema.
