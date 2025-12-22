# Database Migrations

This directory contains database migration scripts for the Shift Scheduler multi-tenant system.

## Migration Order

Migrations must be run in order:

1. `001_add_companies_table.sql` - Creates companies table and onboarding tracking
2. `002_add_company_id_to_tables.sql` - Adds company_id to all relevant tables
3. `003_update_stored_procedures.sql` - Updates stored procedures for multi-tenant support

## Running Migrations

### Manual Execution

```bash
# Run all migrations in order
mysql -u root -p ShiftSchedulerDB < database/migrations/001_add_companies_table.sql
mysql -u root -p ShiftSchedulerDB < database/migrations/002_add_company_id_to_tables.sql
mysql -u root -p ShiftSchedulerDB < database/migrations/003_update_stored_procedures.sql
```

### Using Migration Script

```bash
php database/migrations/run_migrations.php
```

## Migration Safety

- All migrations use `IF NOT EXISTS` and `IF EXISTS` checks where possible
- Foreign keys are added with proper constraints
- Unique constraints are updated to include company_id
- No data loss - migrations are additive

## Rollback

To rollback migrations, run the rollback scripts in reverse order:

```bash
# Rollback in reverse order
mysql -u root -p ShiftSchedulerDB < database/migrations/rollback/003_rollback.sql
mysql -u root -p ShiftSchedulerDB < database/migrations/rollback/002_rollback.sql
mysql -u root -p ShiftSchedulerDB < database/migrations/rollback/001_rollback.sql
```

## Notes

- Migrations assume the base schema from `database/database.sql` exists
- Run migrations on a backup first in production
- Test migrations in development environment

