<?php
declare(strict_types=1);

require_once __DIR__ . '/../Core/config.php';
require_once __DIR__ . '/view.php';
require_once __DIR__ . '/../Models/User.php';

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function current_role(): ?string
{
    $user = current_user();
    return $user['role'] ?? null;
}

function current_company_id(): ?int
{
    $user = current_user();
    if (!$user) {
        return null;
    }

    return $user['company_id'] ?? null;
}

function logout(): void
{
    unset($_SESSION['user']);
}

function require_login(): void
{
    if (!current_user()) {
        header('Location: /index.php');
        exit;
    }
}

function require_role(array $roles): void
{
    $userRole = current_role();
    if (!$userRole || !in_array($userRole, $roles, true)) {
        http_response_code(403);
        $title = 'Access Denied';
        $message = 'You do not have permission to access this page.';
        require_once __DIR__ . '/../../includes/header.php';
        echo '<div class="card"><h2>Access Denied</h2><p>' . e($message) . '</p></div>';
        require_once __DIR__ . '/../../includes/footer.php';
        exit;
    }
}

function csrf_token(): string
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verify_csrf(?string $token): bool
{
    if (is_csrf_exempt()) {
        return true;
    }

    return $token !== null && isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function require_csrf(array $payload): void
{
    if (!verify_csrf($payload['csrf_token'] ?? null)) {
        http_response_code(419);
        $title = 'Invalid Session';
        $message = 'Your session expired or the form is invalid. Please try again.';
        require_once __DIR__ . '/../../includes/header.php';
        echo '<div class="card"><h2>Invalid Session</h2><p>' . e($message) . '</p></div>';
        require_once __DIR__ . '/../../includes/footer.php';
        exit;
    }
}

function is_csrf_exempt(): bool
{
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';

    return str_starts_with($path, '/api/');
}

function submission_window_open(DateTimeImmutable $date): bool
{
    $dayOfWeek = (int) $date->format('N');

    return $dayOfWeek >= 1 && $dayOfWeek <= 6;
}

function format_date(?string $date): string
{
    if (!$date) {
        return 'N/A';
    }

    return (new DateTimeImmutable($date))->format('M d, Y');
}

function json_response(bool $success, string $message, array $extra = []): never
{
    header('Content-Type: application/json');
    echo json_encode(array_merge([
        'success' => $success,
        'message' => $message,
    ], $extra));
    exit;
}
