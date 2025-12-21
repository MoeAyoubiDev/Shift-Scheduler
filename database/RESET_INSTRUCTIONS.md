# Database Reset Instructions

## Quick Reset

To remove all existing data and insert fresh basic test data, run:

```bash
mysql -u shift_user -p ShiftSchedulerDB < database/reset_database.sql
```

Or if you're using root:

```bash
mysql -u root -p ShiftSchedulerDB < database/reset_database.sql
```

## What Gets Reset

### Deleted:
- All notifications
- All employee breaks
- All schedule assignments
- All schedule shifts
- All schedules
- All shift requirements
- All shift requests
- All employees
- All user roles
- All users (except director is recreated)
- All weeks

### Created:

#### Users (31 total):
- **1 Director** (username: `director`, password: `password`)
  - Access to both sections
  
- **4 Team Leaders** (password: `password123`)
  - `tl_app_001`, `tl_app_002` (App After-Sales)
  - `tl_agent_001`, `tl_agent_002` (Agent After-Sales)
  
- **2 Supervisors** (password: `password123`)
  - `sv_app_001` (App After-Sales)
  - `sv_agent_001` (Agent After-Sales)
  
- **4 Seniors** (password: `password123`)
  - `senior_app_001`, `senior_app_002` (App After-Sales)
  - `senior_agent_001`, `senior_agent_002` (Agent After-Sales)
  
- **20 Employees** (password: `password123`)
  - `emp_app_001` through `emp_app_010` (App After-Sales)
  - `emp_agent_001` through `emp_agent_010` (Agent After-Sales)

#### Weeks:
- 5 weeks created (current week + next 4 weeks)

## Login Credentials

| Role | Username | Password | Section |
|------|----------|----------|---------|
| Director | `director` | `password` | Both |
| Team Leader | `tl_app_001` | `password123` | App After-Sales |
| Team Leader | `tl_agent_001` | `password123` | Agent After-Sales |
| Supervisor | `sv_app_001` | `password123` | App After-Sales |
| Senior | `senior_app_001` | `password123` | App After-Sales |
| Employee | `emp_app_001` | `password123` | App After-Sales |

## Notes

- All passwords are `password123` except Director which uses `password`
- The script uses deterministic data generation (no random values)
- All foreign key constraints are properly handled
- The script is safe to run multiple times (it deletes everything first)

