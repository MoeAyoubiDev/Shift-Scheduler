<?php
declare(strict_types=1);

require_once __DIR__ . '/../Helpers/helpers.php';
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Models/Employee.php';
require_once __DIR__ . '/../Models/Schedule.php';
require_once __DIR__ . '/../Models/ShiftRequest.php';

class TeamLeaderController
{
    public static function handleCreateEmployee(array $payload): ?string
    {
        require_login();
        require_role(['Team Leader']);
        require_csrf($payload);

        $sectionId = current_section_id();
        if (!$sectionId) {
            return 'Section not selected.';
        }

        $password = trim($payload['password'] ?? '');
        if ($password === '') {
            return 'Password is required.';
        }

        try {
            $employeeId = User::createEmployee([
                'username' => trim($payload['username'] ?? ''),
                'password_hash' => password_hash($password, PASSWORD_BCRYPT),
                'email' => trim($payload['email'] ?? ''),
                'role_id' => (int) ($payload['role_id'] ?? 0),
                'section_id' => $sectionId,
                'employee_code' => trim($payload['employee_code'] ?? ''),
                'full_name' => trim($payload['full_name'] ?? ''),
                'is_senior' => isset($payload['is_senior']) ? 1 : 0,
                'seniority_level' => (int) ($payload['seniority_level'] ?? 0),
            ]);

            return $employeeId > 0 ? 'Employee created successfully.' : 'Unable to create employee.';
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    public static function handleUpdateRequestStatus(array $payload): ?string
    {
        require_login();
        require_role(['Team Leader']);
        require_csrf($payload);

        $requestId = (int) ($payload['request_id'] ?? 0);
        $status = strtoupper(trim($payload['status'] ?? 'PENDING'));
        $user = current_user();

        if ($requestId <= 0 || !in_array($status, ['APPROVED', 'DECLINED'], true)) {
            return 'Invalid request update.';
        }

        ShiftRequest::updateStatus($requestId, $status, (int) $user['employee_id']);
        return 'Request status updated.';
    }

    public static function handleSaveRequirements(array $payload, int $weekId, int $sectionId): string
    {
        require_login();
        require_role(['Team Leader']);
        require_csrf($payload);

        $shiftTypes = Schedule::getShiftTypes();
        foreach ($shiftTypes as $shiftType) {
            $shiftTypeId = (int) $shiftType['id'];
            foreach ($payload['requirements'][$shiftTypeId] ?? [] as $date => $count) {
                Schedule::saveShiftRequirement(
                    $weekId,
                    $sectionId,
                    $date,
                    $shiftTypeId,
                    (int) $count
                );
            }
        }

        return 'Shift requirements saved.';
    }

    public static function handleGenerateSchedule(int $weekId, int $sectionId): string
    {
        require_login();
        require_role(['Team Leader']);

        $user = current_user();
        Schedule::generateWeekly($weekId, $sectionId, (int) $user['employee_id']);
        return 'Weekly schedule generated.';
    }

    public static function handleUpdateAssignment(array $payload): string
    {
        require_login();
        require_role(['Team Leader']);
        require_csrf($payload);

        $assignmentId = (int) ($payload['assignment_id'] ?? 0);
        $shiftDefinitionId = (int) ($payload['shift_definition_id'] ?? 0);
        $employeeId = isset($payload['employee_id']) ? (int) $payload['employee_id'] : null;
        
        if ($assignmentId <= 0 || $shiftDefinitionId <= 0) {
            return 'Invalid schedule update.';
        }

        try {
            Schedule::updateAssignment($assignmentId, $shiftDefinitionId, $employeeId);
            return 'Schedule assignment updated.';
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    public static function handleDeleteAssignment(array $payload): string
    {
        require_login();
        require_role(['Team Leader']);
        require_csrf($payload);

        $assignmentId = (int) ($payload['assignment_id'] ?? 0);
        if ($assignmentId <= 0) {
            return 'Invalid assignment ID.';
        }

        try {
            Schedule::deleteAssignment($assignmentId);
            return 'Assignment deleted.';
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }
}
