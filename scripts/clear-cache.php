<?php
/**
 * Cache Clearing Script
 * Run this file to clear PHP opcache and verify changes are reflected
 * Access via: http://your-domain/clear_cache.php
 */

header('Content-Type: text/html; charset=utf-8');

echo "<h1>Cache Clearing Utility</h1>";

// Clear opcache if available
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "<p style='color: green;'>✓ PHP Opcache cleared successfully</p>";
} else {
    echo "<p style='color: orange;'>⚠ Opcache not available or not enabled</p>";
}

// Clear realpath cache
clearstatcache(true);

echo "<p style='color: green;'>✓ File stat cache cleared</p>";

// Show current login file info
$loginFile = __DIR__ . '/app/Views/auth/login.php';
if (file_exists($loginFile)) {
    $fileTime = filemtime($loginFile);
    $fileSize = filesize($loginFile);
    $fileContent = file_get_contents($loginFile);
    
    echo "<h2>Login File Status</h2>";
    echo "<p><strong>File:</strong> " . htmlspecialchars($loginFile) . "</p>";
    echo "<p><strong>Last Modified:</strong> " . date('Y-m-d H:i:s', $fileTime) . "</p>";
    echo "<p><strong>File Size:</strong> " . number_format($fileSize) . " bytes</p>";
    
    // Check if credentials section exists
    if (strpos($fileContent, 'demo-credentials') !== false || 
        strpos($fileContent, 'Test Credentials') !== false ||
        strpos($fileContent, 'fillCredentials') !== false) {
        echo "<p style='color: red;'>⚠ WARNING: Credentials section still found in file!</p>";
    } else {
        echo "<p style='color: green;'>✓ No credentials section found - File is clean</p>";
    }
    
    // Show first 500 characters
    echo "<h3>File Preview (first 500 chars):</h3>";
    echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd;'>";
    echo htmlspecialchars(substr($fileContent, 0, 500));
    echo "</pre>";
} else {
    echo "<p style='color: red;'>✗ Login file not found!</p>";
}

echo "<hr>";
echo "<p><a href='/index.php'>Go to Login Page</a></p>";
echo "<p><small>After clearing cache, do a hard refresh in your browser (Ctrl+F5 or Cmd+Shift+R)</small></p>";

