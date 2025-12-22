<?php
/**
 * Safe Migration Script for Adding company_id Columns
 * This script checks if columns exist before adding them
 * Run: php database/migrations/fix_migration_002.php
 */

require_once __DIR__ . '/../../app/Core/config.php';

$pdo = db();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "🔧 Starting safe migration for company_id columns...\n\n";

$tables = [
    'sections' => [
        'add_column' => "ALTER TABLE sections ADD COLUMN company_id INT NULL AFTER id",
        'add_fk' => "ALTER TABLE sections ADD FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE",
        'add_index' => "ALTER TABLE sections ADD INDEX idx_company (company_id)",
        'drop_old_index' => "ALTER TABLE sections DROP INDEX IF EXISTS section_name",
        'add_unique' => "ALTER TABLE sections ADD UNIQUE KEY unique_section_company (section_name, company_id)",
    ],
    'users' => [
        'add_column' => "ALTER TABLE users ADD COLUMN company_id INT NULL AFTER id",
        'add_fk' => "ALTER TABLE users ADD FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE",
        'add_index' => "ALTER TABLE users ADD INDEX idx_company (company_id)",
        'drop_old_index' => "ALTER TABLE users DROP INDEX IF EXISTS username",
        'add_unique' => "ALTER TABLE users ADD UNIQUE KEY unique_username_company (username, company_id)",
    ],
    'weeks' => [
        'add_column' => "ALTER TABLE weeks ADD COLUMN company_id INT NULL AFTER id",
        'add_fk' => "ALTER TABLE weeks ADD FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE",
        'add_index' => "ALTER TABLE weeks ADD INDEX idx_company (company_id)",
        'drop_old_index' => "ALTER TABLE weeks DROP INDEX IF EXISTS unique_week_start",
        'add_unique' => "ALTER TABLE weeks ADD UNIQUE KEY unique_week_company (week_start_date, company_id)",
    ],
    'shift_requirements' => [
        'add_column' => "ALTER TABLE shift_requirements ADD COLUMN company_id INT NULL AFTER id",
        'add_fk' => "ALTER TABLE shift_requirements ADD FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE",
        'add_index' => "ALTER TABLE shift_requirements ADD INDEX idx_company (company_id)",
    ],
    'schedules' => [
        'add_column' => "ALTER TABLE schedules ADD COLUMN company_id INT NULL AFTER id",
        'add_fk' => "ALTER TABLE schedules ADD FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE",
        'add_index' => "ALTER TABLE schedules ADD INDEX idx_company (company_id)",
        'drop_old_index' => "ALTER TABLE schedules DROP INDEX IF EXISTS unique_schedule_week_section",
        'add_unique' => "ALTER TABLE schedules ADD UNIQUE KEY unique_schedule_company_week_section (week_id, section_id, company_id)",
    ],
    'notifications' => [
        'add_column' => "ALTER TABLE notifications ADD COLUMN company_id INT NULL AFTER id",
        'add_fk' => "ALTER TABLE notifications ADD FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE",
        'add_index' => "ALTER TABLE notifications ADD INDEX idx_company (company_id)",
    ],
];

function columnExists($pdo, $table, $column) {
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as cnt
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = ?
            AND COLUMN_NAME = ?
        ");
        $stmt->execute([$table, $column]);
        $result = $stmt->fetch();
        return (int)$result['cnt'] > 0;
    } catch (PDOException $e) {
        return false;
    }
}

function indexExists($pdo, $table, $index) {
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as cnt
            FROM INFORMATION_SCHEMA.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = ?
            AND INDEX_NAME = ?
        ");
        $stmt->execute([$table, $index]);
        $result = $stmt->fetch();
        return (int)$result['cnt'] > 0;
    } catch (PDOException $e) {
        return false;
    }
}

foreach ($tables as $table => $operations) {
    echo "📋 Processing table: {$table}\n";
    
    // Check if column exists
    if (columnExists($pdo, $table, 'company_id')) {
        echo "   ✓ company_id column already exists\n";
    } else {
        echo "   ➕ Adding company_id column...\n";
        try {
            $pdo->exec($operations['add_column']);
            echo "   ✓ Column added\n";
        } catch (PDOException $e) {
            echo "   ⚠️  Error adding column: " . $e->getMessage() . "\n";
        }
    }
    
    // Add foreign key if column exists
    if (columnExists($pdo, $table, 'company_id')) {
        // Check if FK exists
        try {
            $stmt = $pdo->query("
                SELECT CONSTRAINT_NAME
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = '{$table}'
                AND COLUMN_NAME = 'company_id'
                AND REFERENCED_TABLE_NAME = 'companies'
            ");
            if ($stmt->rowCount() == 0) {
                echo "   ➕ Adding foreign key...\n";
                $pdo->exec($operations['add_fk']);
                echo "   ✓ Foreign key added\n";
            } else {
                echo "   ✓ Foreign key already exists\n";
            }
        } catch (PDOException $e) {
            echo "   ⚠️  Error with foreign key: " . $e->getMessage() . "\n";
        }
        
        // Add index
        if (!indexExists($pdo, $table, 'idx_company')) {
            echo "   ➕ Adding index...\n";
            try {
                $pdo->exec($operations['add_index']);
                echo "   ✓ Index added\n";
            } catch (PDOException $e) {
                echo "   ⚠️  Error adding index: " . $e->getMessage() . "\n";
            }
        } else {
            echo "   ✓ Index already exists\n";
        }
    }
    
    // Handle unique constraints
    if (isset($operations['drop_old_index'])) {
        $oldIndexName = '';
        if ($table === 'sections') $oldIndexName = 'section_name';
        elseif ($table === 'users') $oldIndexName = 'username';
        elseif ($table === 'weeks') $oldIndexName = 'unique_week_start';
        elseif ($table === 'schedules') $oldIndexName = 'unique_schedule_week_section';
        
        if ($oldIndexName && indexExists($pdo, $table, $oldIndexName)) {
            echo "   🗑️  Dropping old index: {$oldIndexName}\n";
            try {
                $pdo->exec("ALTER TABLE {$table} DROP INDEX {$oldIndexName}");
                echo "   ✓ Old index dropped\n";
            } catch (PDOException $e) {
                echo "   ⚠️  Error dropping index: " . $e->getMessage() . "\n";
            }
        }
    }
    
    if (isset($operations['add_unique'])) {
        $newIndexName = '';
        if ($table === 'sections') $newIndexName = 'unique_section_company';
        elseif ($table === 'users') $newIndexName = 'unique_username_company';
        elseif ($table === 'weeks') $newIndexName = 'unique_week_company';
        elseif ($table === 'schedules') $newIndexName = 'unique_schedule_company_week_section';
        
        if ($newIndexName && !indexExists($pdo, $table, $newIndexName)) {
            echo "   ➕ Adding unique constraint...\n";
            try {
                $pdo->exec($operations['add_unique']);
                echo "   ✓ Unique constraint added\n";
            } catch (PDOException $e) {
                echo "   ⚠️  Error adding unique constraint: " . $e->getMessage() . "\n";
            }
        } else {
            echo "   ✓ Unique constraint already exists\n";
        }
    }
    
    echo "\n";
}

echo "✅ Migration completed!\n";

