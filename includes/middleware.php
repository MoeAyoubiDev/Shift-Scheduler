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
