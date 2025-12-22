# DigitalOcean Deployment Guide

This guide will help you set up automatic deployment on your DigitalOcean server using `git pull`.

## Initial Server Setup

### 1. First-Time Installation

Follow the steps in `README.md` under "Production Deployment (DigitalOcean Ubuntu Server)" to set up:
- PHP 8.2+
- MySQL
- Nginx
- SSL Certificate

### 2. Clone Repository

```bash
cd /var/www
sudo git clone <your-repository-url> shift-scheduler
cd shift-scheduler
```

### 3. Set Up Environment File

```bash
# Create .env file from template (if .env.example exists)
cp .env.example .env
# OR create manually:
nano .env
```

Add your production database credentials:
```env
DB_HOST=localhost
DB_PORT=3306
DB_NAME=ShiftSchedulerDB
DB_USER=shift_user
DB_PASSWORD=your_secure_password
APP_ENV=production
APP_DEBUG=false
APP_TIMEZONE=UTC
```

### 4. Set Initial Permissions

```bash
sudo chown -R www-data:www-data /var/www/shift-scheduler
sudo chmod +x deploy.sh post-deploy.sh
./deploy.sh
```

## Daily Deployment Workflow

After the initial setup, deploying updates is simple:

### Option 1: Quick Update (Easiest - Recommended)

```bash
cd /var/www/shift-scheduler
./update.sh
```

This script automatically runs `git pull` and then `deploy.sh` in one command.

### Option 2: Manual Deployment

```bash
cd /var/www/shift-scheduler
git pull
./deploy.sh
```

### Option 3: Automatic Post-Pull Hook

Set up a git hook to automatically run deployment after `git pull`:

```bash
cd /var/www/shift-scheduler
cat > .git/hooks/post-merge << 'EOF'
#!/bin/bash
cd /var/www/shift-scheduler
./post-deploy.sh
EOF

chmod +x .git/hooks/post-merge
```

Now every time you run `git pull`, the deployment script will run automatically.

### Option 4: SSH One-Liner

From your local machine, you can deploy with one command:

```bash
ssh user@your-server "cd /var/www/shift-scheduler && git pull && ./deploy.sh"
```

## What the Deployment Script Does

The `deploy.sh` script automatically:

1. ✅ Verifies you're in the correct directory
2. ✅ Checks git status and shows last commit
3. ✅ Sets proper file permissions (www-data:www-data)
4. ✅ Restarts PHP-FPM to clear opcache
5. ✅ Clears PHP opcache
6. ✅ Reloads Nginx/Apache
7. ✅ Verifies recent file changes
8. ✅ Checks environment configuration
9. ✅ Tests database connection

## Troubleshooting

### Changes Not Appearing After Git Pull

1. **Run the deployment script:**
   ```bash
   cd /var/www/shift-scheduler
   ./deploy.sh
   ```

2. **If that doesn't work, manually restart services:**
   ```bash
   sudo systemctl restart php8.2-fpm
   sudo systemctl reload nginx
   ```

3. **Clear cache via browser:**
   Visit: `http://your-domain/clear_cache.php`

4. **Hard refresh browser:**
   Press `Ctrl+F5` (Windows/Linux) or `Cmd+Shift+R` (Mac)

### Permission Errors

```bash
sudo chown -R www-data:www-data /var/www/shift-scheduler
sudo find /var/www/shift-scheduler -type d -exec chmod 755 {} \;
sudo find /var/www/shift-scheduler -type f -exec chmod 644 {} \;
```

### Database Connection Issues

1. Check `.env` file exists and has correct credentials
2. Verify MySQL is running: `sudo systemctl status mysql`
3. Test connection: `mysql -u shift_user -p ShiftSchedulerDB`

### PHP Errors

Check PHP-FPM error log:
```bash
sudo tail -f /var/log/php8.2-fpm.log
```

## Security Best Practices

1. **Never commit `.env` file** - It's in `.gitignore`
2. **Use strong database passwords** in production
3. **Keep PHP-FPM and Nginx updated**
4. **Regular backups** of database and files
5. **Monitor error logs** regularly

## Backup Before Deployment

It's good practice to backup before deploying:

```bash
# Backup database
mysqldump -u shift_user -p ShiftSchedulerDB > backup_$(date +%Y%m%d_%H%M%S).sql

# Backup files (optional)
tar -czf backup_files_$(date +%Y%m%d_%H%M%S).tar.gz /var/www/shift-scheduler
```

## Quick Reference

```bash
# Quick update (recommended)
cd /var/www/shift-scheduler && ./update.sh

# Or manual deployment
cd /var/www/shift-scheduler && git pull && ./deploy.sh

# Check status
git status
git log -1

# View logs
sudo tail -f /var/log/php8.2-fpm.log
sudo tail -f /var/log/nginx/error.log

# Restart services
sudo systemctl restart php8.2-fpm
sudo systemctl reload nginx
```

## Support

If you encounter issues:
1. Check the error logs
2. Verify file permissions
3. Ensure all services are running
4. Review the troubleshooting section above

