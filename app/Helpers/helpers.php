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

function current_section_id(): ?int
{
    $user = current_user();
    if (!$user) {
        return null;
    }

    if ($user['role'] === 'Director') {
        return $_SESSION['selected_section_id'] ?? null;
    }

    return $user['section_id'] ?? null;
}

function set_current_section(int $sectionId): void
{
    if ($sectionId <= 0) {
        unset($_SESSION['selected_section_id']);
        return;
    }

    $_SESSION['selected_section_id'] = $sectionId;
}

function login(string $username, string $password): bool
{
    $user = User::authenticate($username, $password);

    if (!$user) {
        return false;
    }

    $_SESSION['user'] = $user;
    return true;
}

function logout(): void
{
    unset($_SESSION['user'], $_SESSION['selected_section_id']);
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
        render_view('partials/header', [
            'title' => 'Access Denied',
            'message' => 'You do not have permission to access this page.',
        ]);
        render_view('partials/footer');
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
    return $token !== null && isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function require_csrf(array $payload): void
{
    if (!verify_csrf($payload['csrf_token'] ?? null)) {
        http_response_code(419);
        render_view('partials/header', [
            'title' => 'Invalid Session',
            'message' => 'Your session expired or the form is invalid. Please try again.',
        ]);
        render_view('partials/footer');
        exit;
    }
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
