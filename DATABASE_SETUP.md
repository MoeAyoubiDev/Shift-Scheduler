# Database Setup Instructions

## Quick Setup

```bash
mysql -u shift_user -p ShiftSchedulerDB < database/shift_scheduler.sql
```

Or if database doesn't exist:
```bash
mysql -u shift_user -p < database/shift_scheduler.sql
```

## What This Script Does

1. **Drops** existing `ShiftSchedulerDB` database
2. **Creates** fresh database with updated schema
3. **Removes** sections table completely
4. **Enforces** company-only multi-tenant isolation
5. **Updates** all stored procedures to use `company_id` instead of `section_id`
6. **Sets** roles to only 3: Admin, Team Leader, Employee
7. **Inserts** reference data (roles, shift_types, shift_definitions, schedule_patterns, system_settings)

## Key Changes

### Tables Removed
- ❌ `sections` table (completely removed)

### Tables Updated
- ✅ `user_roles`: Removed `section_id`, added `company_id`
- ✅ `shift_requirements`: Removed `section_id`, uses `company_id` only
- ✅ `schedules`: Removed `section_id`, uses `company_id` only
- ✅ `employees`: Added `company_id` directly
- ✅ `shift_requests`: Added `company_id`
- ✅ `employee_breaks`: Added `company_id`
- ✅ `schedule_shifts`: Added `company_id`
- ✅ `schedule_assignments`: Added `company_id`

### Roles Updated
- Only 3 roles: `Admin`, `Team Leader`, `Employee`
- Removed: `Director`, `Supervisor`, `Senior`

### Stored Procedures Updated
All procedures now use `company_id` instead of `section_id`:
- `sp_verify_login` - Added `p_company_id` parameter
- `sp_get_shift_requirements` - Uses `p_company_id` instead of `p_section_id`
- `sp_set_shift_requirement` - Uses `p_company_id` instead of `p_section_id`
- `sp_generate_weekly_schedule` - Uses `p_company_id` instead of `p_section_id`
- `sp_get_weekly_schedule` - Uses `p_company_id` instead of `p_section_id`
- `sp_get_today_shift` - Uses `p_company_id` instead of `p_section_id`
- `sp_get_coverage_gaps` - Uses `p_company_id` instead of `p_section_id`
- `sp_get_break_status` - Uses `p_company_id` instead of `p_section_id`
- `sp_get_shift_requests` - Uses `p_company_id` instead of `p_section_id`
- `sp_create_employee` - Uses `p_company_id` instead of `p_section_id`
- `sp_create_leader` - Uses `p_company_id` instead of `p_section_id`
- `sp_get_employees_by_company` - New (replaces `sp_get_employees_by_section`)
- `sp_get_available_employees` - Uses `p_company_id` instead of `p_section_id`
- `sp_update_employee` - Uses `p_company_id` instead of `p_section_id`
- `sp_get_admin_directory` - Uses `p_company_id` parameter
- `sp_performance_report` - Uses `p_company_id` instead of `p_section_id`
- `sp_admin_dashboard` - Uses `p_company_id` instead of `p_section_id` (renamed from `sp_director_dashboard`)
- `sp_create_admin` - New (replaces `sp_create_supervisor`)
- `sp_upsert_week` - Added `p_company_id` parameter

### Removed Procedures
- ❌ `sp_get_all_sections` (no longer needed)
- ❌ `sp_create_supervisor` (replaced by `sp_create_admin`)
- ❌ `sp_director_dashboard` (replaced by `sp_admin_dashboard`)

## Database Credentials

- **Database**: `ShiftSchedulerDB`
- **User**: `shift_user`
- **Password**: `StrongPassword123!`

## Verification

After running the script, verify:
```sql
USE ShiftSchedulerDB;
SHOW TABLES;  -- Should NOT include 'sections'
SELECT role_name FROM roles;  -- Should show only: Admin, Team Leader, Employee
DESCRIBE user_roles;  -- Should have company_id, NOT section_id
```

## Notes

- ✅ MySQL 5.7 compatible (no LIMIT in subqueries with IN/ANY/ALL/SOME)
- ✅ All foreign keys properly defined
- ✅ All indexes optimized for company_id queries
- ✅ All stored procedures updated for company-only isolation

