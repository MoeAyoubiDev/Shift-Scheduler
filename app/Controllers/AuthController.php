<?php
declare(strict_types=1);

require_once __DIR__ . '/../Helpers/helpers.php';
require_once __DIR__ . '/../Models/Company.php';
require_once __DIR__ . '/../Models/User.php';

class AuthController
{
    public static function handleLogout(array $payload): void
    {
        require_csrf($payload);
        logout();
        header('Location: /index.php');
        exit;
    }

    public static function handleLogin(array $payload): array
    {
        require_csrf($payload);

        $identifier = trim($payload['username'] ?? '');
        $password = (string) ($payload['password'] ?? '');

        if ($identifier === '' || $password === '') {
            return ['success' => false, 'message' => 'Please enter your username/email and password.', 'redirect' => '/login.php'];
        }

        $user = User::authenticate($identifier, $password);
        if (!$user) {
            return ['success' => false, 'message' => 'Invalid credentials. Please try again.', 'redirect' => '/login.php'];
        }

        self::initializeSession($user);

        $companyId = $user['company_id'] ?? null;
        if ($companyId && empty($user['onboarding_completed'])) {
            $_SESSION['onboarding_company_id'] = $companyId;
            $nextStep = self::getNextOnboardingStep((int) $companyId);
            return ['success' => true, 'redirect' => '/onboarding/step-' . $nextStep];
        }

        return ['success' => true, 'redirect' => '/dashboard'];
    }

    public static function handleSignup(array $payload): array
    {
        require_csrf($payload);

        $companyName = trim($payload['company_name'] ?? '');
        $email = trim($payload['admin_email'] ?? '');
        $password = (string) ($payload['admin_password'] ?? '');

        if ($companyName === '' || mb_strlen($companyName) < 2) {
            return ['success' => false, 'message' => 'Company name must be at least 2 characters.', 'redirect' => '/signup.php'];
        }

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Please provide a valid admin email address.', 'redirect' => '/signup.php'];
        }

        if (mb_strlen($password) < 8) {
            return ['success' => false, 'message' => 'Password must be at least 8 characters long.', 'redirect' => '/signup.php'];
        }

        if (User::emailExists($email) || Company::findByEmail($email)) {
            return ['success' => false, 'message' => 'An account with this email already exists.', 'redirect' => '/signup.php'];
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $companyResult = Company::createCompany([
            'company_name' => $companyName,
            'admin_email' => $email,
            'admin_password_hash' => $passwordHash,
            'timezone' => $payload['timezone'] ?? 'UTC',
            'country' => $payload['country'] ?? null,
            'company_size' => $payload['company_size'] ?? null,
        ]);

        if (empty($companyResult['success'])) {
            return ['success' => false, 'message' => $companyResult['message'] ?? 'Failed to create account.', 'redirect' => '/signup.php'];
        }

        $companyId = (int) ($companyResult['company_id'] ?? 0);
        if ($companyId <= 0) {
            return ['success' => false, 'message' => 'Failed to create account.', 'redirect' => '/signup.php'];
        }

        $pdo = db();
        $pdo->beginTransaction();

        try {
            $sectionStmt = $pdo->prepare("INSERT INTO sections (section_name, company_id) VALUES (?, ?)");
            $sectionStmt->execute([$companyName . ' - Main', $companyId]);
            $sectionId = (int) $pdo->lastInsertId();

            $roleStmt = $pdo->prepare("SELECT id FROM roles WHERE role_name = 'Director' LIMIT 1");
            $roleStmt->execute();
            $directorRoleId = (int) ($roleStmt->fetchColumn() ?: 0);
            if ($directorRoleId <= 0) {
                throw new RuntimeException('Director role not found.');
            }

            $usernameBase = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '', strstr($email, '@', true) ?: 'admin'));
            $usernameCandidate = $usernameBase ?: 'admin';
            $suffix = 1;

            while (true) {
                $checkStmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND company_id = ? LIMIT 1");
                $checkStmt->execute([$usernameCandidate, $companyId]);
                if (!$checkStmt->fetch(PDO::FETCH_ASSOC)) {
                    break;
                }
                $usernameCandidate = $usernameBase . $suffix;
                $suffix++;
            }

            $userStmt = $pdo->prepare("
                INSERT INTO users (company_id, username, password_hash, email, role, onboarding_completed, is_active)
                VALUES (?, ?, ?, ?, 'Director', 0, 1)
            ");
            $userStmt->execute([$companyId, $usernameCandidate, $passwordHash, $email]);
            $userId = (int) $pdo->lastInsertId();

            $roleAssignStmt = $pdo->prepare("INSERT INTO user_roles (user_id, role_id, section_id) VALUES (?, ?, ?)");
            $roleAssignStmt->execute([$userId, $directorRoleId, $sectionId]);

            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            error_log('Signup creation error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Unable to finish signup. Please try again.', 'redirect' => '/signup.php'];
        }

        $user = User::findByEmail($email);
        if (!$user) {
            return ['success' => false, 'message' => 'Unable to complete sign-up.', 'redirect' => '/signup.php'];
        }

        self::markCompanyOnboarding($companyId);
        self::initializeSession($user);
        $_SESSION['onboarding_company_id'] = $companyId;

        return ['success' => true, 'redirect' => '/onboarding/step-1'];
    }

    private static function initializeSession(array $user): void
    {
        $_SESSION['user'] = $user;
        $_SESSION['user_id'] = $user['id'] ?? null;
        $_SESSION['role'] = $user['role'] ?? null;
        $_SESSION['company_id'] = $user['company_id'] ?? null;
    }

    private static function getNextOnboardingStep(int $companyId): int
    {
        $progress = Company::getOnboardingProgress($companyId);
        for ($step = 1; $step <= 5; $step++) {
            $stepKey = "step_{$step}";
            if (empty($progress[$stepKey]) || empty($progress[$stepKey]['completed'])) {
                return $step;
            }
        }

        return 5;
    }

    private static function markCompanyOnboarding(int $companyId): void
    {
        $pdo = db();
        $stmt = $pdo->prepare("UPDATE companies SET status = 'ONBOARDING' WHERE id = ? AND status != 'ACTIVE'");
        $stmt->execute([$companyId]);
    }
}
