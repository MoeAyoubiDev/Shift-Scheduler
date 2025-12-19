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
        $date = $payload['submit_date'] ?? '';
        if ($date === '') {
            return 'Please select a date.';
        }

        $dateObj = new DateTimeImmutable($date);
        if (!submission_window_open($dateObj)) {
            return 'Shift requests are only allowed Monday through Saturday.';
        }

        $shiftDefinitionId = (int) ($payload['shift_definition_id'] ?? 0);
        $isDayOff = $shiftDefinitionId === 0;

        ShiftRequest::submit([
            'employee_id' => (int) $user['employee_id'],
            'week_id' => $weekId,
            'submit_date' => $date,
            'shift_definition_id' => $shiftDefinitionId,
            'is_day_off' => $isDayOff ? 1 : 0,
            'schedule_pattern_id' => (int) ($payload['schedule_pattern_id'] ?? 0),
            'reason' => trim($payload['reason'] ?? ''),
            'importance_level' => strtoupper(trim($payload['importance_level'] ?? 'NORMAL')),
        ]);

        return 'Shift request submitted.';
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
