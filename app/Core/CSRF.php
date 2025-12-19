<?php
declare(strict_types=1);

class CSRF
{
    private const TOKEN_NAME = 'csrf_token';
    
    public static function generateToken(): string
    {
        Session::start();
        $token = bin2hex(random_bytes(32));
        Session::set(self::TOKEN_NAME, $token);
        return $token;
    }
    
    public static function getToken(): ?string
    {
        return Session::get(self::TOKEN_NAME);
    }
    
    public static function validateToken(?string $token): bool
    {
        $sessionToken = Session::get(self::TOKEN_NAME);
        return $sessionToken !== null && hash_equals($sessionToken, $token ?? '');
    }
    
    public static function tokenField(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(self::getToken() ?? self::generateToken()) . '">';
    }
}

