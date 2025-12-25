<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/middleware.php';
require_once __DIR__ . '/../../app/Helpers/helpers.php';
require_once __DIR__ . '/../../app/Models/FcmToken.php';
require_once __DIR__ . '/../../app/Services/FirebaseNotificationService.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    json_response(false, 'Method not allowed.');
}

$tokens = FcmToken::listAllTokens();
if (empty($tokens)) {
    http_response_code(404);
    json_response(false, 'No FCM tokens available.');
}

$service = new FirebaseNotificationService();
$result = $service->sendToTokens(
    $tokens,
    'Test Notification',
    'Firebase notifications are working 🎉'
);

if (!empty($result['errors'])) {
    http_response_code(500);
    json_response(false, 'Failed to send some notifications.', [
        'sent' => $result['sent'] ?? 0,
        'errors' => $result['errors'],
    ]);
}

json_response(true, 'Test notification sent.', [
    'sent' => $result['sent'] ?? 0,
]);
