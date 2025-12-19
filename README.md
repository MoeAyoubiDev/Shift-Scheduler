Shift Scheduler System
======================

This project is a lightweight PHP/MySQL application that lets team leaders collect weekly shift requests from employees, review/approve them as a Primary Admin, and generate a weekly schedule (including unmatched/no-request groupings). Secondary Admins can view everything in read-only mode.

## Features

- Static-credential login for Employees, Primary Admin, and Secondary Admin (seed data provided).
- Employees can submit weekly requests (day, shift type, day off, schedule option, reason, importance) Monday–Friday only; attempts after Friday or while submissions are blocked show the required error messaging.
- Primary Admin controls request approvals/declines/pending status, flags important requests, and can temporarily stop submissions for the current week.
- Shift requirements (AM/PM/MID headcount + senior staff notes) feed into an auto-generated weekly schedule with buckets for unmatched and no-request employees; Primary Admin can edit schedule entries after generation.
- Employees can filter their request history and view their personal schedule; admins can view all requests with previous-week context.

## Project Plan (2026 Professional Update)

**Project Overview**
- Web-based Employee Shift Scheduling System for directors, team leaders, senior staff, and employees.
- Automates weekly scheduling, improves visibility, and centralizes requests/approvals.
- Built with PHP 8.x and MySQL for compatibility, performance, and scalability.

**Objectives**
- Automate weekly scheduling and reduce manual errors.
- Provide role-based dashboards and real-time coverage visibility.
- Allow digital requests for day-offs and shift changes.
- Maintain a complete history of schedules, requests, and attendance.

**Users / Stakeholders**
- Employees, Senior Employees, Team Leaders, Directors/Administrators, HR/Management.

**Technical Requirements**
1. **Frontend**: HTML5, CSS3, JavaScript, Bootstrap.
2. **Backend**: PHP 8.x with business logic for scheduling, request rules, breaks, and weekly locking.
3. **Database**: MySQL/MariaDB with core tables for users, roles, sections, schedules, requests, and analytics.
4. **Additional Features**: role dashboards, week locking, secure auth, reporting exports, and analytics.

**Development Phases**
1. **Design**: database schema, user journeys, and wireframes for login, dashboards, schedules, and requests.
2. **Core System**: router, MVC structure, DB connection, and session handling.
3. **Modules**:
   - Director: global weekly view, analytics, and approvals.
   - Team Leader: schedule generation and week lock controls.
   - Senior: daily shifts and break tracking.
   - Employee: requests, schedule views, and status history.
4. **Testing & Refinement**: unit tests, scheduling accuracy, role permissions, and responsive UI polish.
5. **Documentation**: user manual, technical docs, deployment instructions.

**Expected Outcomes**
- Fully working role-based scheduling system.
- Transparent and fair scheduling with optimized coverage.
- Reduced administrative workload and improved communication.

**Current Repository Structure**
```
shift-system/
├── app/
│   ├── Controllers/
│   ├── Models/
│   ├── Views/
│   │   ├── auth/
│   │   ├── dashboard/
│   │   ├── partials/
│   │   └── shifts/
│   ├── Core/
│   └── Helpers/
├── config/
├── database/
│   └── database.sql
├── public/
│   ├── assets/
│   │   ├── css/
│   │   ├── js/
│   │   └── img/
│   └── index.php
├── .env
├── composer.json
└── README.md
```

## Getting started

1. Create a MySQL database (default name: `shift_scheduler`) and import the schema/seed data:

   ```bash
   mysql -u root -p shift_scheduler < database/database.sql
   ```

   The seed users share the password `password123`:

   - Primary Admin: `primaryadmin`
   - Secondary Admin: `secondaryadmin`
   - Employees: `alice`, `bob`

2. Configure database credentials via environment variables if needed:

   - `DB_HOST` (default `127.0.0.1`)
   - `DB_PORT` (default `3306`)
   - `DB_NAME` (default `shift_scheduler`)
   - `DB_USER` (default `root`)
   - `DB_PASSWORD` (default empty)

3. Serve the app (document root should be `public/`):

   ```bash
   php -S localhost:8000 -t public
   ```

4. Log in with one of the seeded accounts and start submitting/approving requests.

## Notes

- Submission blocks reset each Monday; the Primary Admin can re-enable submissions at any time.
- Generated schedules live in the database; export or email workflows can be layered on as follow-up work.
