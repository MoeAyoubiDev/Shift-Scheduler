# Deployment Guide - Shift Scheduler System

## Quick Start

### 1. Database Setup

```bash
# Create database and import schema
mysql -u root -p < database/schema.sql

# Import stored procedures
mysql -u root -p < database/stored_procedures.sql

# Initialize with sample data (optional)
mysql -u root -p < database/init.sql
```

### 2. Configure Database

Edit `config/database.php`:

```php
return [
    'host' => '127.0.0.1',
    'port' => '3306',
    'name' => 'ShiftSchedulerDB',
    'user' => 'your_db_user',
    'pass' => 'your_db_password',
];
```

### 3. Set Web Server Document Root

Point your web server's document root to the `public` directory:

- **Apache**: Set `DocumentRoot` to `/path/to/Shift-Scheduler/public`
- **Nginx**: Set `root` to `/path/to/Shift-Scheduler/public`

### 4. Set Permissions

```bash
chmod -R 755 /path/to/Shift-Scheduler
chown -R www-data:www-data /path/to/Shift-Scheduler
```

## DigitalOcean Ubuntu Server Deployment

### Step 1: Server Preparation

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install required packages
sudo apt install -y php8.1-fpm php8.1-mysql php8.1-mbstring php8.1-xml php8.1-curl
sudo apt install -y mysql-server nginx
sudo apt install -y git
```

### Step 2: MySQL Configuration

```bash
# Secure MySQL
sudo mysql_secure_installation

# Create database and user
sudo mysql -u root -p
```

```sql
CREATE DATABASE ShiftSchedulerDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'shift_user'@'localhost' IDENTIFIED BY 'YourStrongPassword123!';
GRANT ALL PRIVILEGES ON ShiftSchedulerDB.* TO 'shift_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

```bash
# Import database
mysql -u shift_user -p ShiftSchedulerDB < database/schema.sql
mysql -u shift_user -p ShiftSchedulerDB < database/stored_procedures.sql
```

### Step 3: Application Deployment

```bash
# Create web directory
sudo mkdir -p /var/www/shift-scheduler
sudo chown -R $USER:$USER /var/www/shift-scheduler

# Clone or upload files
cd /var/www/shift-scheduler
# Upload your files here or use git

# Set permissions
sudo chown -R www-data:www-data /var/www/shift-scheduler
sudo chmod -R 755 /var/www/shift-scheduler
```

### Step 4: Configure Nginx

Create `/etc/nginx/sites-available/shift-scheduler`:

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/shift-scheduler/public;
    index index.php;

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

### Step 5: SSL Setup (Let's Encrypt)

```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx -y

# Obtain certificate
sudo certbot --nginx -d your-domain.com

# Auto-renewal is set up automatically
```

### Step 6: Update Database Config

Edit `/var/www/shift-scheduler/config/database.php`:

```php
return [
    'host' => '127.0.0.1',
    'port' => '3306',
    'name' => 'ShiftSchedulerDB',
    'user' => 'shift_user',
    'pass' => 'YourStrongPassword123!',
];
```

### Step 7: Test Installation

1. Visit `http://your-domain.com` (or `https://your-domain.com` after SSL)
2. Login with sample credentials:
   - Username: `director`
   - Password: `director123` (change this in production!)

## Creating Users

### Using PHP Script

Create `create_user.php`:

```php
<?php
require_once 'app/bootstrap.php';

$username = 'newuser';
$password = 'securepassword';
$email = 'user@example.com';

$user = new User();
$userId = $user->create($username, $password, $email);

// Assign role
$db = Database::getInstance();
$stmt = $db->prepare("
    INSERT INTO user_roles (user_id, role_id, section_id)
    SELECT ?, r.id, s.id
    FROM roles r, sections s
    WHERE r.role_name = ? AND s.section_name = ?
");
$stmt->execute([$userId, 'Employee', 'App After-Sales']);

echo "User created successfully!";
```

### Using MySQL

```sql
-- Create user
INSERT INTO users (username, password_hash, email) 
VALUES ('username', '$2y$10$...', 'email@example.com');

-- Generate password hash using PHP:
-- echo password_hash('your_password', PASSWORD_BCRYPT);
```

## Security Checklist

- [ ] Change default passwords
- [ ] Enable SSL/HTTPS
- [ ] Set proper file permissions (755 for directories, 644 for files)
- [ ] Configure firewall (UFW)
- [ ] Set up regular database backups
- [ ] Enable MySQL binary logging
- [ ] Configure PHP security settings
- [ ] Set up monitoring and logging
- [ ] Regular security updates

## Firewall Configuration

```bash
# Allow SSH, HTTP, HTTPS
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
```

## Backup Strategy

### Database Backup

```bash
# Create backup script
sudo nano /usr/local/bin/backup-shift-scheduler.sh
```

```bash
#!/bin/bash
BACKUP_DIR="/var/backups/shift-scheduler"
DATE=$(date +%Y%m%d_%H%M%S)
mkdir -p $BACKUP_DIR
mysqldump -u shift_user -p'YourPassword' ShiftSchedulerDB > $BACKUP_DIR/db_backup_$DATE.sql
find $BACKUP_DIR -name "db_backup_*.sql" -mtime +30 -delete
```

```bash
# Make executable
sudo chmod +x /usr/local/bin/backup-shift-scheduler.sh

# Add to crontab (daily at 2 AM)
sudo crontab -e
# Add: 0 2 * * * /usr/local/bin/backup-shift-scheduler.sh
```

## Troubleshooting

### Database Connection Issues

- Check MySQL service: `sudo systemctl status mysql`
- Verify credentials in `config/database.php`
- Test connection: `mysql -u shift_user -p ShiftSchedulerDB`

### Permission Issues

```bash
sudo chown -R www-data:www-data /var/www/shift-scheduler
sudo chmod -R 755 /var/www/shift-scheduler
sudo find /var/www/shift-scheduler -type f -exec chmod 644 {} \;
```

### PHP Errors

- Check PHP error log: `sudo tail -f /var/log/php8.1-fpm.log`
- Enable error display (development only): Add to `public/index.php`:
  ```php
  ini_set('display_errors', 1);
  error_reporting(E_ALL);
  ```

### Nginx Errors

- Check Nginx error log: `sudo tail -f /var/log/nginx/error.log`
- Test configuration: `sudo nginx -t`
- Reload: `sudo systemctl reload nginx`

## Performance Optimization

1. **Enable OPcache** (PHP):
   ```bash
   sudo nano /etc/php/8.1/fpm/php.ini
   # Set: opcache.enable=1
   sudo systemctl restart php8.1-fpm
   ```

2. **MySQL Optimization**:
   ```sql
   -- Add indexes if needed
   -- Monitor slow queries
   ```

3. **Nginx Caching** (optional):
   ```nginx
   location ~* \.(jpg|jpeg|png|gif|ico|css|js)$ {
       expires 1y;
       add_header Cache-Control "public, immutable";
   }
   ```

## Monitoring

- Set up log rotation
- Monitor disk space
- Set up uptime monitoring
- Configure email alerts for errors

## Support

For deployment issues, check:
1. Error logs (PHP, Nginx, MySQL)
2. File permissions
3. Database connectivity
4. Web server configuration

