# Multi-Tenant Refactoring Plan

## Overview
Complete removal of section-based architecture and implementation of strict company-only multi-tenant isolation.

## Database Changes

### Tables to Modify
1. **Remove `sections` table completely**
2. **`user_roles`**: Remove `section_id`, add `company_id`
3. **`shift_requirements`**: Remove `section_id`, ensure `company_id` is NOT NULL
4. **`schedules`**: Remove `section_id`, ensure `company_id` is NOT NULL
5. **`employees`**: Add `company_id` directly (currently only via user_roles)
6. **`shift_requests`**: Ensure `company_id` is NOT NULL
7. **`employee_breaks`**: Ensure `company_id` is NOT NULL
8. **`schedule_shifts`**: Ensure `company_id` is NOT NULL
9. **`schedule_assignments`**: Ensure `company_id` is NOT NULL

### Roles Update
- Only 3 roles: `Admin`, `Team Leader`, `Employee`
- Remove: `Director`, `Supervisor`, `Senior`

## Code Changes Required

### Models
- `User.php`: Remove section logic, ensure company_id scoping
- `Employee.php`: Remove section methods, add company_id methods
- `Section.php`: DELETE (no longer needed)
- `Schedule.php`: Remove section_id parameters
- All other models: Ensure company_id filtering

### Stored Procedures
- Remove all `p_section_id` parameters
- Update all queries to use `company_id` instead
- Remove section joins
- Update: `sp_verify_login`, `sp_get_user_by_email`, `sp_get_user_by_identifier`, `sp_create_employee`, `sp_create_leader`, `sp_get_employees_by_section` → `sp_get_employees_by_company`, `sp_get_shift_requirements`, `sp_set_shift_requirement`, `sp_generate_weekly_schedule`, `sp_get_weekly_schedule`, `sp_get_today_shift`, `sp_get_coverage_gaps`, `sp_get_break_status`, `sp_get_shift_requests`, `sp_update_employee`, `sp_performance_report`, `sp_director_dashboard`

### Controllers
- `AuthController.php`: Ensure company_id in session
- `ActionHandler.php`: Remove all section-based actions
- All dashboard controllers: Filter by company_id only

### Views
- Remove section selection from all dashboards
- Update Director dashboard to show company-wide data
- Update Team Leader dashboard (no section filter)
- Update Employee dashboard (no section context)

### Signup & Onboarding
- New signup page matching screenshot (Create Account)
- New 5-step onboarding wizard:
  1. Company Details
  2. Work Rules
  3. Employees Setup
  4. Scheduling Preferences
  5. Review & Confirm

## Implementation Order

1. ✅ Database migration script created
2. ⏳ New database schema file (without sections)
3. ⏳ Updated stored procedures
4. ⏳ New signup page
5. ⏳ New onboarding wizard
6. ⏳ Update models
7. ⏳ Update controllers
8. ⏳ Update views/dashboards
9. ⏳ Testing

## Files to Create/Update

### New Files
- `database/migrations/remove_sections_multi_tenant.sql` ✅
- `database/shift_scheduler_no_sections.sql` (new schema)
- `public/signup-new.php` (new signup page)
- `public/onboarding-wizard.php` (new 5-step wizard)

### Files to Update
- All models in `app/Models/`
- All controllers in `app/Controllers/`
- All views in `app/Views/`
- All stored procedures in database files

### Files to Delete
- `app/Models/Section.php`
- Any section-related views/partials

