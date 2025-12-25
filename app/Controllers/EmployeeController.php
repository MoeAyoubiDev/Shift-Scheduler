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
        
        // Validate submission window: can only submit during current week, but NOT on Sunday
        $today = new DateTimeImmutable();
        $currentWeekStart = $today->modify('monday this week');
        $currentWeekEnd = $currentWeekStart->modify('+6 days');
        $todayDayOfWeek = (int) $today->format('N'); // 1 = Monday, 7 = Sunday
        
        $todayDate = $today->format('Y-m-d');
        if ($todayDate < $currentWeekStart->format('Y-m-d') || $todayDate > $currentWeekEnd->format('Y-m-d')) {
            return 'Shift requests can only be submitted during the current week. Please contact your Team Leader.';
        }
        
        // Block submissions on Sunday
        if ($todayDayOfWeek === 7) {
            return 'Submissions are not allowed on Sunday. Please submit your requests Monday through Saturday.';
        }

        $date = $payload['request_date'] ?? $payload['submit_date'] ?? '';
        if ($date === '') {
            return 'Please select a day.';
        }

        $dateObj = new DateTimeImmutable($date);
        $dayOfWeek = (int) $dateObj->format('N'); // 1 = Monday, 7 = Sunday
        
        // Allow all days of next week (Monday-Sunday, 1-7)
        if ($dayOfWeek < 1 || $dayOfWeek > 7) {
            return 'Invalid day selected.';
        }
        // Note: Sunday requests are allowed for next week, but submissions are blocked on Sunday

        // Validate that request is for NEXT week only
        $nextWeekStart = $currentWeekStart->modify('+7 days');
        $nextWeekEnd = $nextWeekStart->modify('+6 days');
        
        $requestDate = $dateObj->format('Y-m-d');
        if ($requestDate < $nextWeekStart->format('Y-m-d') || $requestDate > $nextWeekEnd->format('Y-m-d')) {
            return 'Shift requests can only be submitted for next week (' . $nextWeekStart->format('M j') . ' - ' . $nextWeekEnd->format('M j') . ').';
        }

        // Get the correct week_id for next week
        require_once __DIR__ . '/../Models/Schedule.php';
        $nextWeekId = Schedule::upsertWeek($nextWeekStart->format('Y-m-d'), $nextWeekEnd->format('Y-m-d'));

        $shiftDefinitionId = (int) ($payload['shift_definition_id'] ?? 0);
        $isDayOff = $shiftDefinitionId === 0;
        $importance = strtoupper(trim($payload['importance_level'] ?? 'MEDIUM'));
        $allowedImportance = ['LOW', 'MEDIUM', 'HIGH', 'EMERGENCY'];
        if (!in_array($importance, $allowedImportance, true)) {
            $importance = 'MEDIUM';
        }

        try {
            ShiftRequest::submit([
                'employee_id' => (int) $user['employee_id'],
                'week_id' => $nextWeekId, // Use next week's week_id
                'request_date' => $date,
                'shift_definition_id' => $shiftDefinitionId,
                'is_day_off' => $isDayOff ? 1 : 0,
                'schedule_pattern_id' => (int) ($payload['schedule_pattern_id'] ?? 0),
                'reason' => trim($payload['reason'] ?? ''),
                'importance_level' => $importance,
            ]);

            json_response(true, 'Shift request submitted successfully for next week.');
        } catch (Exception $e) {
            json_response(false, $e->getMessage());
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
