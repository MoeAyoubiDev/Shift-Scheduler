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

    public static function getScheduleData(int $weekId, int $sectionId): array
    {
        require_once __DIR__ . '/../Core/config.php';
        
        // Get schedule entries
        $schedule = Schedule::getWeeklySchedule($weekId, $sectionId);
        $employees = Employee::listBySection($sectionId);
        $shiftDefinitions = Schedule::getShiftDefinitions();
        
        // Transform to employee-based format
        $employeeSchedule = [];
        $employeeHours = [];
        
        foreach ($schedule as $entry) {
            if (empty($entry['employee_id']) || empty($entry['shift_date'])) {
                continue;
            }
            $empId = (int) $entry['employee_id'];
            $date = $entry['shift_date'];
            
            if (!isset($employeeSchedule[$empId])) {
                $employeeSchedule[$empId] = [];
            }
            if (!isset($employeeSchedule[$empId][$date])) {
                $employeeSchedule[$empId][$date] = [];
            }
            
            // Get shift definition details
            $shiftDef = null;
            foreach ($shiftDefinitions as $def) {
                if ((int) $def['definition_id'] === (int) ($entry['shift_definition_id'] ?? 0)) {
                    $shiftDef = $def;
                    break;
                }
            }
            
            $employeeSchedule[$empId][$date][] = [
                'shift_name' => $entry['shift_name'] ?? '',
                'start_time' => $shiftDef['start_time'] ?? '',
                'end_time' => $shiftDef['end_time'] ?? '',
                'category' => $entry['shift_category'] ?? '',
                'assignment_id' => $entry['assignment_id'] ?? null,
                'notes' => $entry['notes'] ?? '',
                'shift_definition_id' => $entry['shift_definition_id'] ?? null,
            ];
            
            // Calculate hours
            if (!isset($employeeHours[$empId])) {
                $employeeHours[$empId] = 0;
            }
            $duration = $shiftDef['duration_hours'] ?? 8.0;
            $employeeHours[$empId] += (float) $duration;
        }
        
        // Get unique employees
        $uniqueEmployees = [];
        foreach ($schedule as $entry) {
            if (!empty($entry['employee_id']) && !empty($entry['employee_name'])) {
                $empId = (int) $entry['employee_id'];
                if (!isset($uniqueEmployees[$empId])) {
                    $uniqueEmployees[$empId] = [
                        'id' => $empId,
                        'name' => $entry['employee_name'],
                        'code' => $entry['employee_code'] ?? '',
                    ];
                }
            }
        }
        
        foreach ($employees as $emp) {
            $empId = (int) $emp['id'];
            if (!isset($uniqueEmployees[$empId])) {
                $uniqueEmployees[$empId] = [
                    'id' => $empId,
                    'name' => $emp['full_name'],
                    'code' => $emp['employee_code'],
                ];
            }
        }
        
        return [
            'schedule' => $employeeSchedule,
            'hours' => $employeeHours,
            'employees' => $uniqueEmployees,
        ];
    }

    public static function handleSwapShifts(array $payload, int $weekId, int $sectionId): string
    {
        require_login();
        require_role(['Team Leader']);
        require_csrf($payload);

        $employee1Id = (int) ($payload['employee1_id'] ?? 0);
        $employee2Id = (int) ($payload['employee2_id'] ?? 0);
        $date = trim($payload['swap_date'] ?? '');
        $assignment1Id = (int) ($payload['assignment1_id'] ?? 0);
        $assignment2Id = (int) ($payload['assignment2_id'] ?? 0);

        if ($employee1Id <= 0 || $employee2Id <= 0 || $date === '') {
            return 'Invalid swap data. Please select both employees and a date.';
        }

        if ($employee1Id === $employee2Id) {
            return 'Cannot swap shifts with the same employee.';
        }

        try {
            require_once __DIR__ . '/../Core/config.php';
            
            // Get schedule for this week
            $stmt = db()->prepare('SELECT id FROM schedules WHERE week_id = :week_id AND section_id = :section_id');
            $stmt->execute(['week_id' => $weekId, 'section_id' => $sectionId]);
            $schedule = $stmt->fetch();
            
            if (!$schedule) {
                return 'Schedule not found for this week.';
            }
            $scheduleId = (int) $schedule['id'];

            // Find assignments for both employees on this date
            $stmt = db()->prepare('
                SELECT sa.id, sa.employee_id, sa.schedule_shift_id, ss.shift_definition_id
                FROM schedule_assignments sa
                INNER JOIN schedule_shifts ss ON ss.id = sa.schedule_shift_id
                WHERE ss.schedule_id = :schedule_id 
                AND ss.shift_date = :date
                AND sa.employee_id IN (:emp1_id, :emp2_id)
            ');
            $stmt->execute([
                'schedule_id' => $scheduleId,
                'date' => $date,
                'emp1_id' => $employee1Id,
                'emp2_id' => $employee2Id
            ]);
            $assignments = $stmt->fetchAll();
            
            $emp1Assignment = null;
            $emp2Assignment = null;
            
            foreach ($assignments as $assignment) {
                if ((int) $assignment['employee_id'] === $employee1Id) {
                    $emp1Assignment = $assignment;
                } elseif ((int) $assignment['employee_id'] === $employee2Id) {
                    $emp2Assignment = $assignment;
                }
            }
            
            if (!$emp1Assignment || !$emp2Assignment) {
                return 'Both employees must have shifts assigned on this date to swap.';
            }
            
            // Swap the employees
            $stmt = db()->prepare('UPDATE schedule_assignments SET employee_id = :new_emp_id WHERE id = :assignment_id');
            $stmt->execute([
                'new_emp_id' => $employee2Id,
                'assignment_id' => (int) $emp1Assignment['id']
            ]);
            
            $stmt->execute([
                'new_emp_id' => $employee1Id,
                'assignment_id' => (int) $emp2Assignment['id']
            ]);
            
            return 'Shifts swapped successfully.';
        } catch (Exception $e) {
            return 'Error swapping shifts: ' . $e->getMessage();
        }
    }

    /**
     * Get command center widget data for overview dashboard
     */
    public static function getCommandCenterData(int $weekId, int $sectionId, string $today): array
    {
        require_once __DIR__ . '/../Core/config.php';
        
        // Pending shift requests with urgency
        $requests = ShiftRequest::listByWeek($weekId, $sectionId);
        $pendingRequests = array_filter($requests, fn($r) => strtoupper($r['status'] ?? '') === 'PENDING');
        $highPriorityRequests = array_filter($pendingRequests, fn($r) => strtoupper($r['importance_level'] ?? '') === 'HIGH');
        
        // Coverage gaps (required vs assigned per day/shift)
        $schedule = Schedule::getWeeklySchedule($weekId, $sectionId);
        $requirements = Schedule::getShiftRequirements($weekId, $sectionId);
        $coverageGaps = self::calculateCoverageGaps($requirements, $schedule);
        
        // Employees currently on break
        $breaks = BreakModel::currentBreaks($sectionId, $today);
        $onBreak = array_filter($breaks, fn($b) => !empty($b['break_start']) && empty($b['break_end']));
        
        // Employees without assignments this week
        $employees = Employee::listBySection($sectionId);
        $assignedEmployeeIds = array_unique(array_map(fn($s) => (int) ($s['employee_id'] ?? 0), $schedule));
        $unassignedEmployees = array_filter($employees, fn($e) => !in_array((int) $e['id'], $assignedEmployeeIds));
        
        // SLA alerts (late breaks, overtime risks)
        $slaAlerts = self::calculateSLAAlerts($breaks, $schedule, $employees);
        
        return [
            'pending_requests' => [
                'total' => count($pendingRequests),
                'high_priority' => count($highPriorityRequests),
                'requests' => array_slice($pendingRequests, 0, 5), // Top 5
            ],
            'coverage_gaps' => $coverageGaps,
            'on_break' => [
                'count' => count($onBreak),
                'employees' => array_slice($onBreak, 0, 5),
            ],
            'unassigned' => [
                'count' => count($unassignedEmployees),
                'employees' => array_slice($unassignedEmployees, 0, 5),
            ],
            'sla_alerts' => $slaAlerts,
        ];
    }

    /**
     * Calculate coverage gaps (required vs assigned)
     */
    private static function calculateCoverageGaps(array $requirements, array $schedule): array
    {
        $gaps = [];
        
        // Group requirements by date and shift
        $reqByDateShift = [];
        foreach ($requirements as $req) {
            $date = $req['date'] ?? '';
            $shiftId = (int) ($req['shift_type_id'] ?? 0);
            if ($date && $shiftId) {
                $key = $date . '_' . $shiftId;
                if (!isset($reqByDateShift[$key])) {
                    $reqByDateShift[$key] = [
                        'date' => $date,
                        'shift_id' => $shiftId,
                        'shift_name' => $req['shift_name'] ?? '',
                        'required' => 0,
                        'assigned' => 0,
                    ];
                }
                $reqByDateShift[$key]['required'] += (int) ($req['required_count'] ?? 0);
            }
        }
        
        // Count assigned per date/shift
        foreach ($schedule as $entry) {
            $date = $entry['shift_date'] ?? '';
            $shiftId = (int) ($entry['shift_definition_id'] ?? 0);
            if ($date && $shiftId) {
                $key = $date . '_' . $shiftId;
                if (isset($reqByDateShift[$key])) {
                    $reqByDateShift[$key]['assigned']++;
                }
            }
        }
        
        // Find gaps
        foreach ($reqByDateShift as $gap) {
            if ($gap['assigned'] < $gap['required']) {
                $gaps[] = $gap;
            }
        }
        
        return $gaps;
    }

    /**
     * Calculate SLA alerts (late breaks, overtime risks)
     */
    private static function calculateSLAAlerts(array $breaks, array $schedule, array $employees): array
    {
        $alerts = [];
        $today = date('Y-m-d');
        
        // Late breaks (break started > 30 min after expected)
        foreach ($breaks as $break) {
            if (!empty($break['break_start']) && empty($break['break_end'])) {
                $delay = (int) ($break['delay_minutes'] ?? 0);
                if ($delay > 30) {
                    $alerts[] = [
                        'type' => 'late_break',
                        'severity' => $delay > 60 ? 'high' : 'medium',
                        'message' => $break['employee_name'] . ' started break ' . $delay . ' minutes late',
                        'employee_id' => (int) ($break['employee_id'] ?? 0),
                    ];
                }
            }
        }
        
        // Overtime risks (employees approaching 40+ hours)
        $employeeHours = [];
        foreach ($schedule as $entry) {
            $empId = (int) ($entry['employee_id'] ?? 0);
            if ($empId) {
                if (!isset($employeeHours[$empId])) {
                    $employeeHours[$empId] = 0;
                }
                $employeeHours[$empId] += (float) ($entry['duration_hours'] ?? 8.0);
            }
        }
        
        foreach ($employeeHours as $empId => $hours) {
            if ($hours >= 38) {
                $emp = array_filter($employees, fn($e) => (int) $e['id'] === $empId);
                $empName = !empty($emp) ? reset($emp)['full_name'] : 'Employee #' . $empId;
                $alerts[] = [
                    'type' => 'overtime_risk',
                    'severity' => $hours >= 40 ? 'high' : 'medium',
                    'message' => $empName . ' has ' . number_format($hours, 1) . ' hours this week',
                    'employee_id' => $empId,
                ];
            }
        }
        
        return $alerts;
    }

    /**
     * Get employee workload intelligence data
     */
    public static function getWorkloadData(int $weekId, int $sectionId, array $employees, array $schedule, array $breaks): array
    {
        $workload = [];
        
        // Calculate hours per employee
        $employeeHours = [];
        foreach ($schedule as $entry) {
            $empId = (int) ($entry['employee_id'] ?? 0);
            if ($empId) {
                if (!isset($employeeHours[$empId])) {
                    $employeeHours[$empId] = 0;
                }
                $employeeHours[$empId] += (float) ($entry['duration_hours'] ?? 8.0);
            }
        }
        
        // Calculate break compliance (last 7 days)
        $breakCompliance = [];
        $today = new DateTimeImmutable();
        for ($i = 0; $i < 7; $i++) {
            $date = $today->modify('-' . $i . ' days')->format('Y-m-d');
            $dayBreaks = BreakModel::currentBreaks($sectionId, $date);
            foreach ($dayBreaks as $break) {
                $empId = (int) ($break['employee_id'] ?? 0);
                if ($empId) {
                    if (!isset($breakCompliance[$empId])) {
                        $breakCompliance[$empId] = ['total' => 0, 'compliant' => 0];
                    }
                    $breakCompliance[$empId]['total']++;
                    if (empty($break['break_start']) || (int) ($break['delay_minutes'] ?? 0) <= 15) {
                        $breakCompliance[$empId]['compliant']++;
                    }
                }
            }
        }
        
        // Build workload data
        foreach ($employees as $emp) {
            $empId = (int) $emp['id'];
            $hours = $employeeHours[$empId] ?? 0;
            $compliance = $breakCompliance[$empId] ?? ['total' => 0, 'compliant' => 0];
            $complianceScore = $compliance['total'] > 0 
                ? ($compliance['compliant'] / $compliance['total']) * 100 
                : 100;
            
            $workload[] = [
                'employee_id' => $empId,
                'employee_name' => $emp['full_name'],
                'employee_code' => $emp['employee_code'],
                'weekly_hours' => $hours,
                'overtime_risk' => $hours >= 40 ? 'high' : ($hours >= 38 ? 'medium' : 'low'),
                'break_compliance' => round($complianceScore, 1),
                'fatigue_flag' => $hours > 45 || $complianceScore < 70,
            ];
        }
        
        return $workload;
    }
}
