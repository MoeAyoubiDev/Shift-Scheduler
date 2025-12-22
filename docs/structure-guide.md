# Shift Scheduler - Project Structure Guide

## Overview

This document describes the professional project structure for the Shift Scheduler application.

## Directory Structure

```
Shift-Scheduler/
├── app/                          # Backend application logic
│   ├── Controllers/              # Business logic controllers
│   │   ├── AuthController.php
│   │   ├── DirectorController.php
│   │   ├── TeamLeaderController.php
│   │   ├── SupervisorController.php
│   │   ├── SeniorController.php
│   │   └── EmployeeController.php
│   ├── Models/                   # Data models
│   │   ├── User.php
│   │   ├── Employee.php
│   │   ├── Schedule.php
│   │   └── ...
│   ├── Services/                 # Business services (optional)
│   ├── Middleware/               # Request middleware (optional)
│   ├── Core/                     # Core application files
│   │   └── config.php
│   ├── Helpers/                  # Helper functions
│   │   ├── helpers.php
│   │   ├── view.php
│   │   └── schedule.php
│   └── Views/                    # View templates (legacy, being migrated)
│       ├── auth/
│       ├── director/
│       ├── teamleader/
│       └── ...
│
├── public/                       # Public-facing files (web root)
│   ├── index.php                 # Main entry point
│   ├── login.php                 # Login page (to be created)
│   ├── logout.php                # Logout handler (to be created)
│   ├── api/                      # API endpoints
│   │   ├── auth.php
│   │   ├── schedules.php
│   │   ├── requests.php
│   │   └── employees.php
│   ├── dashboard/                # Dashboard pages by role
│   │   ├── director.php
│   │   ├── team-leader.php
│   │   ├── supervisor.php
│   │   ├── senior.php
│   │   └── employee.php
│   └── assets/                   # Static assets
│       ├── css/
│       │   └── app.css
│       ├── js/
│       │   ├── app.js
│       │   ├── dashboard.js
│       │   └── enhanced.js
│       └── img/
│
├── includes/                     # Shared includes
│   ├── header.php                # Page header
│   ├── footer.php                # Page footer
│   ├── middleware.php            # Request middleware
│   ├── auth.php                  # Authentication helpers
│   └── functions.php             # Common functions
│
├── config/                       # Configuration files
│   ├── app.php
│   ├── database.php
│   └── schedule.php
│
├── database/                     # Database files
│   ├── database.sql
│   ├── reset_database.sql
│   ├── clean_test_data.sql
│   └── RESET_INSTRUCTIONS.md
│
├── docs/                         # Documentation
│   ├── api-documentation.md
│   ├── deployment-guide.md
│   ├── user-manual.md
│   ├── development-guide.md
│   └── structure-guide.md
│
├── scripts/                      # Utility scripts
│   ├── deploy.sh
│   ├── update.sh
│   ├── post-deploy.sh
│   ├── clear-cache.php
│   └── reorganize.sh
│
└── README.md
```

## File Organization Principles

### 1. Separation of Concerns

- **`app/`**: Contains all backend logic (Controllers, Models, Services)
- **`public/`**: Contains only files that should be publicly accessible
- **`includes/`**: Shared components used across multiple files
- **`config/`**: Configuration files (not in public directory)
- **`database/`**: Database schemas and scripts

### 2. Security

- Only files in `public/` are accessible via web server
- Sensitive files (config, app logic) are outside public directory
- Web server should be configured to serve only `public/` as document root

### 3. Maintainability

- Clear directory structure makes it easy to find files
- Related files are grouped together
- Documentation is centralized in `docs/`

### 4. Scalability

- Easy to add new features
- API endpoints can be added to `public/api/`
- New services can be added to `app/Services/`

## Path References

### From Public Files

```php
// Accessing includes
require_once __DIR__ . '/../includes/header.php';

// Accessing app logic
require_once __DIR__ . '/../app/Controllers/AuthController.php';

// Accessing config
require_once __DIR__ . '/../config/database.php';
```

### From App Files

```php
// Accessing config
require_once __DIR__ . '/../config/database.php';

// Accessing includes
require_once __DIR__ . '/../../includes/header.php';
```

### From Includes

```php
// Accessing app logic
require_once __DIR__ . '/../app/Helpers/helpers.php';

// Accessing config
require_once __DIR__ . '/../config/database.php';
```

## Migration Path

### Phase 1: Structure Creation ✅
- Create new directories
- Move files to new locations
- Create placeholder files

### Phase 2: Path Updates (In Progress)
- Update all `require_once` paths
- Update `render_view()` calls
- Update asset references

### Phase 3: File Extraction
- Extract login logic to `public/login.php`
- Extract logout logic to `public/logout.php`
- Create role-specific dashboard files

### Phase 4: API Development
- Implement API endpoints
- Add API documentation
- Add API authentication

### Phase 5: Cleanup
- Remove old file locations
- Update documentation
- Update deployment scripts

## Web Server Configuration

### Nginx Example

```nginx
server {
    listen 80;
    server_name shiftscheduler.example.com;
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

    # Deny access to sensitive directories
    location ~ ^/(app|config|database|docs|includes|scripts) {
        deny all;
    }
}
```

### Apache Example

```apache
<VirtualHost *:80>
    ServerName shiftscheduler.example.com
    DocumentRoot /var/www/shift-scheduler/public

    <Directory /var/www/shift-scheduler/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    # Deny access to sensitive directories
    <DirectoryMatch "^/var/www/shift-scheduler/(app|config|database|docs|includes|scripts)">
        Require all denied
    </DirectoryMatch>
</VirtualHost>
```

## Best Practices

1. **Never expose sensitive files**: Keep app logic, config, and includes outside public directory
2. **Use relative paths**: Use `__DIR__` for reliable path resolution
3. **Organize by feature**: Group related files together
4. **Document changes**: Update this guide when structure changes
5. **Version control**: Keep structure changes in git history

## Notes

- The `app/Views/` directory is kept for backward compatibility during migration
- Eventually, views will be moved to `public/` or `includes/` as appropriate
- API endpoints are optional but recommended for future mobile app support

