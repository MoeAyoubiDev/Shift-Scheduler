<?php
declare(strict_types=1);

require_once __DIR__ . '/../Core/config.php';

class FirebaseNotificationService
{
    private string $projectId;
    private string $serviceAccountPath;
    private ?string $accessToken = null;
    private int $accessTokenExpiresAt = 0;

    public function __construct()
    {
        $config = config('firebase');
        $this->projectId = $config['project_id'] ?? '';
        $this->serviceAccountPath = $config['service_account_path'] ?? '';
    }

    public function sendToTokens(array $tokens, string $title, string $body, array $data = []): array
    {
        $tokens = array_values(array_filter($tokens));
        if (empty($tokens)) {
            return ['sent' => 0, 'errors' => []];
        }

        try {
            $accessToken = $this->getAccessToken();
        } catch (Exception $e) {
            error_log('FCM auth error: ' . $e->getMessage());
            return ['sent' => 0, 'errors' => [$e->getMessage()]];
        }

        $sent = 0;
        $errors = [];

        foreach ($tokens as $token) {
            try {
                $response = $this->sendMessage($accessToken, $token, $title, $body, $data);
                if ($response['success']) {
                    $sent++;
                } else {
                    $errors[] = $response['error'] ?? 'Unknown FCM error';
                }
            } catch (Exception $e) {
                $errors[] = $e->getMessage();
            }
        }

        if (!empty($errors)) {
            error_log('FCM send errors: ' . implode(' | ', $errors));
        }

        return ['sent' => $sent, 'errors' => $errors];
    }

    private function sendMessage(string $accessToken, string $token, string $title, string $body, array $data = []): array
    {
        $url = sprintf('https://fcm.googleapis.com/v1/projects/%s/messages:send', $this->projectId);

        $payload = [
            'message' => [
                'token' => $token,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'data' => $data,
                'webpush' => [
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                ],
            ],
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: application/json; charset=utf-8',
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT => 10,
        ]);

        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false || $httpCode >= 400) {
            $error = $curlError ?: $response ?: 'FCM request failed';
            return ['success' => false, 'error' => $error];
        }

        return ['success' => true];
    }

    private function getAccessToken(): string
    {
        $now = time();
        if ($this->accessToken && $this->accessTokenExpiresAt > ($now + 60)) {
            return $this->accessToken;
        }

        $serviceAccount = $this->loadServiceAccount();
        $jwt = $this->buildJwt($serviceAccount);

        $tokenResponse = $this->requestAccessToken($jwt);
        $this->accessToken = $tokenResponse['access_token'] ?? null;
        $expiresIn = (int) ($tokenResponse['expires_in'] ?? 0);
        $this->accessTokenExpiresAt = $now + $expiresIn;

        if (!$this->accessToken) {
            throw new RuntimeException('Failed to obtain Firebase access token.');
        }

        return $this->accessToken;
    }

    private function loadServiceAccount(): array
    {
        if (!$this->serviceAccountPath || !file_exists($this->serviceAccountPath)) {
            throw new RuntimeException('Firebase service account JSON not found.');
        }

        $contents = file_get_contents($this->serviceAccountPath);
        if ($contents === false) {
            throw new RuntimeException('Unable to read Firebase service account JSON.');
        }

        $decoded = json_decode($contents, true);
        if (!is_array($decoded)) {
            throw new RuntimeException('Invalid Firebase service account JSON.');
        }

        return $decoded;
    }

    private function buildJwt(array $serviceAccount): string
    {
        $issuedAt = time();
        $expiresAt = $issuedAt + 3600;

        $header = [
            'alg' => 'RS256',
            'typ' => 'JWT',
        ];

        $payload = [
            'iss' => $serviceAccount['client_email'] ?? '',
            'sub' => $serviceAccount['client_email'] ?? '',
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $issuedAt,
            'exp' => $expiresAt,
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
        ];

        $segments = [
            $this->base64UrlEncode(json_encode($header)),
            $this->base64UrlEncode(json_encode($payload)),
        ];

        $signingInput = implode('.', $segments);
        $signature = '';
        $privateKey = openssl_pkey_get_private($serviceAccount['private_key'] ?? '');
        if (!$privateKey) {
            throw new RuntimeException('Invalid Firebase private key.');
        }

        $signed = openssl_sign($signingInput, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        openssl_pkey_free($privateKey);

        if (!$signed) {
            throw new RuntimeException('Failed to sign Firebase JWT.');
        }

        $segments[] = $this->base64UrlEncode($signature);
        return implode('.', $segments);
    }

    private function requestAccessToken(string $jwt): array
    {
        $ch = curl_init('https://oauth2.googleapis.com/token');
        $body = http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt,
        ]);

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_TIMEOUT => 10,
        ]);

        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false || $httpCode >= 400) {
            $error = $curlError ?: $response ?: 'Failed to fetch Firebase token.';
            throw new RuntimeException($error);
        }

        $decoded = json_decode($response, true);
        if (!is_array($decoded)) {
            throw new RuntimeException('Invalid Firebase token response.');
        }

        return $decoded;
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
