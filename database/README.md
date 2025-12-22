# Database Scripts

## reset_and_seed.php

**Primary database reset and seeding script**

A comprehensive PHP script that safely resets all data and seeds the database with large, realistic test data.

### Features

- ✅ **Safe Reset**: Respects foreign key constraints, uses transactions
- ✅ **Idempotent**: Can be run multiple times safely
- ✅ **Large Dataset**: Creates hundreds/thousands of realistic records
- ✅ **Feature Coverage**: Tests all UI screens, CRUD operations, scheduling logic
- ✅ **Edge Cases**: Includes empty schedules, fully booked schedules, overlapping shifts

### Usage

```bash
# Method 1: Run as PHP script
php database/reset_and_seed.php

# Method 2: Run via MySQL (if script is pure SQL)
mysql -u root -p ShiftSchedulerDB < database/reset_and_seed.php
```

### What It Creates

- **12 Weeks**: 4 weeks past, current week, 7 weeks future
- **Users & Employees**:
  - 1 Director (access to both sections)
  - 2 Team Leaders per section (4 total)
  - 1 Supervisor per section (2 total)
  - 4 Seniors per section (8 total)
  - 50 Employees per section (100 total)
- **Shift Requirements**: For all weeks, all days, all shift types
- **Shift Requests**: 0-3 requests per employee for current + next 2 weeks
- **Schedules**: For current week and past 2 weeks
- **Schedule Shifts**: All shift definitions for each schedule
- **Schedule Assignments**: 50-100% coverage of requirements
- **Employee Breaks**: 60-80% of employees with break records for past 7 days
- **Notifications**: 0-5 notifications per user

### Default Credentials

- **Director**: `username='director'`, `password='password'`
- **All Others**: `username='{role}_{section}_{number}'`, `password='password123'`

Examples:
- `tl_app_1` (Team Leader, App After-Sales, #1)
- `sup_agent_1` (Supervisor, Agent After-Sales, #1)
- `sen_app_2` (Senior, App After-Sales, #2)
- `emp_app_001` (Employee, App After-Sales, #001)

### Data Characteristics

- **Realistic**: Uses meaningful names, dates, and values
- **Varied**: Different statuses, importance levels, assignment sources
- **Complete**: Covers all business logic paths
- **Testable**: Enough data to stress-test the application

## Other Scripts

### database.sql
Complete database schema with stored procedures. Run this first to create the database structure.

### clean_test_data.sql
Old cleanup script. Replaced by `reset_and_seed.php`.

### reset_database.sql
Old reset script. Replaced by `reset_and_seed.php`.

### test_data.sql
Old test data script. Replaced by `reset_and_seed.php`.

## Initial Setup

1. Create database schema:
   ```bash
   mysql -u root -p < database/database.sql
   ```

2. Reset and seed with test data:
   ```bash
   php database/reset_and_seed.php
   ```

## Troubleshooting

### Foreign Key Errors
The script disables foreign key checks during deletion. If you see FK errors during insertion, check:
- All reference data exists (roles, sections, shift_types, shift_definitions, schedule_patterns)
- Data is inserted in correct dependency order

### Duplicate Entry Errors
The script resets AUTO_INCREMENT counters. If you see duplicates:
- Ensure the script completed successfully
- Check that all DELETE statements executed
- Verify AUTO_INCREMENT reset worked

### Memory/Timeout Issues
For very large datasets, you may need to:
- Increase PHP memory limit: `php -d memory_limit=512M database/reset_and_seed.php`
- Increase MySQL timeout in `my.cnf`
- Run in smaller batches

## Notes

- The script uses transactions for safety
- All operations are logged to console
- Summary statistics are displayed at the end
- Script fails loudly on errors (no silent failures)

