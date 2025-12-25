<?php
declare(strict_types=1);

require_once __DIR__ . '/../Helpers/helpers.php';
require_once __DIR__ . '/../Models/Company.php';
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Services/FirebaseAuthService.php';

class AuthController
{
    public static function handleLogin(string $username, string $password, array $payload): ?string
    {
        try {
            require_csrf($payload);
        } catch (Exception $e) {
            error_log("CSRF error in login: " . $e->getMessage());
            http_response_code(400);
            return 'Error 400: Invalid request. Please try again.';
        }

        try {
            if (login($username, $password)) {
                header('Location: /index.php');
                exit;
            }

            // Authentication failed - return generic message
            return 'Invalid username or password.';
        } catch (PDOException $e) {
            // Database errors - log but don't expose details
            error_log("Database error during login: " . $e->getMessage());
            http_response_code(400);
            return 'Error 400: Unable to process login. Please try again.';
        } catch (Exception $e) {
            // Any other errors
            error_log("Unexpected error during login: " . $e->getMessage());
            http_response_code(400);
            return 'Error 400: An error occurred. Please try again.';
        }
    }

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
            return ['success' => false, 'message' => 'Invalid request. Please try again.'];
        }

        $idToken = trim($payload['id_token'] ?? '');
        if ($idToken === '') {
            http_response_code(400);
            return ['success' => false, 'message' => 'Missing authentication token.'];
        }

        try {
            $service = new FirebaseAuthService();
            $tokenData = $service->verifyIdToken($idToken);
        } catch (Exception $e) {
            http_response_code(401);
            return ['success' => false, 'message' => 'Unable to verify authentication token.'];
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
        }

        if ($user) {
            User::updateFirebaseIdentity($user['id'], $firebaseUid, $provider);
        } else {
            if (User::emailExists($email)) {
                http_response_code(409);
                return ['success' => false, 'message' => 'An account with this email already exists.'];
            }

            $company = Company::findByEmail($email);
            $roleName = $company ? 'Director' : 'Employee';
            $user = User::createFirebaseUser([
                'email' => $email,
                'firebase_uid' => $firebaseUid,
                'provider' => $provider,
                'company_id' => $company['id'] ?? null,
                'role_name' => $roleName,
                'name' => $tokenData['name'] ?? '',
            ]);
        }

        if (!$user) {
            http_response_code(400);
            return ['success' => false, 'message' => 'Unable to complete sign-in.'];
        }

        $_SESSION['user'] = $user;

        return ['success' => true, 'redirect' => '/index.php'];
    }

    public static function handleFirebaseSignup(array $payload): array
    {
        if (!verify_csrf($payload['csrf_token'] ?? null)) {
            http_response_code(400);
            return ['success' => false, 'message' => 'Invalid request. Please try again.'];
        }

        $idToken = trim($payload['id_token'] ?? '');
        if ($idToken === '') {
            http_response_code(400);
            return ['success' => false, 'message' => 'Missing authentication token.'];
        }

        $companyName = trim($payload['company_name'] ?? '');
        $adminPassword = (string) ($payload['admin_password'] ?? '');
        $timezone = $payload['timezone'] ?? 'UTC';
        $country = trim((string) ($payload['country'] ?? ''));
        $companySize = $payload['company_size'] ?? '';

        if ($companyName === '' || mb_strlen($companyName) < 2) {
            http_response_code(400);
            return ['success' => false, 'message' => 'Company name must be at least 2 characters.'];
        }

        if (strlen($adminPassword) < 8) {
            http_response_code(400);
            return ['success' => false, 'message' => 'Password must be at least 8 characters.'];
        }

        try {
            $service = new FirebaseAuthService();
            $tokenData = $service->verifyIdToken($idToken);
        } catch (Exception $e) {
            http_response_code(401);
            return ['success' => false, 'message' => 'Unable to verify authentication token.'];
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

        if (User::emailExists($email) || Company::findByEmail($email)) {
            http_response_code(409);
            return ['success' => false, 'message' => 'An account with this email already exists.'];
        }

        $companyResult = Company::createCompany([
            'company_name' => $companyName,
            'admin_email' => $email,
            'admin_password' => $adminPassword,
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
        ]);

        if (!$user) {
            http_response_code(400);
            return ['success' => false, 'message' => 'Unable to complete sign-up.'];
        }

        $_SESSION['user'] = $user;

        return ['success' => true, 'redirect' => '/index.php'];
    }
}
