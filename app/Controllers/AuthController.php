<?php
declare(strict_types=1);

require_once __DIR__ . '/../Helpers/helpers.php';

class AuthController
{
    public static function handleLogin(string $username, string $password, array $payload): ?string
    {
        require_csrf($payload);

        if (login($username, $password)) {
            header('Location: /index.php');
            exit;
        }

        return 'Invalid username or password.';
    }

    public static function handleLogout(array $payload): void
    {
        require_csrf($payload);
        logout();
        header('Location: /index.php');
        exit;
    }
}
