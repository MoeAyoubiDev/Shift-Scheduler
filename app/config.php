<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('UTC');

$DATABASE_CONFIG = [
    'driver' => getenv('DB_DRIVER') ?: 'mysql',
    'host' => getenv('DB_HOST') ?: '127.0.0.1',
    'port' => getenv('DB_PORT') ?: '3306',
    'name' => getenv('DB_NAME') ?: 'ShiftSchedulerDB',
    'user' => getenv('DB_USER') ?: 'root',
    'pass' => getenv('DB_PASSWORD') ?: '',
];

/**
 * Returns a shared PDO instance configured for MySQL or SQL Server.
 */
function db(): PDO
{
    static $pdo = null;
    global $DATABASE_CONFIG;

    if ($pdo === null) {
        $driver = strtolower($DATABASE_CONFIG['driver']);

        if ($driver === 'sqlsrv') {
            $dsn = sprintf(
                'sqlsrv:Server=%s,%s;Database=%s',
                $DATABASE_CONFIG['host'],
                $DATABASE_CONFIG['port'],
                $DATABASE_CONFIG['name']
            );
        } else {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                $DATABASE_CONFIG['host'],
                $DATABASE_CONFIG['port'],
                $DATABASE_CONFIG['name']
            );
        }

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

function week_end_from_start(string $weekStart): string
{
    $start = new DateTimeImmutable($weekStart);
    return $start->modify('+6 days')->format('Y-m-d');
}
