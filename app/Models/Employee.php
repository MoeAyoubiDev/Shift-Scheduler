<?php
declare(strict_types=1);

require_once __DIR__ . '/../Core/Database.php';

class Employee
{
    private PDO $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    public function create(array $data): array
    {
        $result = ['success' => false, 'message' => '', 'employee_id' => null];
        
        try {
            $procResult = Database::callProcedure('sp_create_employee', [
                $data['user_id'],
                $data['role_id'],
                $data['section_id'],
                $data['employee_code'],
                $data['full_name'],
                $data['email'] ?? null,
                $data['is_senior'] ?? 0,
                $data['seniority_level'] ?? 0
            ]);
            
            if (!empty($procResult)) {
                $row = $procResult[0];
                if ($row['result'] === 'SUCCESS') {
                    $result['success'] = true;
                    $result['employee_id'] = (int) $row['employee_id'];
                    $result['message'] = 'Employee created successfully';
                } else {
                    $result['message'] = $row['result'] ?? 'Failed to create employee';
                }
            }
        } catch (Exception $e) {
            $result['message'] = $e->getMessage();
        }
        
        return $result;
    }
    
    public function getAllBySection(int $sectionId): array
    {
        $stmt = $this->db->prepare("
            SELECT e.*, ur.user_id, s.section_name, r.role_name
            FROM employees e
            INNER JOIN user_roles ur ON e.user_role_id = ur.id
            INNER JOIN sections s ON ur.section_id = s.id
            INNER JOIN roles r ON ur.role_id = r.id
            WHERE s.id = ? AND e.is_active = 1
            ORDER BY e.full_name
        ");
        $stmt->execute([$sectionId]);
        return $stmt->fetchAll();
    }
    
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT e.*, ur.user_id, s.section_name, r.role_name
            FROM employees e
            INNER JOIN user_roles ur ON e.user_role_id = ur.id
            INNER JOIN sections s ON ur.section_id = s.id
            INNER JOIN roles r ON ur.role_id = r.id
            WHERE e.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }
    
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE employees SET is_active = 0 WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
