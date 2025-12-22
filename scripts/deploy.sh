#!/bin/bash
# Deployment script for Shift Scheduler
# Run this script on your server after git pull to ensure changes are applied
# Usage: ./deploy.sh [--skip-git] [--skip-permissions]

set -e  # Exit on error

echo "🚀 Starting deployment process..."
echo "Timestamp: $(date '+%Y-%m-%d %H:%M:%S')"

# Get the directory where the script is located
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd "$SCRIPT_DIR"

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Step 1: Verify we're in the right directory
echo -e "${YELLOW}Step 1: Verifying directory...${NC}"
if [ ! -f "public/index.php" ]; then
    echo -e "${RED}❌ Error: public/index.php not found. Are you in the correct directory?${NC}"
    exit 1
fi
echo -e "${GREEN}✓ Directory verified${NC}"

# Step 2: Check git status (skip if --skip-git flag is set)
if [[ ! "$*" =~ "--skip-git" ]]; then
    echo -e "${YELLOW}Step 2: Checking git status...${NC}"
    if [ -d ".git" ]; then
        # Show last commit
        echo "Last commit:"
        git log -1 --oneline 2>/dev/null || echo "No commits found"
        echo ""
        git status
        echo -e "${GREEN}✓ Git status checked${NC}"
    else
        echo -e "${YELLOW}⚠ Warning: .git directory not found. Skipping git status.${NC}"
    fi
else
    echo -e "${YELLOW}Step 2: Skipping git status (--skip-git flag)${NC}"
fi

# Step 3: Set proper permissions (skip if --skip-permissions flag is set)
if [[ ! "$*" =~ "--skip-permissions" ]]; then
    echo -e "${YELLOW}Step 3: Setting file permissions...${NC}"
    # Find web server user (common: www-data, nginx, apache)
    WEB_USER="www-data"
    if id "$WEB_USER" &>/dev/null; then
        sudo chown -R "$WEB_USER:$WEB_USER" "$SCRIPT_DIR"
        sudo find "$SCRIPT_DIR" -type d -exec chmod 755 {} \;
        sudo find "$SCRIPT_DIR" -type f -exec chmod 644 {} \;
        sudo chmod -R 775 "$SCRIPT_DIR/public/assets" 2>/dev/null || true
        echo -e "${GREEN}✓ Permissions set for $WEB_USER${NC}"
    else
        echo -e "${YELLOW}⚠ Warning: $WEB_USER user not found. Skipping permission changes.${NC}"
        echo -e "${YELLOW}   You may need to run: sudo chown -R www-data:www-data $SCRIPT_DIR${NC}"
    fi
else
    echo -e "${YELLOW}Step 3: Skipping permissions (--skip-permissions flag)${NC}"
fi

# Step 4: Clear PHP opcache by restarting PHP-FPM
echo -e "${YELLOW}Step 4: Restarting PHP-FPM to clear opcache...${NC}"
PHP_VERSION=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;" 2>/dev/null || echo "8.2")

# Try to restart PHP-FPM (common service names)
if systemctl is-active --quiet php${PHP_VERSION}-fpm 2>/dev/null; then
    sudo systemctl restart php${PHP_VERSION}-fpm
    echo -e "${GREEN}✓ PHP-FPM ${PHP_VERSION} restarted${NC}"
elif systemctl is-active --quiet php-fpm 2>/dev/null; then
    sudo systemctl restart php-fpm
    echo -e "${GREEN}✓ PHP-FPM restarted${NC}"
else
    echo -e "${YELLOW}⚠ Warning: PHP-FPM service not found. You may need to restart it manually:${NC}"
    echo -e "${YELLOW}   sudo systemctl restart php${PHP_VERSION}-fpm${NC}"
    echo -e "${YELLOW}   or: sudo systemctl restart php-fpm${NC}"
fi

# Step 5: Clear opcache using PHP script
echo -e "${YELLOW}Step 5: Clearing PHP opcache...${NC}"
php -r "if (function_exists('opcache_reset')) { opcache_reset(); echo 'Opcache cleared'; } else { echo 'Opcache not available'; }" 2>/dev/null
echo -e "${GREEN}✓ Opcache cleared${NC}"

# Step 6: Restart Nginx (if applicable)
echo -e "${YELLOW}Step 6: Restarting web server...${NC}"
if systemctl is-active --quiet nginx 2>/dev/null; then
    sudo systemctl reload nginx
    echo -e "${GREEN}✓ Nginx reloaded${NC}"
elif systemctl is-active --quiet apache2 2>/dev/null; then
    sudo systemctl reload apache2
    echo -e "${GREEN}✓ Apache reloaded${NC}"
else
    echo -e "${YELLOW}⚠ Warning: Web server not found. Skipping restart.${NC}"
fi

# Step 7: Verify file timestamps
echo -e "${YELLOW}Step 7: Verifying recent file changes...${NC}"
RECENT_FILES=$(find app public -type f -mmin -5 2>/dev/null | head -5)
if [ -n "$RECENT_FILES" ]; then
    echo -e "${GREEN}✓ Recent file changes detected:${NC}"
    echo "$RECENT_FILES" | while read file; do
        echo "   - $file (modified: $(stat -c %y "$file" 2>/dev/null || stat -f %Sm "$file" 2>/dev/null))"
    done
else
    echo -e "${YELLOW}⚠ No files modified in last 5 minutes. This is normal if you just pulled.${NC}"
fi

# Step 8: Verify .env file exists (warn if missing)
echo -e "${YELLOW}Step 8: Checking environment configuration...${NC}"
if [ -f ".env" ]; then
    echo -e "${GREEN}✓ .env file found${NC}"
else
    echo -e "${YELLOW}⚠ Warning: .env file not found.${NC}"
    echo -e "${YELLOW}   Create it from .env.example if needed for production.${NC}"
fi

# Step 9: Test database connection (if .env exists)
if [ -f ".env" ] && command -v php &> /dev/null; then
    echo -e "${YELLOW}Step 9: Testing database connection...${NC}"
    php -r "
    require_once 'app/Core/config.php';
    try {
        \$db = db();
        echo '✓ Database connection successful\n';
    } catch (Exception \$e) {
        echo '⚠ Database connection failed: ' . \$e->getMessage() . '\n';
    }
    " 2>/dev/null || echo -e "${YELLOW}⚠ Could not test database connection${NC}"
else
    echo -e "${YELLOW}Step 9: Skipping database test (no .env or PHP not found)${NC}"
fi

# Step 10: Deployment summary
echo ""
echo -e "${YELLOW}════════════════════════════════════════${NC}"
echo -e "${GREEN}✓ Deployment script completed successfully!${NC}"
echo -e "${YELLOW}════════════════════════════════════════${NC}"
echo ""
echo -e "${YELLOW}📋 Next steps:${NC}"
echo "1. Visit your website and do a hard refresh (Ctrl+F5 or Cmd+Shift+R)"
echo "2. If changes still don't appear, visit: http://your-domain/clear_cache.php"
echo "3. Check PHP error logs: sudo tail -f /var/log/php${PHP_VERSION}-fpm.log"
echo "4. Verify files were updated: git log -1 --stat"
echo ""
echo -e "${GREEN}✨ Deployment completed at $(date '+%Y-%m-%d %H:%M:%S')${NC}"

