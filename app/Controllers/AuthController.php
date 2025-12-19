<?php
declare(strict_types=1);

require_once __DIR__ . '/../Core/Auth.php';
require_once __DIR__ . '/../Core/CSRF.php';

class AuthController
{
    public static function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            require __DIR__ . '/../Views/auth/login.php';
            return;
        }
        
        if (!CSRF::validateToken($_POST['csrf_token'] ?? null)) {
            $_SESSION['error'] = 'Invalid security token';
            header('Location: /login.php');
            exit;
        }
        
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            $_SESSION['error'] = 'Username and password are required';
            header('Location: /login.php');
            exit;
        }
        
        $result = Auth::login($username, $password);
        
        if ($result['success']) {
            if ($result['needs_section_choice']) {
                header('Location: /choose-section.php');
            } else {
                // Redirect based on role
                $role = $result['role'];
                $redirect = match($role) {
                    'Team Leader', 'Supervisor' => '/dashboard.php',
                    'Senior' => '/today-shift.php',
                    'Employee' => '/my-schedule.php',
                    default => '/dashboard.php'
                };
                header("Location: {$redirect}");
            }
            exit;
        } else {
            $_SESSION['error'] = $result['message'];
            header('Location: /login.php');
            exit;
        }
    }
    
    public static function logout(): void
    {
        Auth::logout();
        header('Location: /login.php');
        exit;
    }
    
    public static function chooseSection(): void
    {
        Auth::requireRole('Director');
        
        $user = Auth::user();
        require_once __DIR__ . '/../Core/Session.php';
        $roles = Session::get('roles', []);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!CSRF::validateToken($_POST['csrf_token'] ?? null)) {
                $_SESSION['error'] = 'Invalid security token';
                header('Location: /choose-section.php');
                exit;
            }
            
            $sectionId = (int) ($_POST['section_id'] ?? 0);
            
            // Find the role for this section
            foreach ($roles as $role) {
                if ($role['section_id'] == $sectionId) {
                    Session::set('current_role', $role['role_name']);
                    Session::set('current_section_id', $role['section_id']);
                    Session::set('current_section_name', $role['section_name']);
                    Session::set('employee_id', $role['employee_id']);
                    
                    header('Location: /dashboard.php');
                    exit;
                }
            }
            
            $_SESSION['error'] = 'Invalid section selected';
        }
        
        require __DIR__ . '/../Views/director/choose-section.php';
    }
}
