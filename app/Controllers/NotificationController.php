<?php
declare(strict_types=1);

require_once __DIR__ . '/../Helpers/helpers.php';
require_once __DIR__ . '/../Models/FcmToken.php';

class NotificationController
{
    public static function handleSaveFcmToken(array $payload): array
    {
        require_login();
        require_csrf($payload);

        $token = trim($payload['token'] ?? '');
        if ($token === '' || strlen($token) > 2048) {
            return ['success' => false, 'message' => 'Invalid token.'];
        }

        $user = current_user();
        if (!$user) {
            return ['success' => false, 'message' => 'User not found.'];
        }

        $deviceType = trim($payload['device_type'] ?? '');
        if ($deviceType === '') {
            $deviceType = trim($payload['platform'] ?? '');
        }
        if ($deviceType === '') {
            $deviceType = 'web';
        }

        try {
            FcmToken::upsertToken(
                (int) $user['id'],
                isset($user['employee_id']) ? (int) $user['employee_id'] : null,
                $user['role'] ?? 'Employee',
                $token,
                $deviceType
            );
        } catch (Exception $e) {
            error_log('FCM token save error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Unable to save token.'];
        }

        return ['success' => true, 'message' => 'Token saved.'];
    }
}
