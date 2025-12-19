<?php
declare(strict_types=1);

require_once __DIR__ . '/../Helpers/helpers.php';

class AuthController
{
    public static function handleLogin(string $username, string $password): ?string
    {
        if (login($username, $password)) {
            header('Location: /index.php');
            exit;
        }

        return 'Invalid username or password.';
    }

    public static function handleLogout(): void
    {
        logout();
        header('Location: /index.php');
        exit;
    }
}
