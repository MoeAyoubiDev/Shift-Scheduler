<?php
declare(strict_types=1);

/**
 * Production Database Setup Script
 * 
 * This script sets up the database from scratch:
 * 1. Creates the base schema (database.sql)
 * 2. Runs all migrations in order
 * 3. Resets and seeds minimal reference data
 * 
 * Usage: php database/setup_production.php
 */

require_once __DIR__ . '/../app/Core/config.php';

$pdo = db();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "🚀 Starting production database setup...\n\n";

try {
    // Step 1: Create base schema
    echo "📋 Step 1: Creating base schema...\n";
    $baseSchema = file_get_contents(__DIR__ . '/database.sql');
    if ($baseSchema === false) {
        throw new Exception("Could not read database.sql file");
    }
    
    // Split by semicolons and execute each statement
    $statements = array_filter(
        array_map('trim', explode(';', $baseSchema)),
        fn($stmt) => !empty($stmt) && !preg_match('/^(--|USE|DELIMITER)/i', $stmt)
    );
    
    foreach ($statements as $statement) {
        if (!empty(trim($statement))) {
            try {
                $pdo->exec($statement);
            } catch (PDOException $e) {
                // Ignore "already exists" errors
                if (strpos($e->getMessage(), 'already exists') === false && 
                    strpos($e->getMessage(), 'Duplicate') === false) {
                    echo "   ⚠ Warning: " . $e->getMessage() . "\n";
                }
            }
        }
    }
    echo "   ✓ Base schema created\n\n";
    
    // Step 2: Run migrations
    echo "🔄 Step 2: Running migrations...\n";
    $migrations = [
        '001_add_companies_table.sql',
        '002_add_company_id_to_tables.sql',
        '003_update_stored_procedures.sql'
    ];
    
    foreach ($migrations as $migration) {
        $migrationPath = __DIR__ . '/migrations/' . $migration;
        if (!file_exists($migrationPath)) {
            echo "   ⚠ Warning: Migration file not found: $migration\n";
            continue;
        }
        
        echo "   → Running: $migration\n";
        $migrationSql = file_get_contents($migrationPath);
        
        // Handle DELIMITER statements
        $migrationSql = preg_replace('/DELIMITER \$\$.*?DELIMITER ;/s', '', $migrationSql);
        $migrationSql = str_replace('$$', ';', $migrationSql);
        
        $statements = array_filter(
            array_map('trim', explode(';', $migrationSql)),
            fn($stmt) => !empty($stmt) && !preg_match('/^(--|USE)/i', $stmt)
        );
        
        foreach ($statements as $statement) {
            if (!empty(trim($statement))) {
                try {
                    $pdo->exec($statement);
                } catch (PDOException $e) {
                    // Ignore "already exists" and "does not exist" errors for idempotency
                    if (strpos($e->getMessage(), 'already exists') === false && 
                        strpos($e->getMessage(), 'does not exist') === false &&
                        strpos($e->getMessage(), 'Duplicate') === false) {
                        echo "      ⚠ Warning: " . $e->getMessage() . "\n";
                    }
                }
            }
        }
        echo "      ✓ Completed: $migration\n";
    }
    
    echo "\n✅ Migrations completed\n\n";
    
    // Step 3: Reset and seed reference data
    echo "🌱 Step 3: Seeding reference data...\n";
    require_once __DIR__ . '/reset_database_production.php';
    
    echo "\n✅ Production database setup complete!\n";
    echo "\n📝 Database is ready for:\n";
    echo "   - Company sign-ups at /signup.php\n";
    echo "   - Multi-tenant data isolation\n";
    echo "   - All stored procedures and business logic\n\n";
    
} catch (Exception $e) {
    echo "\n❌ Setup error: " . $e->getMessage() . "\n";
    echo "   Stack trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}

