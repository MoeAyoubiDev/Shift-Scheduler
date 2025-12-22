# Deployment Instructions - Fixed Business Logic

## Overview

The business logic has been completely rewritten from scratch with a clean, robust action routing system. All buttons and forms now work correctly.

## What Was Fixed

1. **Clean Action Router System** - Centralized routing in `app/Core/Router.php` and `app/Core/ActionHandler.php`
2. **Fixed Form Submissions** - Forms now submit correctly without JavaScript interference
3. **Fixed Button Clicks** - All navigation buttons work properly
4. **AJAX Support** - Forms with `data-ajax="true"` use AJAX, others submit normally
5. **Proper Error Handling** - All actions return consistent response format

## Files Changed

### New Files Created:
- `app/Core/Router.php` - Action routing system
- `app/Core/ActionHandler.php` - Action handler initialization

### Files Updated:
- `public/index.php` - Clean routing using ActionHandler
- `public/assets/js/dashboard.js` - Fixed form handling, no interference with normal forms
- `app/Controllers/EmployeeController.php` - Fixed Sunday request validation

## Server Deployment Steps

### Step 1: Backup Current Code
```bash
cd /var/www/shift-scheduler
cp -r app app.backup
cp -r public public.backup
```

### Step 2: Pull Latest Code
```bash
cd /var/www/shift-scheduler
git pull origin main
# or
git pull origin master
```

### Step 3: Verify New Files
```bash
ls -la app/Core/Router.php
ls -la app/Core/ActionHandler.php
```

### Step 4: Set Permissions
```bash
sudo chown -R www-data:www-data /var/www/shift-scheduler
sudo chmod -R 755 /var/www/shift-scheduler
sudo chmod -R 775 /var/www/shift-scheduler/public/assets
```

### Step 5: Clear PHP Cache
```bash
# Restart PHP-FPM (IMPORTANT!)
sudo systemctl restart php8.2-fpm
# or
sudo systemctl restart php-fpm

# Clear opcache
php -r "opcache_reset();"
```

### Step 6: Reload Web Server
```bash
# For Nginx
sudo systemctl reload nginx

# For Apache
sudo systemctl reload apache2
```

### Step 7: Test Application
1. Login to the application
2. Test navigation buttons (should switch sections)
3. Test form submissions (should work correctly)
4. Test assign shift functionality
5. Test all role-specific actions

## Quick Deployment Script

Create a file `scripts/deploy-fix.sh`:

```bash
#!/bin/bash
set -e

cd /var/www/shift-scheduler

echo "Backing up..."
cp -r app app.backup.$(date +%Y%m%d_%H%M%S)

echo "Pulling latest code..."
git pull

echo "Setting permissions..."
sudo chown -R www-data:www-data /var/www/shift-scheduler
sudo chmod -R 755 /var/www/shift-scheduler
sudo chmod -R 775 /var/www/shift-scheduler/public/assets

echo "Restarting PHP-FPM..."
sudo systemctl restart php8.2-fpm || sudo systemctl restart php-fpm

echo "Reloading web server..."
sudo systemctl reload nginx || sudo systemctl reload apache2

echo "Clearing cache..."
php -r "opcache_reset();" || true

echo "✅ Deployment complete!"
echo "Please test the application to verify all buttons work."
```

Make it executable:
```bash
chmod +x scripts/deploy-fix.sh
```

Run it:
```bash
./scripts/deploy-fix.sh
```

## Verification Checklist

After deployment, verify:

- [ ] Login works
- [ ] Navigation cards switch sections
- [ ] Widget clicks navigate correctly
- [ ] Quick action cards work
- [ ] Forms submit correctly
- [ ] Assign shift form works (AJAX)
- [ ] Create employee form works
- [ ] Submit request form works
- [ ] Update request status works
- [ ] All buttons respond to clicks
- [ ] No JavaScript errors in console

## Troubleshooting

### Buttons Still Not Working

1. **Clear browser cache**: Press Ctrl+F5 (Windows) or Cmd+Shift+R (Mac)
2. **Check JavaScript console**: Open browser DevTools (F12) and check for errors
3. **Verify file permissions**: Ensure all files are readable by web server
4. **Check PHP errors**: Check `/var/log/php8.2-fpm.log` or Apache error log

### Forms Not Submitting

1. **Check form action**: Should be `action="/index.php"`
2. **Verify CSRF token**: Form must include `csrf_token` hidden field
3. **Check action parameter**: Form must include `name="action"` hidden field
4. **Test without JavaScript**: Disable JS and test - forms should still work

### AJAX Forms Not Working

1. **Verify data-ajax attribute**: Form must have `data-ajax="true"`
2. **Check network tab**: Open DevTools Network tab and verify request is sent
3. **Check response**: Verify server returns JSON response
4. **Check console**: Look for JavaScript errors

## Rollback Instructions

If issues occur, rollback:

```bash
cd /var/www/shift-scheduler
rm -rf app/Core
mv app.backup app
mv public.backup public
sudo systemctl restart php8.2-fpm
sudo systemctl reload nginx
```

## Support

If you encounter issues:
1. Check browser console for JavaScript errors
2. Check server error logs
3. Verify all files were updated correctly
4. Test with a fresh browser session (incognito mode)

