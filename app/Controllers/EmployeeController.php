<?php
declare(strict_types=1);

require_once __DIR__ . '/../Helpers/helpers.php';
require_once __DIR__ . '/../Models/ShiftRequest.php';
require_once __DIR__ . '/../Models/Break.php';

class EmployeeController
{
    public static function handleSubmitRequest(array $payload, int $weekId): string
    {
        require_login();
        require_role(['Employee']);
        require_csrf($payload);

        $user = current_user();
        
        // Check if user is Senior (Seniors cannot submit requests)
        if ($user['role'] === 'Senior' || ($user['is_senior'] ?? 0) === 1) {
            return 'Senior employees cannot submit shift requests.';
        }

        $date = $payload['request_date'] ?? $payload['submit_date'] ?? '';
        if ($date === '') {
            return 'Please select a date.';
        }

        $dateObj = new DateTimeImmutable($date);
        $dayOfWeek = (int) $dateObj->format('N'); // 1 = Monday, 7 = Sunday
        
        // Block Sunday (day 7)
        if ($dayOfWeek === 7) {
            return 'Shift requests are not allowed on Sunday.';
        }

        // Only allow Monday-Saturday (1-6)
        if ($dayOfWeek < 1 || $dayOfWeek > 6) {
            return 'Shift requests are only allowed Monday through Saturday.';
        }

        $shiftDefinitionId = (int) ($payload['shift_definition_id'] ?? 0);
        $isDayOff = $shiftDefinitionId === 0;

        try {
            ShiftRequest::submit([
                'employee_id' => (int) $user['employee_id'],
                'week_id' => $weekId,
                'request_date' => $date,
                'shift_definition_id' => $shiftDefinitionId,
                'is_day_off' => $isDayOff ? 1 : 0,
                'schedule_pattern_id' => (int) ($payload['schedule_pattern_id'] ?? 0),
                'reason' => trim($payload['reason'] ?? ''),
                'importance_level' => strtoupper(trim($payload['importance_level'] ?? 'NORMAL')),
            ]);

            return 'Shift request submitted successfully.';
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    public static function handleBreakAction(array $payload, string $action): string
    {
        require_login();
        require_role(['Employee']);
        require_csrf($payload);

        $user = current_user();
        $date = $payload['worked_date'] ?? (new DateTimeImmutable())->format('Y-m-d');

        if ($action === 'start') {
            $scheduleShiftId = isset($payload['schedule_shift_id']) ? (int) $payload['schedule_shift_id'] : null;
            BreakModel::start((int) $user['employee_id'], $date, $scheduleShiftId);
            return 'Break started.';
        }

        BreakModel::end((int) $user['employee_id'], $date);
        return 'Break ended.';
    }
}
