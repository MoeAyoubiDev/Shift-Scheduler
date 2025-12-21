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

        // Validate required fields
        $username = trim($payload['username'] ?? '');
        $email = trim($payload['email'] ?? '');
        $employeeCode = trim($payload['employee_code'] ?? '');
        $fullName = trim($payload['full_name'] ?? '');

        if ($username === '' || $employeeCode === '' || $fullName === '') {
            return 'Username, employee code, and full name are required.';
        }

        try {
            $employeeId = User::createEmployee([
                'username' => $username,
                'password_hash' => password_hash($password, PASSWORD_BCRYPT),
                'email' => $email,
                'role_id' => (int) ($payload['role_id'] ?? 0),
                'section_id' => $sectionId,
                'employee_code' => $employeeCode,
                'full_name' => $fullName,
                'is_senior' => isset($payload['is_senior']) ? 1 : 0,
                'seniority_level' => (int) ($payload['seniority_level'] ?? 0),
            ]);

            return $employeeId > 0 ? 'Employee created successfully.' : 'Unable to create employee.';
        } catch (PDOException $e) {
            $errorMessage = $e->getMessage();
            // Extract user-friendly error from SQLSTATE message
            if (strpos($errorMessage, 'Username already exists') !== false) {
                return 'Username already exists. Please choose a different username.';
            } elseif (strpos($errorMessage, 'Email already exists') !== false) {
                return 'Email already exists. Please use a different email address.';
            } elseif (strpos($errorMessage, 'Employee code already exists') !== false) {
                return 'Employee code already exists. Please use a different employee code.';
            } elseif (strpos($errorMessage, 'Full name already exists') !== false) {
                return 'Full name already exists. Please use a different full name.';
            } elseif (strpos($errorMessage, 'Duplicate entry') !== false) {
                if (strpos($errorMessage, 'username') !== false) {
                    return 'Username already exists. Please choose a different username.';
                } elseif (strpos($errorMessage, 'email') !== false) {
                    return 'Email already exists. Please use a different email address.';
                }
                return 'A record with this information already exists.';
            }
            return 'Error creating employee: ' . $errorMessage;
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

    public static function handleUpdateEmployee(array $payload): ?string
    {
        require_login();
        require_role(['Team Leader']);
        require_csrf($payload);

        $employeeId = (int) ($payload['employee_id'] ?? 0);
        if ($employeeId <= 0) {
            return 'Invalid employee ID.';
        }

        $sectionId = current_section_id();
        if (!$sectionId) {
            return 'Section not selected.';
        }

        // For now, return a message indicating update functionality
        // This would need a database procedure to implement fully
        return 'Employee update functionality - to be implemented with database procedure.';
    }

    public static function handleDeleteEmployee(array $payload): ?string
    {
        require_login();
        require_role(['Team Leader']);
        require_csrf($payload);

        $employeeId = (int) ($payload['employee_id'] ?? 0);
        if ($employeeId <= 0) {
            return 'Invalid employee ID.';
        }

        $sectionId = current_section_id();
        if (!$sectionId) {
            return 'Section not selected.';
        }

        try {
            // Deactivate employee by setting is_active = 0
            require_once __DIR__ . '/../Core/config.php';
            $stmt = db()->prepare('UPDATE employees e 
                INNER JOIN user_roles ur ON ur.id = e.user_role_id 
                SET e.is_active = 0 
                WHERE e.id = :employee_id AND ur.section_id = :section_id');
            $stmt->execute([
                'employee_id' => $employeeId,
                'section_id' => $sectionId,
            ]);

            return 'Employee deleted successfully.';
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    public static function handleAssignShift(array $payload, int $weekId, int $sectionId): string
    {
        require_login();
        require_role(['Team Leader']);
        require_csrf($payload);

        // Get employee_id from select dropdown (not hidden field)
        $employeeId = (int) ($payload['employee_id'] ?? 0);
        $date = trim($payload['date'] ?? '');
        $shiftDefinitionId = (int) ($payload['shift_definition_id'] ?? 0);
        $customStartTime = trim($payload['custom_start_time'] ?? '');
        $customEndTime = trim($payload['custom_end_time'] ?? '');
        $notes = trim($payload['notes'] ?? '');
        $requestId = !empty($payload['request_id']) ? (int) $payload['request_id'] : null;

        if ($employeeId <= 0 || $date === '' || $shiftDefinitionId <= 0) {
            return 'Invalid assignment data. Please fill all required fields.';
        }

        try {
            require_once __DIR__ . '/../Core/config.php';
            
            // Get or create schedule for this week
            $stmt = db()->prepare('SELECT id FROM schedules WHERE week_id = :week_id AND section_id = :section_id');
            $stmt->execute(['week_id' => $weekId, 'section_id' => $sectionId]);
            $schedule = $stmt->fetch();
            
            if (!$schedule) {
                // Create schedule
                $user = current_user();
                $stmt = db()->prepare('INSERT INTO schedules (week_id, section_id, generated_by_admin_id) VALUES (:week_id, :section_id, :admin_id)');
                $stmt->execute([
                    'week_id' => $weekId,
                    'section_id' => $sectionId,
                    'admin_id' => (int) $user['employee_id']
                ]);
                $scheduleId = (int) db()->lastInsertId();
            } else {
                $scheduleId = (int) $schedule['id'];
            }

            // Get or create schedule_shift for this date and shift definition
            $stmt = db()->prepare('SELECT id FROM schedule_shifts WHERE schedule_id = :schedule_id AND shift_date = :date AND shift_definition_id = :shift_def_id');
            $stmt->execute([
                'schedule_id' => $scheduleId,
                'date' => $date,
                'shift_def_id' => $shiftDefinitionId
            ]);
            $scheduleShift = $stmt->fetch();
            
            if (!$scheduleShift) {
                // Create schedule_shift
                $stmt = db()->prepare('INSERT INTO schedule_shifts (schedule_id, shift_date, shift_definition_id, required_count) VALUES (:schedule_id, :date, :shift_def_id, 1)');
                $stmt->execute([
                    'schedule_id' => $scheduleId,
                    'date' => $date,
                    'shift_def_id' => $shiftDefinitionId
                ]);
                $scheduleShiftId = (int) db()->lastInsertId();
            } else {
                $scheduleShiftId = (int) $scheduleShift['id'];
            }

            // Check if assignment already exists for this employee on this shift
            $stmt = db()->prepare('SELECT id FROM schedule_assignments WHERE schedule_shift_id = :shift_id AND employee_id = :emp_id');
            $stmt->execute(['shift_id' => $scheduleShiftId, 'emp_id' => $employeeId]);
            $existing = $stmt->fetch();

            // Build notes with custom times if provided
            $assignmentNotes = $notes;
            if ($customStartTime || $customEndTime) {
                $timeNote = 'Custom times: ';
                if ($customStartTime) $timeNote .= $customStartTime;
                if ($customStartTime && $customEndTime) $timeNote .= ' - ';
                if ($customEndTime) $timeNote .= $customEndTime;
                $assignmentNotes = ($notes ? $notes . ' | ' : '') . $timeNote;
            }

            if ($existing) {
                // Update existing assignment
                $stmt = db()->prepare('UPDATE schedule_assignments SET notes = :notes, assignment_source = "MANUALLY_ADJUSTED" WHERE id = :id');
                $stmt->execute([
                    'id' => (int) $existing['id'],
                    'notes' => $assignmentNotes
                ]);
            } else {
                // Create new assignment
                $stmt = db()->prepare('INSERT INTO schedule_assignments (schedule_shift_id, employee_id, assignment_source, notes) VALUES (:shift_id, :emp_id, :source, :notes)');
                $stmt->execute([
                    'shift_id' => $scheduleShiftId,
                    'emp_id' => $employeeId,
                    'source' => $requestId ? 'MATCHED_REQUEST' : 'MANUALLY_ADJUSTED',
                    'notes' => $assignmentNotes
                ]);
            }

            // If request ID provided, auto-approve it
            if ($requestId && $requestId > 0) {
                $user = current_user();
                ShiftRequest::updateStatus($requestId, 'APPROVED', (int) $user['employee_id']);
            }

            return 'Shift assigned successfully.';
        } catch (Exception $e) {
            return 'Error assigning shift: ' . $e->getMessage();
        }
    }
}
