<?php
declare(strict_types=1);

require_once __DIR__ . '/../Core/Database.php';

class ShiftRequest
{
    private PDO $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    public function submit(array $data): array
    {
        $result = ['success' => false, 'message' => '', 'request_id' => null];
        
        try {
            // Calculate week start (Monday)
            $submitDate = new DateTime($data['submit_date']);
            $weekStart = clone $submitDate;
            $weekStart->modify('monday this week');
            
            $results = Database::callProcedure('sp_submit_shift_request', [
                $data['employee_id'],
                $weekStart->format('Y-m-d'),
                $data['submit_date'],
                $data['shift_definition_id'] ?? null,
                $data['is_day_off'] ?? 0,
                $data['schedule_pattern_id'],
                $data['reason'] ?? null,
                $data['importance_level'] ?? 'NORMAL'
            ]);
            
            // Check result from procedure
            if (!empty($procResult)) {
                $row = $procResult[0];
                if ($row['result'] === 'SUCCESS') {
                    $result['success'] = true;
                    $result['request_id'] = (int) $row['request_id'];
                    $result['message'] = 'Shift request submitted successfully';
                } else {
                    $result['message'] = $row['result'] ?? 'Failed to submit request';
                }
            }
        } catch (Exception $e) {
            $result['message'] = $e->getMessage();
        }
        
        return $result;
    }
    
    public function review(int $requestId, int $reviewedByEmployeeId, string $status): array
    {
        $result = ['success' => false, 'message' => ''];
        
        try {
            Database::callProcedure('sp_review_shift_request', [
                $requestId,
                $reviewedByEmployeeId,
                $status
            ]);
            
            if (!empty($procResult)) {
                $row = $procResult[0];
                if ($row['result'] === 'SUCCESS') {
                    $result['success'] = true;
                    $result['message'] = "Request {$status} successfully";
                } else {
                    $result['message'] = $row['result'] ?? 'Failed to review request';
                }
            }
        } catch (Exception $e) {
            $result['message'] = $e->getMessage();
        }
        
        return $result;
    }
    
    public function getPendingBySection(int $sectionId, string $weekStart): array
    {
        $stmt = $this->db->prepare("
            SELECT sr.*, e.full_name, e.employee_code, sd.shift_name, sd.category,
                   sp.pattern_name, w.week_start_date
            FROM shift_requests sr
            INNER JOIN employees e ON sr.employee_id = e.id
            INNER JOIN user_roles ur ON e.user_role_id = ur.id
            LEFT JOIN shift_definitions sd ON sr.shift_definition_id = sd.id
            INNER JOIN schedule_patterns sp ON sr.schedule_pattern_id = sp.id
            INNER JOIN weeks w ON sr.week_id = w.id
            WHERE ur.section_id = ? AND sr.status = 'PENDING' AND w.week_start_date = ?
            ORDER BY sr.importance_level DESC, sr.submitted_at ASC
        ");
        $stmt->execute([$sectionId, $weekStart]);
        return $stmt->fetchAll();
    }
    
    public function getByEmployee(int $employeeId, ?string $weekStart = null): array
    {
        $sql = "
            SELECT sr.*, sd.shift_name, sd.category, sp.pattern_name, w.week_start_date
            FROM shift_requests sr
            LEFT JOIN shift_definitions sd ON sr.shift_definition_id = sd.id
            INNER JOIN schedule_patterns sp ON sr.schedule_pattern_id = sp.id
            INNER JOIN weeks w ON sr.week_id = w.id
            WHERE sr.employee_id = ?
        ";
        
        $params = [$employeeId];
        if ($weekStart) {
            $sql .= " AND w.week_start_date = ?";
            $params[] = $weekStart;
        }
        
        $sql .= " ORDER BY sr.submitted_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
