<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$configDirectory = __DIR__ . '/../../config';
$CONFIG = [];

if (is_dir($configDirectory)) {
    foreach (glob($configDirectory . '/*.php') ?: [] as $configFile) {
        $key = basename($configFile, '.php');
        $CONFIG[$key] = require $configFile;
    }
}

$APP_CONFIG = $CONFIG['app'] ?? [];

date_default_timezone_set($APP_CONFIG['timezone'] ?? 'UTC');

function config(string $group, ?string $key = null, mixed $default = null): mixed
{
    global $CONFIG;

    if (!array_key_exists($group, $CONFIG)) {
        return $default;
    }

    if ($key === null) {
        return $CONFIG[$group];
    }

    return $CONFIG[$group][$key] ?? $default;
}

function app_config(string $key, mixed $default = null): mixed
{
    return config('app', $key, $default);
}

// Load .env file if it exists
$envFile = __DIR__ . '/../../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue; // Skip comments
        }
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            if (!isset($_ENV[$key])) {
                $_ENV[$key] = $value;
                putenv("$key=$value");
            }
        }
    }
}

$databaseConfig = config('database') ?? [];
$DATABASE_CONFIG = [
    'host' => getenv('DB_HOST') ?: ($databaseConfig['host'] ?? 'localhost'),
    'port' => getenv('DB_PORT') ?: ($databaseConfig['port'] ?? '3306'),
    'name' => getenv('DB_NAME') ?: ($databaseConfig['name'] ?? 'ShiftSchedulerDB'),
    'user' => getenv('DB_USER') ?: ($databaseConfig['user'] ?? 'shift_user'),
    'pass' => getenv('DB_PASSWORD') ?: ($databaseConfig['pass'] ?? 'StrongPassword123!'),
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
