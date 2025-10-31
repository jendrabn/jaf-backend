<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class FirebaseService
{
    /**
     * Get OAuth 2.0 access token for Firebase service account
     */
    public function getAccessToken(): string
    {
        return Cache::remember('firebase_access_token', 3500, function () {
            $serviceAccount = config('firebase.service_account');

            $jwtHeader = $this->base64UrlEncode(json_encode([
                'alg' => 'RS256',
                'typ' => 'JWT'
            ]));

            $now = time();
            $jwtPayload = $this->base64UrlEncode(json_encode([
                'iss' => $serviceAccount['client_email'],
                'scope' => config('firebase.fcm.scope'),
                'aud' => $serviceAccount['token_uri'],
                'exp' => $now + 3600,
                'iat' => $now
            ]));

            $jwt = $jwtHeader . '.' . $jwtPayload;

            $privateKey = openssl_pkey_get_private($serviceAccount['private_key']);
            if (!$privateKey) {
                throw new \Exception('Invalid private key');
            }

            openssl_sign($jwt, $signature, $privateKey, OPENSSL_ALGO_SHA256);
            $jwtSignature = $this->base64UrlEncode($signature);
            $signedJwt = $jwt . '.' . $jwtSignature;

            $response = Http::asForm()->post($serviceAccount['token_uri'], [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $signedJwt
            ]);

            if (!$response->successful()) {
                throw new \Exception('Failed to get access token: ' . $response->body());
            }

            return $response->json('access_token');
        });
    }

    /**
     * Send push notification to specific device
     */
    public function sendNotification(string $token, array $notification, array $data = []): array
    {
        $accessToken = $this->getAccessToken();
        $projectId = config('firebase.project_id');
        $endpoint = str_replace('{project_id}', $projectId, config('firebase.fcm.endpoint'));

        $messagePayload = [
            'message' => [
                'token' => $token,
                'notification' => [
                    'title' => $notification['title'],
                    'body' => $notification['body'],
                ],
                'data' => $data,
                'android' => [
                    'priority' => 'high',
                    'notification' => [
                        'sound' => 'default',
                        'click_action' => $notification['click_action'] ?? 'FLUTTER_NOTIFICATION_CLICK',
                    ],
                ],
                'apns' => [
                    'payload' => [
                        'aps' => [
                            'sound' => 'default',
                            'badge' => 1,
                        ],
                    ],
                ],
            ],
        ];

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $accessToken,
        ])->post($endpoint, $messagePayload);

        return [
            'success' => $response->successful(),
            'data' => $response->json(),
            'status' => $response->status(),
        ];
    }

    /**
     * Send notification to multiple devices
     */
    public function sendMulticastNotification(array $tokens, array $notification, array $data = []): array
    {
        $results = [];
        $accessToken = $this->getAccessToken();
        $projectId = config('firebase.project_id');
        $endpoint = str_replace('{project_id}', $projectId, config('firebase.fcm.endpoint'));

        foreach ($tokens as $token) {
            $messagePayload = [
                'message' => [
                    'token' => $token,
                    'notification' => [
                        'title' => $notification['title'],
                        'body' => $notification['body'],
                    ],
                    'data' => $data,
                    'android' => [
                        'priority' => 'high',
                        'notification' => [
                            'sound' => 'default',
                            'click_action' => $notification['click_action'] ?? 'FLUTTER_NOTIFICATION_CLICK',
                        ],
                    ],
                    'apns' => [
                        'payload' => [
                            'aps' => [
                                'sound' => 'default',
                                'badge' => 1,
                            ],
                        ],
                    ],
                ],
            ];

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $accessToken,
            ])->post($endpoint, $messagePayload);

            $results[] = [
                'token' => $token,
                'success' => $response->successful(),
                'data' => $response->json(),
                'status' => $response->status(),
            ];
        }

        return $results;
    }

    /**
     * Base64 URL encode
     */
    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Clear access token cache
     */
    public function clearAccessTokenCache(): void
    {
        Cache::forget('firebase_access_token');
    }
}
