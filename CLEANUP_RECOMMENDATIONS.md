# Cleanup Recommendations

This document lists files that can be safely removed or are no longer used in the current codebase.

## Files to Remove

### Unused Controllers
These controllers are not referenced in `ActionHandler.php` or `public/index.php`:

- `app/Controllers/AdminController.php` - Not used (old admin functionality)
- `app/Controllers/RequestController.php` - Not used (functionality moved to EmployeeController)
- `app/Controllers/SupervisorController.php` - Empty, only has `enforceAccess()` which is not called

**Action**: Can be removed if not needed for future features.

### Unused Views
These views are not rendered in the current application:

- `app/Views/dashboard/admin.php` - Old admin dashboard (uses `is_primary_admin()` which doesn't exist)
- `app/Views/dashboard/employee.php` - Old employee dashboard (replaced by `app/Views/employee/dashboard.php`)
- `app/Views/dashboard/overview.php` - Old overview (not used)
- `app/Views/shifts/admin-schedule.php` - Old admin schedule view (not used)
- `app/Views/shifts/employee-schedule.php` - Old employee schedule view (not used)

**Action**: Can be removed.

### Empty Directories
- `app/Views/partials/` - Empty (moved to `includes/`)
- `public/dashboard/` - Empty placeholder directory

**Action**: Can be removed if not needed.

### Documentation Files (Optional)
These are historical documentation files that may not be needed:

- `FIXES_SUMMARY.md` - Historical fix documentation
- `REORGANIZATION_COMPLETE.md` - Historical reorganization documentation
- `REORGANIZATION_PLAN.md` - Historical plan (already completed)
- `SECURITY_CHECKLIST.md` - Can be moved to `docs/` if needed

**Action**: Move to `docs/archive/` or remove if not needed.

### Old Database Scripts (Optional)
These can be kept for reference or removed:

- `database/clean_test_data.sql` - Replaced by `reset_and_seed.php`
- `database/reset_database.sql` - Replaced by `reset_and_seed.php`
- `database/test_data.sql` - Replaced by `reset_and_seed.php`
- `database/RESET_INSTRUCTIONS.md` - Can be updated to reference `reset_and_seed.php`

**Action**: Keep for reference or remove if `reset_and_seed.php` works correctly.

## Files to Keep

### Active Controllers
- `app/Controllers/AuthController.php` ✅
- `app/Controllers/DirectorController.php` ✅
- `app/Controllers/TeamLeaderController.php` ✅
- `app/Controllers/EmployeeController.php` ✅
- `app/Controllers/SeniorController.php` ✅

### Active Views
- `app/Views/auth/login.php` ✅
- `app/Views/director/` ✅
- `app/Views/employee/dashboard.php` ✅
- `app/Views/teamleader/dashboard.php` ✅
- `app/Views/supervisor/dashboard.php` ✅
- `app/Views/senior/dashboard.php` ✅

### Core Files
- `app/Core/Router.php` ✅
- `app/Core/ActionHandler.php` ✅
- All Models ✅
- All Helpers ✅

## Cleanup Script

To safely remove unused files, run:

```bash
# Backup first!
git add -A
git commit -m "Backup before cleanup"

# Remove unused controllers
rm app/Controllers/AdminController.php
rm app/Controllers/RequestController.php
rm app/Controllers/SupervisorController.php

# Remove unused views
rm app/Views/dashboard/admin.php
rm app/Views/dashboard/employee.php
rm/Views/dashboard/overview.php
rm app/Views/shifts/admin-schedule.php
rm app/Views/shifts/employee-schedule.php

# Remove empty directories (if empty)
rmdir app/Views/partials 2>/dev/null
rmdir public/dashboard 2>/dev/null

# Optional: Archive documentation
mkdir -p docs/archive
mv FIXES_SUMMARY.md docs/archive/ 2>/dev/null
mv REORGANIZATION_COMPLETE.md docs/archive/ 2>/dev/null
mv REORGANIZATION_PLAN.md docs/archive/ 2>/dev/null
```

## Verification

After cleanup, verify:
1. Application still works correctly
2. All dashboards load
3. All forms submit correctly
4. No broken includes or requires

