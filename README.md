# Shift Scheduler System

A complete production-ready shift scheduling system built with PHP (MVC architecture) and MySQL.

## Features

- **Role-Based Access Control**: Director, Team Leader, Supervisor, Senior, and Employee roles with specific permissions
- **Shift Request Management**: Employees can submit shift requests (Monday-Saturday only)
- **Schedule Generation**: Automated weekly schedule generation based on approved requests
- **Break Management**: 30-minute break tracking with delay monitoring
- **Performance Analytics**: Comprehensive reporting with filtering options
- **CSV Export**: Export schedules as CSV files

## Requirements

- PHP 8.0 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- SSL certificate (for production)

## Installation

### 1. Database Setup

```bash
# Import database schema
mysql -u root -p < database/schema.sql

# Import stored procedures (use fixed version for PHP compatibility)
mysql -u root -p < database/stored_procedures_fixed.sql
# OR use the original version
# mysql -u root -p < database/stored_procedures.sql
```

### 2. Configuration

Update `config/database.php` with your database credentials:

```php
return [
    'host' => '127.0.0.1',
    'port' => '3306',
    'name' => 'ShiftSchedulerDB',
    'user' => 'your_db_user',
    'pass' => 'your_db_password',
];
```

Or set environment variables:
- `DB_HOST`
- `DB_PORT`
- `DB_NAME`
- `DB_USER`
- `DB_PASSWORD`

### 3. Web Server Configuration

#### Apache (.htaccess)

Create `.htaccess` in the `public` directory:

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

#### Nginx

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/Shift-Scheduler/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

### 4. Permissions

```bash
chmod -R 755 /path/to/Shift-Scheduler
chown -R www-data:www-data /path/to/Shift-Scheduler
```

## User Roles & Permissions

### Director
- Can access both sections (App After-Sales and Agent After-Sales)
- Read-only access to all data
- Must choose section after login

### Team Leader
- Full CRUD permissions in assigned section
- Create & manage employees
- Approve/decline shift requests
- Generate and edit weekly schedules
- Monitor breaks & delays
- View performance analytics
- Export schedules as CSV

### Supervisor
- Read-only access to assigned section
- View schedules, employees, performance, and break reports

### Senior
- Manages today's shift only
- Can see employees working in current shift
- Can assign and control breaks
- Can monitor who is late to break or return
- Can view weekly schedule summary

### Employee
- Can submit shift requests
- Can view weekly schedule
- Can start and end one 30-minute shift break per day

## Database Structure

The system uses the following main tables:
- `users` - User accounts
- `roles` - User roles
- `sections` - Company sections
- `user_roles` - User-role-section assignments
- `employees` - Employee information
- `shift_definitions` - Shift types
- `shift_requests` - Employee shift requests
- `schedules` - Weekly schedules
- `schedule_assignments` - Employee-shift assignments
- `employee_breaks` - Break tracking
- `system_settings` - System configuration

All business logic is implemented using MySQL stored procedures.

## Security Features

- Password hashing using bcrypt
- Prepared statements for all database queries
- Session-based authentication
- CSRF protection
- Role & section-based access control

## Deployment (DigitalOcean Ubuntu Server)

### 1. Server Setup

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP and extensions
sudo apt install php8.1-fpm php8.1-mysql php8.1-mbstring php8.1-xml -y

# Install MySQL
sudo apt install mysql-server -y

# Install Nginx
sudo apt install nginx -y
```

### 2. SSL Setup (Let's Encrypt)

```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx -y

# Obtain certificate
sudo certbot --nginx -d your-domain.com
```

### 3. Database Setup

```bash
# Secure MySQL installation
sudo mysql_secure_installation

# Create database and user
sudo mysql -u root -p
```

```sql
CREATE DATABASE ShiftSchedulerDB;
CREATE USER 'shift_user'@'localhost' IDENTIFIED BY 'StrongPassword123!';
GRANT ALL PRIVILEGES ON ShiftSchedulerDB.* TO 'shift_user'@'localhost';
FLUSH PRIVILEGES;
```

### 4. Deploy Application

```bash
# Clone or upload files
cd /var/www
sudo git clone your-repo Shift-Scheduler
# or upload via SFTP

# Set permissions
sudo chown -R www-data:www-data /var/www/Shift-Scheduler
sudo chmod -R 755 /var/www/Shift-Scheduler
```

### 5. Configure Nginx

Create `/etc/nginx/sites-available/shift-scheduler`:

```nginx
server {
    listen 80;
    server_name your-domain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name your-domain.com;
    root /var/www/Shift-Scheduler/public;
    index index.php;

    ssl_certificate /etc/letsencrypt/live/your-domain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/your-domain.com/privkey.pem;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    location ~ /\. {
        deny all;
    }
}
```

```bash
# Enable site
sudo ln -s /etc/nginx/sites-available/shift-scheduler /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

## Creating Initial Users

```sql
-- Create a Director user
INSERT INTO users (username, password_hash, email) 
VALUES ('director', '$2y$10$...', 'director@example.com');

-- Assign Director role to both sections
INSERT INTO user_roles (user_id, role_id, section_id)
SELECT u.id, r.id, s.id
FROM users u, roles r, sections s
WHERE u.username = 'director' 
  AND r.role_name = 'Director';
```

Use PHP to generate password hashes:
```php
echo password_hash('your_password', PASSWORD_BCRYPT);
```

## Support

For issues or questions, please refer to the documentation or contact the development team.

## License

Proprietary - All rights reserved.
