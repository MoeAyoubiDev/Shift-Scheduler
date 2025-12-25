<?php
declare(strict_types=1);

require_once __DIR__ . '/../Helpers/helpers.php';
require_once __DIR__ . '/../Models/User.php';

class DirectorController
{
    public static function handleSelectSection(array $payload): void
    {
        require_login();
        require_role(['Director']);
        require_csrf($payload);

        $sectionId = (int) ($payload['section_id'] ?? 0);
        if ($sectionId > 0) {
            set_current_section($sectionId);
        }

        header('Location: /index.php');
        exit;
    }

    public static function handleCreateLeader(array $payload): ?string
    {
        require_login();
        require_role(['Director']);
        require_csrf($payload);

        $roleId = (int) ($payload['role_id'] ?? 0);
        $sectionId = (int) ($payload['section_id'] ?? 0);
        $password = (string) ($payload['password'] ?? '');

        if ($roleId <= 0 || $sectionId <= 0) {
            return 'Invalid role or section.';
        }

        if (mb_strlen($password) < 8) {
            return 'Password must be at least 8 characters long.';
        }

        // Verify role is Team Leader or Supervisor
        require_once __DIR__ . '/../Models/Role.php';
        $roles = Role::listRoles();
        $selectedRole = null;
        foreach ($roles as $role) {
            if ($role['id'] == $roleId) {
                $selectedRole = $role;
                break;
            }
        }

        if (!$selectedRole || !in_array($selectedRole['role_name'], ['Team Leader', 'Supervisor'], true)) {
            return 'Invalid role. Only Team Leader or Supervisor can be created.';
        }

        try {
            $userId = User::createLeader([
                'username' => trim($payload['username'] ?? ''),
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'email' => trim($payload['email'] ?? ''),
                'role_id' => $roleId,
                'section_id' => $sectionId,
                'full_name' => trim($payload['full_name'] ?? ''),
            ]);

            return $userId > 0 ? ucfirst($selectedRole['role_name']) . ' created successfully.' : 'Unable to create leader.';
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }
}
