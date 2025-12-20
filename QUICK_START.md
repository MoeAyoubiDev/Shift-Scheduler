# Quick Start - Local Testing

## Fastest Way to Test Locally

### 1. Setup Database (One Time)

```bash
# Create database and import schema
mysql -u root -p < database/schema.sql

# Import stored procedures
mysql -u root -p ShiftSchedulerDB < database/stored_procedures_fixed.sql

# (Optional) Add test users
mysql -u root -p ShiftSchedulerDB < database/init.sql
```

### 2. Configure Database

Edit `config/database.php` with your MySQL credentials:
```php
'user' => 'root',
'pass' => 'your_password_here',
```

### 3. Start Server

**Option A: Use the start script**
```bash
./start-local.sh
```

**Option B: Manual start**
```bash
php -S localhost:8000 -t public
```

### 4. Open Browser

Go to: **http://localhost:8000**

### 5. Login

Use test credentials:
- **Director**: `director` / `director123`
- **Team Leader**: `teamleader` / `teamleader123`

## What to Test

1. ✅ **Login** - Should redirect based on role
2. ✅ **Director** - Choose section, view dashboard
3. ✅ **Team Leader** - Create employee, approve requests, generate schedule
4. ✅ **Employee** - Submit request, manage break
5. ✅ **Break System** - Start/end break, see delays

## Troubleshooting

**Database connection error?**
```bash
# Test connection
mysql -u root -p ShiftSchedulerDB -e "SELECT 1;"
```

**404 errors?**
- Make sure you're using: `php -S localhost:8000 -t public`
- The `-t public` flag is important!

**Class not found?**
- Check `app/bootstrap.php` exists
- Verify `public/index.php` includes bootstrap

## Stop Server

Press `Ctrl+C` in the terminal

