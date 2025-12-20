<?php
/**
 * Database Connection Test
 * Run this to verify your database configuration is correct
 * Access via: http://localhost:8000/test_connection.php
 */

require_once __DIR__ . '/app/Core/config.php';

header('Content-Type: text/html; charset=utf-8');

echo "<h1>Database Connection Test</h1>";
echo "<style>body { font-family: Arial, sans-serif; padding: 20px; } .success { color: green; } .error { color: red; } .info { background: #f0f0f0; padding: 10px; margin: 10px 0; border-radius: 5px; }</style>";

// Show configuration
echo "<div class='info'>";
echo "<h2>Current Configuration</h2>";
global $DATABASE_CONFIG;
echo "<p><strong>Host:</strong> " . htmlspecialchars($DATABASE_CONFIG['host']) . "</p>";
echo "<p><strong>Port:</strong> " . htmlspecialchars($DATABASE_CONFIG['port']) . "</p>";
echo "<p><strong>Database:</strong> " . htmlspecialchars($DATABASE_CONFIG['name']) . "</p>";
echo "<p><strong>User:</strong> " . htmlspecialchars($DATABASE_CONFIG['user']) . "</p>";
echo "<p><strong>Password:</strong> " . (empty($DATABASE_CONFIG['pass']) ? '(empty)' : '***') . "</p>";
echo "</div>";

// Test connection
try {
    $pdo = db();
    echo "<p class='success'>✓ Database connection successful!</p>";
    
    // Test query
    $stmt = $pdo->query("SELECT DATABASE() as db_name, USER() as db_user");
    $result = $stmt->fetch();
    echo "<div class='info'>";
    echo "<h3>Connection Info</h3>";
    echo "<p><strong>Connected to database:</strong> " . htmlspecialchars($result['db_name']) . "</p>";
    echo "<p><strong>Connected as user:</strong> " . htmlspecialchars($result['db_user']) . "</p>";
    echo "</div>";
    
    // Check if tables exist
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<div class='info'>";
    echo "<h3>Database Tables (" . count($tables) . ")</h3>";
    if (count($tables) > 0) {
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li>" . htmlspecialchars($table) . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p class='error'>⚠ No tables found. Please import database/database.sql</p>";
    }
    echo "</div>";
    
    // Check for required tables
    $requiredTables = ['users', 'roles', 'sections', 'employees', 'shift_requests', 'schedules'];
    $missingTables = [];
    foreach ($requiredTables as $table) {
        if (!in_array($table, $tables)) {
            $missingTables[] = $table;
        }
    }
    
    if (empty($missingTables)) {
        echo "<p class='success'>✓ All required tables exist</p>";
    } else {
        echo "<p class='error'>⚠ Missing tables: " . implode(', ', $missingTables) . "</p>";
        echo "<p>Please run: <code>mysql -u root ShiftSchedulerDB < database/database.sql</code></p>";
    }
    
} catch (PDOException $e) {
    echo "<p class='error'>✗ Database connection failed!</p>";
    echo "<div class='info'>";
    echo "<h3>Error Details</h3>";
    echo "<p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Code:</strong> " . $e->getCode() . "</p>";
    echo "</div>";
    
    echo "<div class='info'>";
    echo "<h3>Troubleshooting</h3>";
    echo "<ul>";
    echo "<li>Verify MySQL is running: <code>mysql -u root -e 'SELECT 1'</code></li>";
    echo "<li>Check database exists: <code>mysql -u root -e 'SHOW DATABASES'</code></li>";
    echo "<li>Create database if needed: <code>mysql -u root -e 'CREATE DATABASE IF NOT EXISTS ShiftSchedulerDB'</code></li>";
    echo "<li>Import schema: <code>mysql -u root ShiftSchedulerDB < database/database.sql</code></li>";
    echo "</ul>";
    echo "</div>";
}

echo "<hr>";
echo "<p><a href='/index.php'>Go to Login Page</a></p>";

