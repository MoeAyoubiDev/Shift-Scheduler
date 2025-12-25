<?php
declare(strict_types=1);

require_once __DIR__ . '/../Helpers/helpers.php';
require_once __DIR__ . '/../Models/FcmToken.php';
require_once __DIR__ . '/../Services/FirebaseNotificationService.php';

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

        $pendingWelcomeUserId = $_SESSION['welcome_notification_user_id'] ?? null;
        if ($pendingWelcomeUserId && (int) $pendingWelcomeUserId === (int) $user['id']) {
            try {
                $service = new FirebaseNotificationService();
                $service->sendToUser(
                    (int) $user['id'],
                    'Welcome to Shift Scheduler 🎉',
                    'Your account is ready. You can now manage shifts, requests, and schedules.',
                    ['type' => 'WELCOME']
                );
            } catch (Exception $e) {
                error_log('FCM welcome notification error: ' . $e->getMessage());
            } finally {
                unset($_SESSION['welcome_notification_user_id']);
            }
        }

        return ['success' => true, 'message' => 'Token saved.'];
    }
}
