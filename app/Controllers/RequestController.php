<?php
declare(strict_types=1);

require_once __DIR__ . '/../Helpers/helpers.php';

class RequestController
{
    public static function handleSubmitRequest(PDO $pdo, array $payload): array
    {
        require_login();
        $user = current_user();
        if (!$user || !is_employee($user)) {
            return [
                'message' => 'Only employees can submit requests.',
                'week_start' => current_week_start(),
            ];
        }

        if (!submission_window_open()) {
            return [
                'message' => 'Sorry, you cannot submit a late request. Please contact your Team Leader for more information.',
                'week_start' => current_week_start(),
            ];
        }

        $requestedDay = $payload['requested_day'] ?? 'Monday';
        $shiftType = $payload['shift_type'] ?? 'AM';
        $dayOff = isset($payload['day_off']) ? 1 : 0;
        $scheduleOption = $payload['schedule_option'] ?? '5x2';
        $reason = trim($payload['reason'] ?? '');
        $importance = $payload['importance'] ?? 'low';
        $weekStart = $payload['week_start'] ?: current_week_start();

        if ($reason === '') {
            return [
                'message' => 'Please provide a reason for your request.',
                'week_start' => $weekStart,
            ];
        }

        $previousWeek = (new DateTimeImmutable($weekStart))->modify('-7 days')->format('Y-m-d');
        $prevStmt = $pdo->prepare('SELECT requested_day, shift_type, day_off FROM requests WHERE user_id = :uid AND week_start = :week_start LIMIT 1');
        $prevStmt->execute(['uid' => $user['id'], 'week_start' => $previousWeek]);
        $prevRow = $prevStmt->fetch();
        $previousRequestSummary = $prevRow ? sprintf('%s %s (%s)', $prevRow['requested_day'], $prevRow['shift_type'], $prevRow['day_off'] ? 'Off' : 'On') : null;

        $stmt = $pdo->prepare(
            'INSERT INTO requests (user_id, requested_day, shift_type, day_off, schedule_option, reason, importance, status, submission_date, week_start, previous_week_request)
             VALUES (:user_id, :requested_day, :shift_type, :day_off, :schedule_option, :reason, :importance, "pending", NOW(), :week_start, :previous_week_request)'
        );
        $stmt->execute([
            'user_id' => $user['id'],
            'requested_day' => $requestedDay,
            'shift_type' => $shiftType,
            'day_off' => $dayOff,
            'schedule_option' => $scheduleOption,
            'reason' => $reason,
            'importance' => $importance,
            'week_start' => $weekStart,
            'previous_week_request' => $previousRequestSummary,
        ]);

        return [
            'message' => 'Request submitted successfully.',
            'week_start' => $weekStart,
        ];
    }
}
