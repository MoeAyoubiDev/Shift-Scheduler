<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseModel.php';

class FcmToken extends BaseModel
{
    protected string $table = 'fcm_tokens';

    public static function upsertToken(
        int $userId,
        ?int $employeeId,
        string $role,
        string $token,
        string $deviceType
    ): void {
        $stmt = db()->prepare(
            'INSERT INTO fcm_tokens (user_id, employee_id, role, token, device_type, last_seen, created_at)
             VALUES (:user_id, :employee_id, :role, :token, :device_type, NOW(), NOW())
             ON DUPLICATE KEY UPDATE
                user_id = VALUES(user_id),
                employee_id = VALUES(employee_id),
                role = VALUES(role),
                device_type = VALUES(device_type),
                last_seen = NOW()'
        );

        $stmt->execute([
            'user_id' => $userId,
            'employee_id' => $employeeId,
            'role' => $role,
            'token' => $token,
            'device_type' => $deviceType,
        ]);
    }

    public static function listTokensForEmployee(int $employeeId): array
    {
        $stmt = db()->prepare('SELECT token FROM fcm_tokens WHERE employee_id = :employee_id');
        $stmt->execute(['employee_id' => $employeeId]);
        return array_column($stmt->fetchAll(), 'token');
    }

    public static function listTokensForRoleInSection(string $role, int $sectionId): array
    {
        $stmt = db()->prepare(
            'SELECT DISTINCT ft.token
             FROM fcm_tokens ft
             INNER JOIN user_roles ur ON ur.user_id = ft.user_id
             INNER JOIN roles r ON r.id = ur.role_id
             WHERE r.role_name = :role AND ur.section_id = :section_id'
        );
        $stmt->execute([
            'role' => $role,
            'section_id' => $sectionId,
        ]);

        return array_column($stmt->fetchAll(), 'token');
    }

    public static function listTokensForRolesInSection(array $roles, int $sectionId): array
    {
        if (empty($roles)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($roles), '?'));
        $sql = sprintf(
            'SELECT DISTINCT ft.token
             FROM fcm_tokens ft
             INNER JOIN user_roles ur ON ur.user_id = ft.user_id
             INNER JOIN roles r ON r.id = ur.role_id
             WHERE r.role_name IN (%s) AND ur.section_id = ?',
            $placeholders
        );

        $stmt = db()->prepare($sql);
        $params = array_merge($roles, [$sectionId]);
        $stmt->execute($params);

        return array_column($stmt->fetchAll(), 'token');
    }
}
