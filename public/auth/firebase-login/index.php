<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../app/Helpers/helpers.php';
require_once __DIR__ . '/../../../app/Core/ActionHandler.php';
require_once __DIR__ . '/../../../app/Models/Schedule.php';

header('Content-Type: application/json');

$payload = $_POST;

if (empty($payload)) {
    $raw = file_get_contents('php://input');
    if ($raw) {
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            $payload = $decoded;
        }
    }
}

$payload['action'] = $payload['action'] ?? 'firebase_login';

try {
    $today = new DateTimeImmutable();
    $weekStart = $today->modify('monday this week')->format('Y-m-d');
    $weekEnd = $today->modify('sunday this week')->format('Y-m-d');
    $weekId = Schedule::upsertWeek($weekStart, $weekEnd);

    ActionHandler::initialize($weekId);
    $result = ActionHandler::process($payload, [
        'weekId' => $weekId,
        'weekStart' => $weekStart,
        'weekEnd' => $weekEnd,
    ]);

    echo json_encode($result);
} catch (Throwable $e) {
    error_log('Firebase login endpoint error: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Unable to process authentication request.',
    ]);
}
