<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('UTC');

$DATABASE_CONFIG = [
    'host' => getenv('DB_HOST') ?: '127.0.0.1',
    'port' => getenv('DB_PORT') ?: '3306',
    'name' => getenv('DB_NAME') ?: 'shift_scheduler',
    'user' => getenv('DB_USER') ?: 'root',
    'pass' => getenv('DB_PASSWORD') ?: '',
];

/**
 * Returns a shared PDO instance configured for MySQL.
 */
function db(): PDO
{
    static $pdo = null;
    global $DATABASE_CONFIG;

    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            $DATABASE_CONFIG['host'],
            $DATABASE_CONFIG['port'],
            $DATABASE_CONFIG['name']
        );

        $pdo = new PDO($dsn, $DATABASE_CONFIG['user'], $DATABASE_CONFIG['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }

    return $pdo;
}

/**
 * Determine the current ISO week start (Monday).
 */
function current_week_start(): string
{
    $monday = new DateTimeImmutable('monday this week');
    return $monday->format('Y-m-d');
}
