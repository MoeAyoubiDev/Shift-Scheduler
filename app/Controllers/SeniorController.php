<?php
declare(strict_types=1);

require_once __DIR__ . '/../Helpers/helpers.php';
require_once __DIR__ . '/../Models/Break.php';
require_once __DIR__ . '/../Models/FcmToken.php';
require_once __DIR__ . '/../Services/FirebaseNotificationService.php';

class SeniorController
{
    public static function handleBreakAction(array $payload, string $action): string
    {
        require_login();
        require_role(['Senior']);
        require_csrf($payload);

        $employeeId = (int) ($payload['employee_id'] ?? 0);
        $date = $payload['worked_date'] ?? (new DateTimeImmutable())->format('Y-m-d');

        if ($employeeId <= 0) {
            return 'Invalid employee for break action.';
        }

        if ($action === 'start') {
            $scheduleShiftId = isset($payload['schedule_shift_id']) ? (int) $payload['schedule_shift_id'] : null;
            BreakModel::start($employeeId, $date, $scheduleShiftId);
            self::notifyBreakStatus($employeeId, 'Break started.', [
                'status' => 'STARTED',
                'worked_date' => $date,
            ]);
            return 'Break started.';
        }

        BreakModel::end($employeeId, $date);
        self::notifyBreakStatus($employeeId, 'Break ended.', [
            'status' => 'ENDED',
            'worked_date' => $date,
        ]);
        return 'Break ended.';
    }

    private static function notifyBreakStatus(int $employeeId, string $message, array $data): void
    {
        try {
            $tokens = FcmToken::listTokensForEmployee($employeeId);
            $service = new FirebaseNotificationService();
            $service->sendToTokens($tokens, 'Break status update', $message, array_merge([
                'type' => 'BREAK_STATUS',
                'employee_id' => (string) $employeeId,
            ], $data));
        } catch (Exception $e) {
            error_log('FCM break notify error: ' . $e->getMessage());
        }
    }
}
