<?php
declare(strict_types=1);

require_once __DIR__ . '/../Helpers/helpers.php';
require_once __DIR__ . '/../Models/Company.php';
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Services/FirebaseAuthService.php';

class AuthController
{
    public static function handleLogout(array $payload): void
    {
        require_csrf($payload);
        logout();
        header('Location: /index.php');
        exit;
    }

    public static function handleFirebaseLogin(array $payload): array
    {
        if (!verify_csrf($payload['csrf_token'] ?? null)) {
            http_response_code(400);
            return ['success' => false, 'message' => 'Invalid security token. Please refresh and try again.'];
        }

        $idToken = trim($payload['firebase_token'] ?? $payload['id_token'] ?? '');
        if ($idToken === '') {
            http_response_code(400);
            return ['success' => false, 'message' => 'Missing authentication token.'];
        }

        try {
            $service = new FirebaseAuthService();
            $tokenData = $service->verifyIdToken($idToken);
        } catch (Exception $e) {
            error_log('Firebase login verification error: ' . $e->getMessage());
            http_response_code(401);
            return ['success' => false, 'message' => 'Unable to verify authentication token. Please sign in again.'];
        }

        $email = $tokenData['email'] ?? '';
        $firebaseUid = $tokenData['uid'] ?? '';
        $provider = $tokenData['provider'] ?? 'email';

        if ($email === '' || $firebaseUid === '') {
            http_response_code(400);
            return ['success' => false, 'message' => 'Authentication payload is incomplete.'];
        }

        $user = User::findByFirebaseUid($firebaseUid);
        if (!$user) {
            $user = User::findByEmail($email);
            if ($user && !empty($user['firebase_uid']) && $user['firebase_uid'] !== $firebaseUid) {
                http_response_code(409);
                return ['success' => false, 'message' => 'This account is already linked to another sign-in.'];
            }
        }

        if ($user) {
            User::updateFirebaseIdentity($user['id'], $firebaseUid, $provider);
            $user = User::findByFirebaseUid($firebaseUid) ?? $user;
        } else {
            http_response_code(404);
            return ['success' => false, 'message' => 'No account found for this email. Please sign up first.'];
        }

        if (!$user) {
            http_response_code(400);
            return ['success' => false, 'message' => 'Unable to complete sign-in.'];
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

    public static function handleFirebaseSignup(array $payload): array
    {
        if (!verify_csrf($payload['csrf_token'] ?? null)) {
            http_response_code(400);
            return ['success' => false, 'message' => 'Invalid security token. Please refresh and try again.'];
        }

        $idToken = trim($payload['firebase_token'] ?? $payload['id_token'] ?? '');
        if ($idToken === '') {
            http_response_code(400);
            return ['success' => false, 'message' => 'Missing authentication token.'];
        }

        $companyName = trim($payload['company_name'] ?? '');
        $timezone = $payload['timezone'] ?? 'UTC';
        $country = trim((string) ($payload['country'] ?? ''));
        $companySize = $payload['company_size'] ?? '';

        if ($companyName === '' || mb_strlen($companyName) < 2) {
            http_response_code(400);
            return ['success' => false, 'message' => 'Company name must be at least 2 characters.'];
        }

        try {
            $service = new FirebaseAuthService();
            $tokenData = $service->verifyIdToken($idToken);
        } catch (Exception $e) {
            error_log('Firebase signup verification error: ' . $e->getMessage());
            http_response_code(401);
            return ['success' => false, 'message' => 'Unable to verify authentication token. Please try again.'];
        }

        $email = $tokenData['email'] ?? '';
        $firebaseUid = $tokenData['uid'] ?? '';
        $provider = $tokenData['provider'] ?? 'email';

        if ($email === '' || $firebaseUid === '') {
            http_response_code(400);
            return ['success' => false, 'message' => 'Authentication payload is incomplete.'];
        }

        $payloadEmail = trim($payload['admin_email'] ?? '');
        if ($payloadEmail !== '' && strcasecmp($payloadEmail, $email) !== 0) {
            http_response_code(400);
            return ['success' => false, 'message' => 'Admin email does not match authentication email.'];
        }

        if (User::findByFirebaseUid($firebaseUid)) {
            http_response_code(409);
            return ['success' => false, 'message' => 'An account with this authentication already exists.'];
        }

        if (User::emailExists($email) || Company::findByEmail($email)) {
            http_response_code(409);
            return ['success' => false, 'message' => 'An account with this email already exists.'];
        }

        $companyResult = Company::createCompany([
            'company_name' => $companyName,
            'admin_email' => $email,
            'timezone' => $timezone,
            'country' => $country !== '' ? $country : null,
            'company_size' => $companySize !== '' ? $companySize : null,
        ]);

        if (empty($companyResult['success'])) {
            http_response_code(400);
            return ['success' => false, 'message' => $companyResult['message'] ?? 'Failed to create account.'];
        }

        $companyId = (int) ($companyResult['company_id'] ?? 0);
        if ($companyId <= 0) {
            http_response_code(400);
            return ['success' => false, 'message' => 'Failed to create account.'];
        }

        $user = User::createFirebaseUser([
            'email' => $email,
            'firebase_uid' => $firebaseUid,
            'provider' => $provider,
            'company_id' => $companyId,
            'role_name' => 'Director',
            'name' => $tokenData['name'] ?? '',
            'section_name' => $companyName . ' - Main',
        ]);

        if (!$user) {
            http_response_code(400);
            return ['success' => false, 'message' => 'Unable to complete sign-up.'];
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
