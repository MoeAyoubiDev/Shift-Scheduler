# Local Testing Guide - Shift Scheduler

## Prerequisites

You need the following installed on your local machine:
- **PHP 8.0+** (with extensions: pdo_mysql, mbstring, session)
- **MySQL 5.7+** or **MariaDB 10.3+**
- **Web Server** (Apache or PHP built-in server)

## Step 1: Check PHP Installation

```bash
# Check PHP version
php -v

# Should show PHP 8.0 or higher

# Check required extensions
php -m | grep -E "pdo_mysql|mbstring|session"
```

If PHP is not installed:
- **macOS**: `brew install php`
- **Ubuntu/Debian**: `sudo apt install php php-mysql php-mbstring`
- **Windows**: Download from https://windows.php.net/download/

## Step 2: Setup Database

### 2.1 Start MySQL

```bash
# macOS (if installed via Homebrew)
brew services start mysql

# Or use MySQL directly
mysql.server start

# Ubuntu/Debian
sudo systemctl start mysql

# Windows: Start MySQL service from Services
```

### 2.2 Create Database and Import Schema

```bash
# Login to MySQL
mysql -u root -p

# In MySQL prompt, run:
```

```sql
-- Create database
CREATE DATABASE ShiftSchedulerDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Exit MySQL
EXIT;
```

```bash
# Import schema
mysql -u root -p ShiftSchedulerDB < database/schema.sql

# Import stored procedures (use fixed version)
mysql -u root -p ShiftSchedulerDB < database/stored_procedures_fixed.sql

# (Optional) Import sample data
mysql -u root -p ShiftSchedulerDB < database/init.sql
```

### 2.3 Configure Database Connection

Edit `config/database.php`:

```php
return [
    'host' => '127.0.0.1',
    'port' => '3306',
    'name' => 'ShiftSchedulerDB',
    'user' => 'root',  // Change if different
    'pass' => '',      // Your MySQL root password
];
```

## Step 3: Start Local Web Server

### Option A: PHP Built-in Server (Easiest)

```bash
# Navigate to project directory
cd /Users/joechamoun/Development/Shift-Scheduler

# Start PHP server (point to public directory)
php -S localhost:8000 -t public

# Server will run at: http://localhost:8000
```

### Option B: Apache (More Production-like)

1. **Configure Apache Virtual Host** (macOS with Homebrew):

```bash
# Edit Apache config
sudo nano /opt/homebrew/etc/httpd/httpd.conf

# Uncomment these lines:
# LoadModule rewrite_module lib/httpd/modules/mod_rewrite.so
# LoadModule php_module /opt/homebrew/opt/php/lib/httpd/modules/libphp.so

# Add at the end:
<VirtualHost *:80>
    ServerName shift-scheduler.local
    DocumentRoot "/Users/joechamoun/Development/Shift-Scheduler/public"
    <Directory "/Users/joechamoun/Development/Shift-Scheduler/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

2. **Add to /etc/hosts**:
```bash
sudo nano /etc/hosts
# Add: 127.0.0.1 shift-scheduler.local
```

3. **Restart Apache**:
```bash
sudo brew services restart httpd
```

## Step 4: Test the Application

### 4.1 Access the Application

Open your browser and go to:
- **PHP Built-in Server**: http://localhost:8000
- **Apache**: http://shift-scheduler.local or http://localhost

### 4.2 Login with Test Credentials

From `database/init.sql`, use these test accounts:

**Director:**
- Username: `director`
- Password: `director123`

**Team Leader:**
- Username: `teamleader`
- Password: `teamleader123`

### 4.3 Test Features by Role

#### Director
1. Login → Should see "Choose Section" page
2. Select a section → View dashboard
3. Click "View Section" → See section details

#### Team Leader
1. Login → Dashboard
2. **Manage Employees**: Create new employee
3. **Shift Requests**: View and approve/decline requests
4. **Schedule**: Generate weekly schedule
5. **Performance**: View analytics
6. **Breaks**: Monitor break management

#### Supervisor
1. Login → Dashboard
2. View Schedule (read-only)
3. View Performance Analytics
4. View Break Reports

#### Senior
1. Login → Today's Shift Dashboard
2. Start/End breaks for employees
3. View weekly schedule summary

#### Employee
1. Login → My Schedule
2. Submit shift request
3. Start/End break

## Step 5: Common Issues & Solutions

### Issue: "Database connection failed"
**Solution:**
- Check MySQL is running: `mysql.server status`
- Verify credentials in `config/database.php`
- Test connection: `mysql -u root -p ShiftSchedulerDB`

### Issue: "404 Not Found" on routes
**Solution:**
- Make sure you're using PHP built-in server with `-t public` flag
- Or ensure Apache `.htaccess` is in `public/` directory
- Check `public/.htaccess` exists

### Issue: "Class not found" errors
**Solution:**
- Make sure `app/bootstrap.php` is being loaded
- Check `public/index.php` includes bootstrap
- Verify all files are in correct directories

### Issue: "Stored procedure not found"
**Solution:**
- Make sure you imported `stored_procedures_fixed.sql`
- Verify you're using the correct database: `USE ShiftSchedulerDB;`
- Check procedures exist: `SHOW PROCEDURE STATUS;`

### Issue: Session errors
**Solution:**
- Check PHP session extension is enabled: `php -m | grep session`
- Verify `app/Core/Session.php` is loaded
- Check file permissions on session directory

## Step 6: Debugging

### Enable Error Display (Development Only)

Add to `public/index.php` at the top:

```php
<?php
// Development only - remove in production!
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
```

### Check PHP Errors

```bash
# View PHP error log
tail -f /var/log/php_errors.log

# Or if using PHP built-in server, errors show in terminal
```

### Test Database Connection

Create `test_db.php` in project root:

```php
<?php
require_once 'app/bootstrap.php';

try {
    $db = Database::getInstance();
    echo "✅ Database connection successful!\n";
    
    // Test stored procedure
    $result = Database::callProcedure('sp_get_user_roles', [1]);
    echo "✅ Stored procedures working!\n";
    print_r($result);
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
```

Run: `php test_db.php`

## Step 7: Quick Test Checklist

- [ ] PHP server starts without errors
- [ ] Can access http://localhost:8000
- [ ] Login page loads
- [ ] Can login with test credentials
- [ ] Director can choose section
- [ ] Team Leader can see dashboard
- [ ] Can create employee
- [ ] Can submit shift request
- [ ] Can approve/decline request
- [ ] Can generate schedule
- [ ] Can start/end break
- [ ] CSV export works

## Step 8: Stop the Server

**PHP Built-in Server:**
- Press `Ctrl+C` in terminal

**Apache:**
```bash
sudo brew services stop httpd
```

## Additional Testing Tools

### Test with Different Browsers
- Chrome
- Firefox
- Safari
- Edge

### Test Responsive Design
- Use browser DevTools (F12)
- Test mobile views (iPhone, iPad sizes)

### Test Security
- Try accessing protected routes without login
- Test CSRF token validation
- Verify password hashing works

## Next Steps

Once local testing passes:
1. Create Pull Request on GitHub
2. Deploy to staging server
3. Test on staging environment
4. Deploy to production

