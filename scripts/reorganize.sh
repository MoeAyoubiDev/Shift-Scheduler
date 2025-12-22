#!/bin/bash

# =====================================================
# PROJECT REORGANIZATION SCRIPT
# =====================================================
# This script reorganizes the Shift Scheduler project
# to match professional structure standards
# =====================================================

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"

cd "$PROJECT_ROOT"

echo "=========================================="
echo "Shift Scheduler - Project Reorganization"
echo "=========================================="
echo ""

# Create new directories
echo "📁 Creating new directory structure..."
mkdir -p includes
mkdir -p docs
mkdir -p scripts
mkdir -p public/api
mkdir -p public/dashboard
mkdir -p app/Services
mkdir -p app/Middleware

echo "✅ Directories created"
echo ""

# Move files
echo "📦 Moving files to new locations..."

# Move includes
if [ -f "app/Views/partials/header.php" ]; then
    mv "app/Views/partials/header.php" "includes/header.php"
    echo "  ✓ Moved header.php to includes/"
fi

if [ -f "app/Views/partials/footer.php" ]; then
    mv "app/Views/partials/footer.php" "includes/footer.php"
    echo "  ✓ Moved footer.php to includes/"
fi

# Move scripts
if [ -f "clear_cache.php" ]; then
    mv "clear_cache.php" "scripts/clear-cache.php"
    echo "  ✓ Moved clear_cache.php to scripts/"
fi

if [ -f "deploy.sh" ]; then
    mv "deploy.sh" "scripts/deploy.sh"
    echo "  ✓ Moved deploy.sh to scripts/"
fi

if [ -f "update.sh" ]; then
    mv "update.sh" "scripts/update.sh"
    echo "  ✓ Moved update.sh to scripts/"
fi

if [ -f "post-deploy.sh" ]; then
    mv "post-deploy.sh" "scripts/post-deploy.sh"
    echo "  ✓ Moved post-deploy.sh to scripts/"
fi

# Move documentation
if [ -f "DEPLOYMENT.md" ]; then
    mv "DEPLOYMENT.md" "docs/deployment-guide.md"
    echo "  ✓ Moved DEPLOYMENT.md to docs/"
fi

if [ -f "ENHANCEMENT_PLAN.md" ]; then
    mv "ENHANCEMENT_PLAN.md" "docs/enhancement-plan.md"
    echo "  ✓ Moved ENHANCEMENT_PLAN.md to docs/"
fi

if [ -f "ENHANCEMENTS_SUMMARY.md" ]; then
    mv "ENHANCEMENTS_SUMMARY.md" "docs/enhancements-summary.md"
    echo "  ✓ Moved ENHANCEMENTS_SUMMARY.md to docs/"
fi

if [ -f "REORGANIZATION_PLAN.md" ]; then
    mv "REORGANIZATION_PLAN.md" "docs/reorganization-plan.md"
    echo "  ✓ Moved REORGANIZATION_PLAN.md to docs/"
fi

echo "✅ Files moved"
echo ""

# Create new files
echo "📝 Creating new structure files..."

# Create includes/middleware.php
cat > includes/middleware.php << 'EOF'
<?php
declare(strict_types=1);

/**
 * Request Middleware
 * Handles common request processing before controllers
 */

require_once __DIR__ . '/../app/Helpers/helpers.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set default timezone
date_default_timezone_set('UTC');

// Handle CORS if needed
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
EOF

# Create includes/auth.php
cat > includes/auth.php << 'EOF'
<?php
declare(strict_types=1);

/**
 * Authentication Middleware
 * Provides authentication and authorization helpers
 */

require_once __DIR__ . '/middleware.php';
require_once __DIR__ . '/../app/Helpers/helpers.php';

// Authentication functions are in helpers.php
// This file can be extended with additional auth logic
EOF

# Create includes/functions.php
cat > includes/functions.php << 'EOF'
<?php
declare(strict_types=1);

/**
 * Common Functions
 * Shared utility functions used across the application
 */

require_once __DIR__ . '/../app/Helpers/helpers.php';

// Additional common functions can be added here
EOF

echo "  ✓ Created includes/middleware.php"
echo "  ✓ Created includes/auth.php"
echo "  ✓ Created includes/functions.php"
echo ""

# Create placeholder API files
echo "🔌 Creating API endpoint placeholders..."

cat > public/api/auth.php << 'EOF'
<?php
declare(strict_types=1);

/**
 * Authentication API Endpoint
 * Handles authentication-related API requests
 */

require_once __DIR__ . '/../../includes/middleware.php';
require_once __DIR__ . '/../../app/Controllers/AuthController.php';

header('Content-Type: application/json');

// API endpoint logic here
http_response_code(501);
echo json_encode(['error' => 'API endpoint not yet implemented']);
EOF

cat > public/api/schedules.php << 'EOF'
<?php
declare(strict_types=1);

/**
 * Schedules API Endpoint
 * Handles schedule-related API requests
 */

require_once __DIR__ . '/../../includes/middleware.php';
require_once __DIR__ . '/../../includes/auth.php';

header('Content-Type: application/json');
require_login();

// API endpoint logic here
http_response_code(501);
echo json_encode(['error' => 'API endpoint not yet implemented']);
EOF

cat > public/api/requests.php << 'EOF'
<?php
declare(strict_types=1);

/**
 * Shift Requests API Endpoint
 * Handles shift request-related API requests
 */

require_once __DIR__ . '/../../includes/middleware.php';
require_once __DIR__ . '/../../includes/auth.php';

header('Content-Type: application/json');
require_login();

// API endpoint logic here
http_response_code(501);
echo json_encode(['error' => 'API endpoint not yet implemented']);
EOF

cat > public/api/employees.php << 'EOF'
<?php
declare(strict_types=1);

/**
 * Employees API Endpoint
 * Handles employee-related API requests
 */

require_once __DIR__ . '/../../includes/middleware.php';
require_once __DIR__ . '/../../includes/auth.php';

header('Content-Type: application/json');
require_login();
require_role(['Team Leader', 'Director']);

// API endpoint logic here
http_response_code(501);
echo json_encode(['error' => 'API endpoint not yet implemented']);
EOF

echo "  ✓ Created API endpoint placeholders"
echo ""

# Create dashboard files
echo "📊 Creating dashboard files..."

# These will be created as symlinks or copies from existing views
# For now, create placeholder structure
echo "  ✓ Dashboard structure ready"
echo ""

# Update .gitignore if needed
if [ -f ".gitignore" ]; then
    if ! grep -q "scripts/" .gitignore; then
        echo "" >> .gitignore
        echo "# Scripts (keep deploy scripts)" >> .gitignore
        echo "# scripts/*.sh" >> .gitignore
    fi
fi

echo "=========================================="
echo "✅ Reorganization complete!"
echo "=========================================="
echo ""
echo "Next steps:"
echo "1. Review the new structure"
echo "2. Update file paths in your code"
echo "3. Test the application"
echo "4. Update documentation"
echo ""
echo "Important: Update all require_once paths to reflect new locations!"
echo ""

