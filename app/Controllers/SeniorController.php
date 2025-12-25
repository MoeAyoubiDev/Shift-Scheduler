<?php
declare(strict_types=1);

require_once __DIR__ . '/../Helpers/helpers.php';
require_once __DIR__ . '/../Models/Break.php';

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
            return 'Break started.';
        }

        BreakModel::end($employeeId, $date);
        return 'Break ended.';
    }
}
