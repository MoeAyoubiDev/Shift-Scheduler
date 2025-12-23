<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseModel.php';

class User extends BaseModel
{
    protected string $table = 'users';

    public static function authenticate(string $username, string $password): ?array
    {
        $model = new self();
        
        try {
            // First, try to get the user's company_id
            $pdo = db();
            $userStmt = $pdo->prepare("SELECT id, company_id, password_hash, is_active FROM users WHERE username = ? LIMIT 1");
            $userStmt->execute([$username]);
            $userData = $userStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$userData) {
                return null; // User not found
            }
            
            // Verify password before proceeding
            if (!password_verify($password, $userData['password_hash'])) {
                return null; // Invalid password
            }
            
            if (!$userData['is_active']) {
                return null; // User inactive
            }
            
            $companyId = $userData['company_id'] ?? null;
            
            // Try to use stored procedure with company_id
            try {
                if ($companyId !== null) {
                    $rows = $model->callProcedure('sp_verify_login', [
                        'p_username' => $username,
                        'p_company_id' => $companyId,
                    ]);
                } else {
                    // Fallback: try without company_id (for backward compatibility)
                    $rows = $model->callProcedure('sp_verify_login', [
                        'p_username' => $username,
                    ]);
                }
            } catch (PDOException $e) {
                // If stored procedure doesn't exist or has wrong signature, use direct SQL
                if (strpos($e->getMessage(), 'does not exist') !== false || 
                    strpos($e->getMessage(), 'Incorrect number of arguments') !== false ||
                    strpos($e->getMessage(), 'Unknown procedure') !== false) {
                    
                    // Fallback to direct SQL query
                    $sql = "
                        SELECT u.id AS user_id,
                               u.username,
                               u.password_hash,
                               u.email,
                               u.is_active,
                               u.company_id,
                               ur.id AS user_role_id,
                               r.id AS role_id,
                               r.role_name,
                               s.id AS section_id,
                               s.section_name,
                               e.id AS employee_id,
                               e.full_name AS employee_name,
                               e.is_senior,
                               e.seniority_level,
                               e.employee_code
                        FROM users u
                        INNER JOIN user_roles ur ON ur.user_id = u.id
                        INNER JOIN roles r ON r.id = ur.role_id
                        INNER JOIN sections s ON s.id = ur.section_id
                        LEFT JOIN employees e ON e.user_role_id = ur.id
                        WHERE u.username = ? AND u.is_active = 1
                    ";
                    
                    if ($companyId !== null) {
                        $sql .= " AND u.company_id = ?";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([$username, $companyId]);
                    } else {
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([$username]);
                    }
                    
                    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } else {
                    throw $e;
                }
            }

            if (!$rows || empty($rows)) {
                return null;
            }

            $primary = $rows[0];
            
            // Double-check password (already verified, but ensure consistency)
            if (!password_verify($password, $primary['password_hash'])) {
                return null;
            }

            $sections = [];
            foreach ($rows as $row) {
                $sections[] = [
                    'section_id' => (int) $row['section_id'],
                    'section_name' => $row['section_name'],
                ];
            }

            return [
                'id' => (int) $primary['user_id'],
                'username' => $primary['username'],
                'email' => $primary['email'],
                'role' => $primary['role_name'],
                'section_id' => $primary['section_id'] ? (int) $primary['section_id'] : null,
                'section_name' => $primary['section_name'],
                'employee_id' => $primary['employee_id'] ? (int) $primary['employee_id'] : null,
                'is_senior' => (int) ($primary['is_senior'] ?? 0),
                'seniority_level' => (int) ($primary['seniority_level'] ?? 0),
                'employee_code' => $primary['employee_code'] ?? null,
                'full_name' => $primary['employee_name'] ?? null,
                'sections' => $sections,
            ];
        } catch (PDOException $e) {
            // Log the error but don't expose database details
            error_log("Authentication error: " . $e->getMessage());
            return null;
        } catch (Exception $e) {
            // Log unexpected errors
            error_log("Unexpected authentication error: " . $e->getMessage());
            return null;
        }
    }

    public static function createEmployee(array $payload): int
    {
        $model = new self();
        $rows = $model->callProcedure('sp_create_employee', [
            'p_username' => $payload['username'],
            'p_password_hash' => $payload['password_hash'],
            'p_email' => $payload['email'],
            'p_role_id' => $payload['role_id'],
            'p_section_id' => $payload['section_id'],
            'p_employee_code' => $payload['employee_code'],
            'p_full_name' => $payload['full_name'],
            'p_is_senior' => $payload['is_senior'],
            'p_seniority_level' => $payload['seniority_level'],
        ]);

        return (int) ($rows[0]['employee_id'] ?? 0);
    }

    public static function createLeader(array $payload): int
    {
        $model = new self();
        $rows = $model->callProcedure('sp_create_leader', [
            'p_username' => $payload['username'],
            'p_password_hash' => $payload['password_hash'],
            'p_email' => $payload['email'],
            'p_role_id' => $payload['role_id'],
            'p_section_id' => $payload['section_id'],
            'p_full_name' => $payload['full_name'],
        ]);

        return (int) ($rows[0]['user_id'] ?? 0);
    }
}
