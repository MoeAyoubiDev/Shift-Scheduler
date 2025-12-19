<?php
declare(strict_types=1);

require_once __DIR__ . '/../Helpers/helpers.php';
require_once __DIR__ . '/../Helpers/schedule.php';

class AdminController
{
    public static function handleUpdateRequestStatus(PDO $pdo, array $payload): ?string
    {
        require_login();
        $user = current_user();
        if (!$user || !is_primary_admin($user)) {
            return 'Only the Primary Admin can update requests.';
        }

        $requestId = (int) ($payload['request_id'] ?? 0);
        $status = $payload['status'] ?? 'pending';
        $stmt = $pdo->prepare('UPDATE requests SET status = :status WHERE id = :id');
        $stmt->execute(['status' => $status, 'id' => $requestId]);

        return null;
    }

    public static function handleToggleFlag(PDO $pdo, array $payload): ?string
    {
        require_login();
        $user = current_user();
        if (!$user || !is_primary_admin($user)) {
            return 'Only the Primary Admin can flag requests.';
        }

        $requestId = (int) ($payload['request_id'] ?? 0);
        $flagged = (int) ($payload['flagged'] ?? 0);
        $stmt = $pdo->prepare('UPDATE requests SET flagged = :flagged WHERE id = :id');
        $stmt->execute(['flagged' => $flagged, 'id' => $requestId]);

        return null;
    }

    public static function handleToggleSubmission(string $weekStart, array $payload): ?string
    {
        require_login();
        $user = current_user();
        if (!$user || !is_primary_admin($user)) {
            return 'Only the Primary Admin can toggle submissions.';
        }

        $locked = isset($payload['locked']);
        set_submission_lock($weekStart, $locked);

        return null;
    }

    public static function handleDeleteEmployee(PDO $pdo, array $payload): ?string
    {
        require_login();
        $user = current_user();
        if (!$user || !is_primary_admin($user)) {
            return 'Only the Primary Admin can delete employees.';
        }

        $employeeId = (int) ($payload['employee_id'] ?? 0);
        $pdo->prepare('DELETE FROM users WHERE id = :id AND role = "employee"')->execute(['id' => $employeeId]);

        return null;
    }

    public static function handleSaveRequirements(string $weekStart, array $payload): ?string
    {
        require_login();
        $user = current_user();
        if (!$user || !is_primary_admin($user)) {
            return 'Only the Primary Admin can edit requirements.';
        }

        $am = (int) ($payload['am_required'] ?? 0);
        $pm = (int) ($payload['pm_required'] ?? 0);
        $mid = (int) ($payload['mid_required'] ?? 0);
        $senior = trim($payload['senior_staff'] ?? '');
        save_shift_requirements($weekStart, $am, $pm, $mid, $senior);

        return 'Requirements saved.';
    }

    public static function handleGenerateSchedule(string $weekStart): ?string
    {
        require_login();
        $user = current_user();
        if (!$user || !is_primary_admin($user)) {
            return 'Only the Primary Admin can generate the schedule.';
        }

        generate_schedule($weekStart);

        return 'Schedule generated for the week.';
    }

    public static function handleUpdateScheduleEntry(array $payload): ?string
    {
        require_login();
        $user = current_user();
        if (!$user || !is_primary_admin($user)) {
            return 'Only the Primary Admin can edit the schedule.';
        }

        $entryId = (int) ($payload['entry_id'] ?? 0);
        $day = $payload['day'] ?? 'Monday';
        $shift = $payload['shift_type'] ?? 'AM';
        $notes = trim($payload['notes'] ?? '');
        update_schedule_entry($entryId, $day, $shift, $notes);

        return 'Schedule entry updated.';
    }
}
