<?php
declare(strict_types=1);

require_once __DIR__ . '/../Core/config.php';

use Kreait\Firebase\Factory;

class FirebaseAuthService
{
    private \Kreait\Firebase\Auth $auth;

    public function __construct()
    {
        $config = config('firebase');
        $serviceAccountPath = $config['service_account_path'] ?? null;
        $projectId = $config['project_id'] ?? null;

        if (!$serviceAccountPath || !file_exists($serviceAccountPath)) {
            throw new RuntimeException('Firebase service account configuration is missing.');
        }

        if (!$projectId) {
            throw new RuntimeException('Firebase project configuration is missing.');
        }

        if (!class_exists(Factory::class)) {
            $autoload = __DIR__ . '/../../vendor/autoload.php';
            if (file_exists($autoload)) {
                require_once $autoload;
            }
        }

        if (!class_exists(Factory::class)) {
            throw new RuntimeException('Firebase Admin SDK is not available.');
        }

        $factory = (new Factory())
            ->withServiceAccount($serviceAccountPath)
            ->withProjectId($projectId);

        $this->auth = $factory->createAuth();
    }

    public function verifyIdToken(string $idToken): array
    {
        if (trim($idToken) === '') {
            throw new RuntimeException('Missing Firebase ID token.');
        }

        try {
            $verifiedToken = $this->auth->verifyIdToken($idToken);
        } catch (Throwable $e) {
            error_log('Firebase token verification failed: ' . $e->getMessage());
            throw new RuntimeException('Invalid or expired Firebase token.');
        }

        $claims = $verifiedToken->claims();
        $firebaseData = (array) $claims->get('firebase', []);
        $signInProvider = (string) ($firebaseData['sign_in_provider'] ?? 'password');

        $provider = $signInProvider === 'google.com' ? 'google' : 'email';
        $email = $claims->get('email');

        if (!$email) {
            throw new RuntimeException('Email address is missing in Firebase token.');
        }

        return [
            'uid' => (string) $claims->get('sub'),
            'email' => (string) $email,
            'name' => (string) ($claims->get('name') ?? ''),
            'provider' => $provider,
        ];
    }
}
