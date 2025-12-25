<?php
declare(strict_types=1);

require_once __DIR__ . '/../Helpers/helpers.php';
require_once __DIR__ . '/../Models/User.php';

class DirectorController
{
    public static function handleSelectSection(array $payload): void
    {
        require_login();
        require_role(['Admin']);
        require_csrf($payload);

        // Deprecated - sections no longer exist, redirect to dashboard
        header('Location: /index.php');
        exit;
    }

    public static function handleCreateLeader(array $payload): ?string
    {
        require_login();
        require_role(['Admin']);
        require_csrf($payload);

        $user = current_user();
        $companyId = $user['company_id'] ?? null;
        if (!$companyId) {
            return 'Company ID not found.';
        }

        $roleId = (int) ($payload['role_id'] ?? 0);
        $password = (string) ($payload['password'] ?? '');

        if ($roleId <= 0) {
            return 'Invalid role.';
        }

        if (mb_strlen($password) < 8) {
            return 'Password must be at least 8 characters long.';
        }

        // Verify role is Team Leader
        require_once __DIR__ . '/../Models/Role.php';
        $roles = Role::listRoles();
        $selectedRole = null;
        foreach ($roles as $role) {
            if ($role['id'] == $roleId) {
                $selectedRole = $role;
                break;
            }
        }

        if (!$selectedRole || !in_array($selectedRole['role_name'], ['Team Leader'], true)) {
            return 'Invalid role. Only Team Leader can be created.';
        }

        try {
            $userId = User::createLeader([
                'company_id' => $companyId,
                'username' => trim($payload['username'] ?? ''),
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'email' => trim($payload['email'] ?? ''),
                'role_id' => $roleId,
                'full_name' => trim($payload['full_name'] ?? ''),
            ]);

            return $userId > 0 ? ucfirst($selectedRole['role_name']) . ' created successfully.' : 'Unable to create leader.';
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }
}
