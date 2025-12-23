<?php
declare(strict_types=1);

require_once __DIR__ . '/../Helpers/helpers.php';

class AuthController
{
    public static function handleLogin(string $username, string $password, array $payload): ?string
    {
        try {
            require_csrf($payload);
        } catch (Exception $e) {
            error_log("CSRF error in login: " . $e->getMessage());
            http_response_code(400);
            return 'Error 400: Invalid request. Please try again.';
        }

        try {
            if (login($username, $password)) {
                header('Location: /index.php');
                exit;
            }

            // Authentication failed - return generic message
            return 'Invalid username or password.';
        } catch (PDOException $e) {
            // Database errors - log but don't expose details
            error_log("Database error during login: " . $e->getMessage());
            http_response_code(400);
            return 'Error 400: Unable to process login. Please try again.';
        } catch (Exception $e) {
            // Any other errors
            error_log("Unexpected error during login: " . $e->getMessage());
            http_response_code(400);
            return 'Error 400: An error occurred. Please try again.';
        }
    }

    public static function handleLogout(array $payload): void
    {
        require_csrf($payload);
        logout();
        header('Location: /index.php');
        exit;
    }
}
