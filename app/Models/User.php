<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseModel.php';

class User extends BaseModel
{
    protected string $table = 'users';

    private static function mapUserRows(array $rows): ?array
    {
        if (empty($rows)) {
            return null;
        }

        $primary = $rows[0];
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
            'company_id' => $primary['company_id'] ? (int) $primary['company_id'] : null,
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
    }

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

            return self::mapUserRows($rows);
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

    public static function findByEmail(string $email): ?array
    {
        try {
            $pdo = db();
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
                WHERE u.email = ? AND u.is_active = 1
            ";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$email]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return self::mapUserRows($rows);
        } catch (PDOException $e) {
            error_log("User lookup by email failed: " . $e->getMessage());
            return null;
        }
    }

    public static function findByFirebaseUid(string $firebaseUid): ?array
    {
        try {
            $pdo = db();
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
                WHERE u.firebase_uid = ? AND u.is_active = 1
            ";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$firebaseUid]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return self::mapUserRows($rows);
        } catch (PDOException $e) {
            error_log("User lookup by Firebase UID failed: " . $e->getMessage());
            return null;
        }
    }

    public static function updateFirebaseIdentity(int $userId, string $firebaseUid, string $provider): void
    {
        $pdo = db();
        $stmt = $pdo->prepare("UPDATE users SET firebase_uid = ?, provider = ? WHERE id = ?");
        $stmt->execute([$firebaseUid, $provider, $userId]);
    }

    public static function emailExists(string $email): bool
    {
        try {
            $pdo = db();
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Email lookup failed: " . $e->getMessage());
            return false;
        }
    }

    public static function createFirebaseUser(array $payload): ?array
    {
        $pdo = db();
        $pdo->beginTransaction();

        try {
            $email = $payload['email'];
            $firebaseUid = $payload['firebase_uid'];
            $provider = $payload['provider'];
            $companyId = $payload['company_id'] ?? null;
            $roleName = $payload['role_name'] ?? 'Employee';
            $displayName = trim($payload['name'] ?? '');

            $usernameBase = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '', strstr($email, '@', true) ?: 'user'));
            $usernameCandidate = $usernameBase ?: 'user';
            $suffix = 1;

            while (true) {
                $checkStmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND company_id <=> ? LIMIT 1");
                $checkStmt->execute([$usernameCandidate, $companyId]);
                if (!$checkStmt->fetch(PDO::FETCH_ASSOC)) {
                    break;
                }
                $usernameCandidate = $usernameBase . $suffix;
                $suffix++;
            }

            $insertUser = $pdo->prepare("
                INSERT INTO users (company_id, username, password_hash, email, firebase_uid, provider)
                VALUES (?, ?, NULL, ?, ?, ?)
            ");
            $insertUser->execute([$companyId, $usernameCandidate, $email, $firebaseUid, $provider]);
            $userId = (int) $pdo->lastInsertId();

            if ($userId <= 0) {
                throw new RuntimeException('Failed to create user.');
            }

            $sectionStmt = $pdo->prepare("SELECT id FROM sections WHERE company_id <=> ? ORDER BY id LIMIT 1");
            $sectionStmt->execute([$companyId]);
            $sectionRow = $sectionStmt->fetch(PDO::FETCH_ASSOC);
            $sectionId = (int) ($sectionRow['id'] ?? 0);

            if ($sectionId === 0) {
                $sectionName = $payload['section_name'] ?? 'General';
                $createSection = $pdo->prepare("INSERT INTO sections (section_name, company_id) VALUES (?, ?)");
                $createSection->execute([$sectionName, $companyId]);
                $sectionId = (int) $pdo->lastInsertId();
            }

            $roleStmt = $pdo->prepare("SELECT id FROM roles WHERE role_name = ? LIMIT 1");
            $roleStmt->execute([$roleName]);
            $roleRow = $roleStmt->fetch(PDO::FETCH_ASSOC);
            $roleId = (int) ($roleRow['id'] ?? 0);

            if ($roleId === 0) {
                throw new RuntimeException('Default role not found.');
            }

            $userRoleStmt = $pdo->prepare("INSERT INTO user_roles (user_id, role_id, section_id) VALUES (?, ?, ?)");
            $userRoleStmt->execute([$userId, $roleId, $sectionId]);
            $userRoleId = (int) $pdo->lastInsertId();

            if (in_array($roleName, ['Employee', 'Senior'], true)) {
                $employeeCode = 'EMP-' . str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
                $codeCheck = $pdo->prepare("SELECT id FROM employees WHERE employee_code = ? LIMIT 1");
                while (true) {
                    $codeCheck->execute([$employeeCode]);
                    if (!$codeCheck->fetch(PDO::FETCH_ASSOC)) {
                        break;
                    }
                    $employeeCode = 'EMP-' . str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
                }

                $fullName = $displayName !== '' ? $displayName : ucfirst(str_replace('.', ' ', strstr($email, '@', true)));
                $employeeStmt = $pdo->prepare("
                    INSERT INTO employees (user_role_id, employee_code, full_name, email, is_senior, seniority_level)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $employeeStmt->execute([
                    $userRoleId,
                    $employeeCode,
                    $fullName,
                    $email,
                    $roleName === 'Senior' ? 1 : 0,
                    0,
                ]);
            }

            $pdo->commit();
            return self::findByEmail($email);
        } catch (Throwable $e) {
            $pdo->rollBack();
            error_log("Firebase user creation failed: " . $e->getMessage());
            return null;
        }
    }
}
