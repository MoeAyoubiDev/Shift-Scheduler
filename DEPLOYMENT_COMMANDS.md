# Production Deployment Commands

## After Git Pull on Server

Run these commands in order:

```bash
git pull
php database/setup_production.php
systemctl restart php8.1-fpm
systemctl reload nginx
```

## Alternative: If Database Already Exists

If the database schema already exists but needs reset:

```bash
git pull
php database/reset_database_production.php
systemctl restart php8.1-fpm
systemctl reload nginx
```

## Manual Migration (If Setup Script Fails)

```bash
mysql -u root -p ShiftSchedulerDB < database/database.sql
mysql -u root -p ShiftSchedulerDB < database/migrations/001_add_companies_table.sql
mysql -u root -p ShiftSchedulerDB < database/migrations/002_add_company_id_to_tables.sql
mysql -u root -p ShiftSchedulerDB < database/migrations/003_update_stored_procedures.sql
php database/reset_database_production.php
systemctl restart php8.1-fpm
systemctl reload nginx
```

## Verify Setup

Check that signup.php works:

```bash
curl -I https://shiftscheduler.online/signup.php
```

Should return HTTP 200, not 500.

