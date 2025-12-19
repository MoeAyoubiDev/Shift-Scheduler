<?php
declare(strict_types=1);

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Session.php';

class Auth
{
    public static function login(string $username, string $password): array
    {
        $db = Database::getInstance();
        
        // Note: In production, password verification should be done in PHP
        // For stored procedure compatibility, we'll verify in PHP after getting hash
        $stmt = $db->prepare("SELECT id, username, password_hash, email, is_active FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if (!$user || !$user['is_active']) {
            return ['success' => false, 'message' => 'Invalid credentials'];
        }
        
        if (!password_verify($password, $user['password_hash'])) {
            return ['success' => false, 'message' => 'Invalid credentials'];
        }
        
        // Get user roles and sections
        $roles = Database::callProcedure('sp_get_user_roles', [$user['id']]);
        
        if (empty($roles)) {
            return ['success' => false, 'message' => 'No roles assigned'];
        }
        
        // Store user data in session
        Session::set('user_id', $user['id']);
        Session::set('username', $user['username']);
        Session::set('email', $user['email']);
        Session::set('roles', $roles);
        
        // Determine primary role (first one, or Director if exists)
        $primaryRole = null;
        foreach ($roles as $role) {
            if ($role['role_name'] === 'Director') {
                $primaryRole = $role;
                break;
            }
        }
        if (!$primaryRole) {
            $primaryRole = $roles[0];
        }
        
        Session::set('current_role', $primaryRole['role_name']);
        Session::set('current_section_id', $primaryRole['section_id']);
        Session::set('current_section_name', $primaryRole['section_name']);
        Session::set('employee_id', $primaryRole['employee_id']);
        
        Session::regenerate();
        
        return [
            'success' => true,
            'role' => $primaryRole['role_name'],
            'needs_section_choice' => $primaryRole['role_name'] === 'Director'
        ];
    }
    
    public static function logout(): void
    {
        Session::destroy();
    }
    
    public static function user(): ?array
    {
        if (!Session::has('user_id')) {
            return null;
        }
        
        return [
            'id' => Session::get('user_id'),
            'username' => Session::get('username'),
            'email' => Session::get('email'),
            'role' => Session::get('current_role'),
            'section_id' => Session::get('current_section_id'),
            'section_name' => Session::get('current_section_name'),
            'employee_id' => Session::get('employee_id'),
        ];
    }
    
    public static function check(): bool
    {
        return Session::has('user_id');
    }
    
    public static function requireAuth(): void
    {
        if (!self::check()) {
            header('Location: /login.php');
            exit;
        }
    }
    
    public static function requireRole(string $role): void
    {
        self::requireAuth();
        $user = self::user();
        if ($user['role'] !== $role) {
            http_response_code(403);
            die('Access denied');
        }
    }
    
    public static function requireAnyRole(array $roles): void
    {
        self::requireAuth();
        $user = self::user();
        if (!in_array($user['role'], $roles)) {
            http_response_code(403);
            die('Access denied');
        }
    }
}

