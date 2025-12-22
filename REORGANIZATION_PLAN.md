# Project Reorganization Plan

## Current Structure Analysis

**Current:**
- `public/index.php` - Single entry point with all routing
- `app/Views/` - All view files organized by role
- `app/Views/partials/` - Header and footer
- `app/` - Controllers, Models, Helpers, Core
- `config/` - Configuration files
- `database/` - SQL files

## Target Structure (Professional Organization)

```
Shift-Scheduler/
├── app/                          # Backend application logic
│   ├── Controllers/              # Business logic controllers
│   ├── Models/                   # Data models
│   ├── Services/                 # Business services (NEW)
│   ├── Middleware/               # Request middleware (NEW)
│   └── Core/                     # Core application files
│
├── public/                       # Public-facing files (web root)
│   ├── index.php                 # Main entry point
│   ├── login.php                 # Login page
│   ├── logout.php                # Logout handler
│   ├── api/                      # API endpoints (NEW)
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
│   ├── assets/                   # Static assets
│   │   ├── css/
│   │   ├── js/
│   │   └── img/
│
├── includes/                     # Shared includes (NEW)
│   ├── header.php
│   ├── footer.php
│   ├── middleware.php
│   ├── auth.php                 # Authentication helpers
│   └── functions.php            # Common functions
│
├── config/                       # Configuration files
│   ├── app.php
│   ├── database.php
│   └── schedule.php
│
├── database/                     # Database files
│   ├── database.sql
│   ├── reset_database.sql
│   └── ...
│
├── docs/                         # Documentation (NEW)
│   ├── api-documentation.md
│   ├── deployment-guide.md
│   ├── user-manual.md
│   └── development-guide.md
│
├── scripts/                      # Utility scripts (NEW)
│   ├── deploy.sh
│   ├── update.sh
│   └── clear-cache.php
│
└── README.md
```

## Reorganization Steps

### Phase 1: Create New Directories
1. Create `includes/` directory
2. Create `docs/` directory
3. Create `scripts/` directory
4. Create `public/api/` directory
5. Create `public/dashboard/` directory
6. Create `app/Services/` directory
7. Create `app/Middleware/` directory

### Phase 2: Move Files
1. Move `app/Views/partials/header.php` → `includes/header.php`
2. Move `app/Views/partials/footer.php` → `includes/footer.php`
3. Move `clear_cache.php` → `scripts/clear-cache.php`
4. Move `deploy.sh` → `scripts/deploy.sh`
5. Move `update.sh` → `scripts/update.sh`
6. Move `post-deploy.sh` → `scripts/post-deploy.sh`
7. Move documentation files → `docs/`

### Phase 3: Create New Files
1. Create `includes/middleware.php` for request middleware
2. Create `includes/auth.php` for authentication checks
3. Create `includes/functions.php` for common functions
4. Create `public/login.php` (extract from index.php)
5. Create `public/logout.php` (extract from index.php)
6. Create role-specific dashboard files in `public/dashboard/`
7. Create API endpoints in `public/api/`

### Phase 4: Update References
1. Update all `require_once` paths
2. Update `render_view()` calls
3. Update asset paths
4. Update documentation

## Benefits

1. **Clear Separation**: Public files vs. application logic
2. **Better Security**: Only public files exposed to web
3. **Easier Maintenance**: Organized by purpose
4. **Professional Structure**: Industry-standard layout
5. **Scalability**: Easy to add new features

