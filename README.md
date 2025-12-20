# Shift Scheduler System

A complete production-ready PHP/MySQL shift scheduling system with role-based access control, automated schedule generation, break management, and performance analytics.

## Features

### User Roles & Permissions

1. **Director**
   - Access to both sections (App After-Sales & Agent After-Sales)
   - Read-only access to employees, schedules, performance, and statistics
   - Section selection after login

2. **Team Leader** (Full Permissions)
   - Full CRUD permissions in assigned section
   - Create & manage employees
   - Approve/decline shift requests
   - Generate weekly schedules
   - Edit generated schedules manually
   - Monitor breaks & delays
   - View performance analytics
   - Export schedules as CSV

3. **Supervisor**
   - Read-only access to assigned section
   - View schedules, employees, performance, and break reports

4. **Senior**
   - Does NOT submit shift requests
   - Manages TODAY's shift only
   - Can see employees working in current shift (AM/MID/PM/NIGHT)
   - Can assign and control breaks
   - Can monitor who is late to break or return
   - Can view weekly schedule summary

5. **Employee**
   - Can submit shift requests (Monday-Saturday, Sunday blocked)
   - Can view weekly schedule
   - Can start and end ONE 30-minute shift break per day

### Core Features

- **Shift Requests**: Employees submit requests with date, shift type, importance, reason, and schedule pattern (5 or 6 days). Requests allowed Monday-Saturday (Sunday blocked).
- **Weekly Schedule Generation**: Team Leader defines required employees per shift per day. System generates schedule based on approved requests, seniority, patterns, and off days. Schedule can be edited manually after generation.
- **Break Management**: One 30-minute break per employee per shift. System calculates break duration and delay minutes.
- **Performance Analytics**: Filter by month, date range, employee, or section. Shows employee name, days worked, total delay minutes, average delay. Sorted from LOW delay → HIGH delay.

## Technology Stack

- **Backend**: PHP 8+ (MVC Architecture)
- **Database**: MySQL with stored procedures for all business logic
- **Frontend**: HTML5, CSS3, JavaScript
- **Security**: Password hashing (bcrypt), prepared statements, session-based authentication, CSRF protection

## Project Structure

```
Shift-Scheduler/
├── app/
│   ├── Controllers/
│   │   ├── AuthController.php
│   │   ├── DirectorController.php
│   │   ├── TeamLeaderController.php
│   │   ├── SupervisorController.php
│   │   ├── SeniorController.php
│   │   └── EmployeeController.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Employee.php
│   │   ├── Schedule.php
│   │   ├── ShiftRequest.php
│   │   ├── Break.php
│   │   ├── Performance.php
│   │   └── ...
│   ├── Views/
│   │   ├── auth/
│   │   ├── director/
│   │   ├── teamleader/
│   │   ├── supervisor/
│   │   ├── senior/
│   │   ├── employee/
│   │   └── partials/
│   ├── Core/
│   │   └── config.php
│   └── Helpers/
│       ├── helpers.php
│       └── view.php
├── config/
│   ├── database.php
│   └── app.php
├── database/
│   └── database.sql
├── public/
│   ├── index.php
│   └── assets/
│       ├── css/
│       ├── js/
│       └── img/
└── README.md
```

## Installation & Setup

### Local Development

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd Shift-Scheduler
   ```

2. **Create MySQL database**
   ```bash
   mysql -u root -p
   ```
   Then import the schema:
   ```bash
   mysql -u root -p < database/database.sql
   ```
   Or manually:
   ```sql
   source database/database.sql;
   ```

3. **Configure database connection**
   Edit `config/database.php`:
   ```php
   return [
       'host' => '127.0.0.1',
       'port' => '3306',
       'name' => 'ShiftSchedulerDB',
       'user' => 'your_username',
       'pass' => 'your_password',
   ];
   ```

4. **Set up web server**
   - Point document root to `public/` directory
   - For PHP built-in server:
     ```bash
     php -S localhost:8000 -t public
     ```
   - For Apache/Nginx, configure virtual host pointing to `public/`

5. **Access the application**
   - Open browser: `http://localhost:8000`
   - Login with seeded accounts (see Database Seed Data below)

### Production Deployment (DigitalOcean Ubuntu Server)

#### Prerequisites
- Ubuntu 20.04+ server
- Root or sudo access
- Domain name (optional, for SSL)

#### Step 1: Update System
```bash
sudo apt update && sudo apt upgrade -y
```

#### Step 2: Install PHP 8+
```bash
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.2 php8.2-fpm php8.2-mysql php8.2-mbstring php8.2-xml php8.2-curl
```

#### Step 3: Install MySQL
```bash
sudo apt install -y mysql-server
sudo mysql_secure_installation
```

