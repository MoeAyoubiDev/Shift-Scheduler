<?php
declare(strict_types=1);

/**
 * Production Database Reset Script
 * 
 * This script safely resets the database by:
 * 1. Truncating all data (respecting foreign key constraints)
 * 2. Keeping the exact same schema (tables, columns, constraints, procedures)
 * 3. Seeding only minimal required reference data
 * 
 * DO NOT:
 * - Drop tables
 * - Modify schema
 * - Add demo users
 * - Insert test data
 * 
 * Usage: php database/reset_database_production.php
 */

require_once __DIR__ . '/../app/Core/config.php';

$pdo = db();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "🔄 Starting database reset...\n\n";

try {
    // Disable foreign key checks temporarily
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    // List of tables in dependency order (child tables first)
    $tables = [
        'notifications',
        'employee_breaks',
        'schedule_assignments',
        'schedule_shifts',
        'schedules',
        'shift_requests',
        'shift_requirements',
        'weeks',
        'employees',
        'user_roles',
        'users',
        'company_onboarding',
        'companies',
        'sections',
        'shift_definitions',
        'shift_types',
        'schedule_patterns',
        'system_settings',
        'roles'
    ];
    
    echo "🗑️  Step 1: Truncating all data...\n";
    foreach ($tables as $table) {
        try {
            $pdo->exec("TRUNCATE TABLE `$table`");
            echo "   ✓ Cleared: $table\n";
        } catch (PDOException $e) {
            // Table might not exist yet (first run), skip it
            if (strpos($e->getMessage(), "doesn't exist") === false) {
                echo "   ⚠ Warning: Could not truncate $table: " . $e->getMessage() . "\n";
            }
        }
    }
    
    // Re-enable foreign key checks
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    echo "\n✅ All data cleared successfully\n\n";
    
    echo "🌱 Step 2: Seeding minimal reference data...\n";
    
    // Seed roles (required for the system)
    $roles = [
        ['Director', 'Read-only access to both sections'],
        ['Team Leader', 'Full CRUD access for assigned section'],
        ['Supervisor', 'Read-only access for assigned section'],
        ['Senior', 'Shift leader for today operations'],
        ['Employee', 'Shift request and schedule access']
    ];
    
    $stmt = $pdo->prepare("INSERT INTO roles (role_name, description) VALUES (?, ?)");
    foreach ($roles as $role) {
        $stmt->execute($role);
    }
    echo "   ✓ Seeded: roles (" . count($roles) . " roles)\n";
    
    // Seed shift_types (required for shift definitions)
    $shiftTypes = [
        ['AM', 'Morning', '06:00:00', '14:00:00', 8.00],
        ['MID', 'Mid', '10:00:00', '18:00:00', 8.00],
        ['PM', 'Evening', '14:00:00', '22:00:00', 8.00],
        ['MIDNIGHT', 'Midnight', '18:00:00', '02:00:00', 8.00],
        ['OVERNIGHT', 'Overnight', '22:00:00', '06:00:00', 8.00]
    ];
    
    $stmt = $pdo->prepare("INSERT INTO shift_types (code, name, start_time, end_time, duration_hours) VALUES (?, ?, ?, ?, ?)");
    foreach ($shiftTypes as $type) {
        $stmt->execute($type);
    }
    echo "   ✓ Seeded: shift_types (" . count($shiftTypes) . " types)\n";
    
    // Seed shift_definitions (required for scheduling)
    $shiftDefs = [
        ['AM Shift', '06:00:00', '14:00:00', 8.00, 'AM', '#38bdf8', 1],
        ['Mid Shift', '10:00:00', '18:00:00', 8.00, 'MID', '#6366f1', 2],
        ['PM Shift', '14:00:00', '22:00:00', 8.00, 'PM', '#f97316', 3],
        ['Midnight Shift', '18:00:00', '02:00:00', 8.00, 'MIDNIGHT', '#0f172a', 4],
        ['Overnight Shift', '22:00:00', '06:00:00', 8.00, 'OVERNIGHT', '#334155', 5],
        ['Day Off', null, null, 0.00, 'OFF', '#94a3b8', null]
    ];
    
    $stmt = $pdo->prepare("INSERT INTO shift_definitions (shift_name, start_time, end_time, duration_hours, category, color_code, shift_type_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($shiftDefs as $def) {
        $stmt->execute($def);
    }
    echo "   ✓ Seeded: shift_definitions (" . count($shiftDefs) . " definitions)\n";
    
    // Seed schedule_patterns (required for shift requests)
    $patterns = [
        ['5x2', 5, 2, 8.00, '5 days on / 2 days off'],
        ['6x1', 6, 1, 8.00, '6 days on / 1 day off']
    ];
    
    $stmt = $pdo->prepare("INSERT INTO schedule_patterns (name, work_days_per_week, off_days_per_week, default_shift_duration_hours, description) VALUES (?, ?, ?, ?, ?)");
    foreach ($patterns as $pattern) {
        $stmt->execute($pattern);
    }
    echo "   ✓ Seeded: schedule_patterns (" . count($patterns) . " patterns)\n";
    
    echo "\n✅ Database reset complete!\n";
    echo "\n📝 Next steps:\n";
    echo "   1. Companies can now sign up at /signup.php\n";
    echo "   2. Each company will create their own sections and users during onboarding\n";
    echo "   3. No demo data is included - each company starts fresh\n\n";
    
} catch (PDOException $e) {
    echo "\n❌ Database error: " . $e->getMessage() . "\n";
    echo "   Error code: " . $e->getCode() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