Create database and user:
```bash
sudo mysql -u root -p
```
```sql
CREATE DATABASE ShiftSchedulerDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'shift_user'@'localhost' IDENTIFIED BY 'StrongPassword123!';
GRANT ALL PRIVILEGES ON ShiftSchedulerDB.* TO 'shift_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

Import schema:
```bash
mysql -u shift_user -p ShiftSchedulerDB < database/database.sql
```

#### Step 4: Install Nginx
```bash
sudo apt install -y nginx
```

Create Nginx configuration `/etc/nginx/sites-available/shift-scheduler`:
```nginx
server {
    listen 80;
    server_name your-domain.com www.your-domain.com;
    root /var/www/shift-scheduler/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

Enable site:
```bash
sudo ln -s /etc/nginx/sites-available/shift-scheduler /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

#### Step 5: Deploy Application
```bash
cd /var/www
sudo git clone <repository-url> shift-scheduler
cd shift-scheduler
sudo chown -R www-data:www-data /var/www/shift-scheduler
sudo chmod -R 755 /var/www/shift-scheduler
```

Update database config:
```bash
sudo nano config/database.php
```

#### Step 6: Install SSL with Let's Encrypt
```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d your-domain.com -d www.your-domain.com
```

Follow prompts and SSL will be configured automatically.

#### Step 7: Configure PHP-FPM
Edit `/etc/php/8.2/fpm/php.ini`:
```ini
upload_max_filesize = 10M
post_max_size = 10M
memory_limit = 256M
```

Restart PHP-FPM:
```bash
sudo systemctl restart php8.2-fpm
```

#### Step 8: Set Permissions
```bash
sudo chown -R www-data:www-data /var/www/shift-scheduler
sudo chmod -R 755 /var/www/shift-scheduler
sudo chmod -R 775 /var/www/shift-scheduler/public/assets
```

## Database Seed Data

Default test accounts (password: `password`):
- **Director**: `director` (access to both sections)
- **Team Leader**: `teamleader` (App After-Sales section)
- **Employee**: `employee` (App After-Sales section)

**Important**: Change all default passwords in production!

## Security Features

- Password hashing using bcrypt
- Prepared statements for all database queries
- Session-based authentication
- Role & section-based access control
- CSRF protection on all forms
- Input validation and sanitization

## Business Logic

All business logic is implemented in MySQL stored procedures:
- `sp_verify_login` - User authentication
- `sp_create_employee` - Employee creation
- `sp_submit_shift_request` - Shift request submission (with Sunday blocking and Senior validation)
- `sp_update_shift_request_status` - Approve/decline requests
- `sp_generate_weekly_schedule` - Automatic schedule generation
- `sp_get_weekly_schedule` - Retrieve schedules
- `sp_start_break` / `sp_end_break` - Break management
- `sp_performance_report` - Performance analytics
- And more...

## API Endpoints (via POST actions)

- `action=login` - User login
- `action=logout` - User logout
- `action=select_section` - Director section selection
- `action=create_employee` - Create new employee (Team Leader)
- `action=submit_request` - Submit shift request (Employee)
- `action=update_request_status` - Approve/decline request (Team Leader)
- `action=save_requirements` - Save shift requirements (Team Leader)
- `action=generate_schedule` - Generate weekly schedule (Team Leader)
- `action=update_assignment` - Update schedule assignment (Team Leader)
- `action=start_break` / `action=end_break` - Break management

## CSV Export

Team Leaders can export weekly schedules as CSV:
```
/index.php?download=schedule
```

## Deployment & Updates

### After Git Pull - Changes Not Appearing

If you've pulled code changes but they're not showing on your server, follow these steps:

#### Quick Fix (Recommended)
Run the deployment script:
```bash
cd /var/www/shift-scheduler
chmod +x deploy.sh
./deploy.sh
```

#### Manual Steps

1. **Restart PHP-FPM** (Most Important!)
   ```bash
   # Find your PHP version
   php -v
   
   # Restart PHP-FPM (replace 8.2 with your version)
   sudo systemctl restart php8.2-fpm
   # OR
   sudo systemctl restart php-fpm
   ```

2. **Clear PHP Opcache**
   - Visit: `http://your-domain/clear_cache.php`
   - OR run: `php -r "opcache_reset();"`

3. **Reload Web Server**
   ```bash
   # For Nginx
   sudo systemctl reload nginx
   
   # For Apache
   sudo systemctl reload apache2
   ```

4. **Verify Files Were Updated**
   ```bash
   cd /var/www/shift-scheduler
   git log -1 --stat
   git status
   ```

5. **Check File Permissions**
   ```bash
   sudo chown -R www-data:www-data /var/www/shift-scheduler
   sudo find /var/www/shift-scheduler -type d -exec chmod 755 {} \;
   sudo find /var/www/shift-scheduler -type f -exec chmod 644 {} \;
   ```

6. **Hard Refresh Browser**
   - Press `Ctrl+F5` (Windows/Linux) or `Cmd+Shift+R` (Mac)
   - Or clear browser cache

#### Common Causes

- **PHP Opcache**: PHP caches compiled code. Restart PHP-FPM to clear it.
- **PHP-FPM Process Pool**: Old processes may still have old code in memory.
- **Browser Cache**: Your browser may be caching old CSS/JS files.
- **File Permissions**: Files may not be readable by the web server.
- **Wrong Directory**: Make sure you're pulling to the correct location.

## Troubleshooting

### Database Connection Issues
- Verify database credentials in `config/database.php`
- Check MySQL service: `sudo systemctl status mysql`
- Test connection: `mysql -u shift_user -p ShiftSchedulerDB`

### Permission Issues
- Ensure `www-data` owns application files
- Check file permissions (755 for directories, 644 for files)

### PHP Errors
- Check PHP error log: `/var/log/php8.2-fpm.log`
- Enable error display in development (disable in production)

### Changes Not Appearing After Git Pull
See "Deployment & Updates" section above for detailed steps.

## Support

For issues or questions, please refer to the project documentation or contact the development team.

## License

[Your License Here]
