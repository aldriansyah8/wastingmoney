<?php

namespace App\Services;

use Google\Auth\Credentials\ServiceAccountCredentials;
use GuzzleHttp\Client;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Auth as FirebaseAuth;
use Kreait\Firebase\Database;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class FirebaseService
{
    protected ?FirebaseAuth $auth = null;
    protected ?Database $database = null;
    protected bool $isAvailable = false;
    protected ?string $clientApiKey = null;
    protected ?string $projectId = null;
    protected ?array $serviceAccount = null;
    protected ?string $serviceAccountPath = null;
    protected ?string $cachedOAuthToken = null;
    protected int $tokenExpiresAt = 0;

    public function __construct()
    {
        $this->clientApiKey = env('FIREBASE_API_KEY');
        $this->projectId = env('FIREBASE_PROJECT_ID', 'wastingmoney-1cd45');
        $this->initializeFirebase();
    }

    protected function initializeFirebase(): void
    {
        $credentialsPath = base_path('serviceAccountKey.json');

        if (!file_exists($credentialsPath)) {
            $customPath = env('FIREBASE_CREDENTIALS');
            if ($customPath && file_exists(base_path($customPath))) {
                $credentialsPath = base_path($customPath);
            }
        }

        if (file_exists($credentialsPath)) {
            try {
                $content = file_get_contents($credentialsPath);
                $json = json_decode($content, true);

                if (isset($json['private_key']) && !str_contains($json['private_key'], 'REPLACE_WITH_YOUR_ACTUAL')) {
                    $this->serviceAccount = $json;
                    $this->serviceAccountPath = $credentialsPath;
                    $this->projectId = $json['project_id'] ?? $this->projectId;

                    // Initialize Kreait SDK
                    $factory = (new Factory)->withServiceAccount($credentialsPath);

                    if (env('FIREBASE_DATABASE_URL')) {
                        $factory = $factory->withDatabaseUri(env('FIREBASE_DATABASE_URL'));
                    }

                    $this->auth = $factory->createAuth();
                    try {
                        $this->database = $factory->createDatabase();
                    } catch (\Throwable $e) {
                        // Realtime DB optional
                    }

                    $this->isAvailable = true;
                    Log::info('Firebase Admin SDK initialized with project: ' . $this->projectId);
                    return;
                }
            } catch (\Throwable $e) {
                Log::warning('Firebase Admin SDK initialization: ' . $e->getMessage());
            }
        }

        if ($this->clientApiKey) {
            $this->isAvailable = true;
            Log::info('Firebase Client Web API Key mode active.');
        } else {
            Log::info('Firebase service account initialized in local fallback mode.');
        }
    }

    public function isConnected(): bool
    {
        return $this->isAvailable;
    }

    /**
     * Dapatkan Bearer Access Token OAuth2 dari Google IAM
     */
    public function getOAuthAccessToken(): ?string
    {
        if ($this->cachedOAuthToken && time() < ($this->tokenExpiresAt - 60)) {
            return $this->cachedOAuthToken;
        }

        if (!$this->serviceAccount) {
            return null;
        }

        try {
            $scopes = [
                'https://www.googleapis.com/auth/cloud-platform',
                'https://www.googleapis.com/auth/datastore',
                'https://www.googleapis.com/auth/firebase.database',
                'https://www.googleapis.com/auth/identitytoolkit'
            ];

            $creds = new ServiceAccountCredentials($scopes, $this->serviceAccount);
            $tokenData = $creds->fetchAuthToken();

            if (!empty($tokenData['access_token'])) {
                $this->cachedOAuthToken = $tokenData['access_token'];
                $this->tokenExpiresAt = time() + ($tokenData['expires_in'] ?? 3600);
                return $this->cachedOAuthToken;
            }
        } catch (\Throwable $e) {
            Log::warning('Failed generating Google OAuth token: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Daftarkan user ke Firebase Auth
     */
    public function createAuthUser(string $email, string $password, string $displayName): ?string
    {
        // 1. Coba via Firebase Admin SDK
        if ($this->auth) {
            try {
                $createdUser = $this->auth->createUser([
                    'email' => $email,
                    'emailVerified' => false,
                    'password' => $password,
                    'displayName' => $displayName,
                    'disabled' => false,
                ]);
                return $createdUser->uid;
            } catch (\Throwable $e) {
                Log::warning('Firebase Admin Auth createUser error: ' . $e->getMessage());
            }
        }

        // 2. Coba via Firebase Client REST API
        if ($this->clientApiKey) {
            try {
                $url = "https://identitytoolkit.googleapis.com/v1/accounts:signUp?key={$this->clientApiKey}";
                $response = Http::post($url, [
                    'email' => $email,
                    'password' => $password,
                    'returnSecureToken' => true,
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    return $data['localId'] ?? null;
                }
            } catch (\Throwable $e) {
                Log::warning('Firebase Client REST Auth error: ' . $e->getMessage());
            }
        }

        return 'fb_' . substr(md5($email . time()), 0, 16);
    }

    /**
     * Verifikasi Login ke Firebase Auth
     */
    public function verifyClientLogin(string $email, string $password): ?array
    {
        if ($this->clientApiKey) {
            try {
                $url = "https://identitytoolkit.googleapis.com/v1/accounts:signInWithPassword?key={$this->clientApiKey}";
                $response = Http::post($url, [
                    'email' => $email,
                    'password' => $password,
                    'returnSecureToken' => true,
                ]);

                if ($response->successful()) {
                    return $response->json();
                }
            } catch (\Throwable $e) {
                Log::warning('Firebase verifyClientLogin error: ' . $e->getMessage());
            }
        }
        return null;
    }

    /**
     * Convert PHP primitive array to Firestore Field Map format
     */
    protected function toFirestoreFields(array $data): array
    {
        $fields = [];
        foreach ($data as $key => $value) {
            if (is_null($value)) {
                $fields[$key] = ['nullValue' => null];
            } elseif (is_bool($value)) {
                $fields[$key] = ['booleanValue' => $value];
            } elseif (is_int($value)) {
                $fields[$key] = ['integerValue' => (string)$value];
            } elseif (is_float($value)) {
                $fields[$key] = ['doubleValue' => $value];
            } elseif (is_array($value)) {
                $fields[$key] = ['stringValue' => json_encode($value)];
            } else {
                $fields[$key] = ['stringValue' => (string)$value];
            }
        }
        return $fields;
    }

    /**
     * Sync data pengajuan ke Cloud Firestore & Realtime Database
     */
    public function syncSubmission(array $data): void
    {
        $id = $data['id'] ?? time();

        // 1. Sync ke Cloud Firestore via REST
        $token = $this->getOAuthAccessToken();
        if ($token && $this->projectId) {
            try {
                $url = "https://firestore.googleapis.com/v1/projects/{$this->projectId}/databases/(default)/documents/submissions/{$id}";
                $client = new Client(['http_errors' => false]);
                $client->patch($url, [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $token,
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ],
                    'json' => [
                        'fields' => $this->toFirestoreFields($data)
                    ]
                ]);
            } catch (\Throwable $e) {
                Log::warning('Firestore REST syncSubmission: ' . $e->getMessage());
            }
        }

        // 2. Sync ke Realtime Database (jika diaktifkan)
        if ($this->database) {
            try {
                $this->database->getReference('submissions/' . $id)->set($data);
            } catch (\Throwable $e) {
                // RTDB fallback
            }
        }
    }

    /**
     * Sync item approved (katalog) ke Firestore
     */
    public function syncItem(array $data): void
    {
        $slug = $data['slug'] ?? ('item_' . time());
        $token = $this->getOAuthAccessToken();
        if ($token && $this->projectId) {
            try {
                $url = "https://firestore.googleapis.com/v1/projects/{$this->projectId}/databases/(default)/documents/items/{$slug}";
                $client = new Client(['http_errors' => false]);
                $client->patch($url, [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $token,
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ],
                    'json' => [
                        'fields' => $this->toFirestoreFields($data)
                    ]
                ]);
            } catch (\Throwable $e) {
                Log::warning('Firestore REST syncItem: ' . $e->getMessage());
            }
        }
    }

    /**
     * Kirim notifikasi / alert admin ke Firebase
     */
    public function sendAdminAlert(string $title, string $message, array $payload = []): void
    {
        if ($this->database) {
            try {
                $this->database->getReference('admin_alerts/' . time())->set([
                    'title' => $title,
                    'message' => $message,
                    'payload' => $payload,
                    'timestamp' => now()->toIso8601String(),
                ]);
            } catch (\Throwable $e) {
                // Realtime DB fallback
            }
        }
    }

    /**
     * Diagnostics method: Memeriksa status koneksi Firebase secara lengkap
     */
    public function testConnection(): array
    {
        $result = [
            'has_service_account' => false,
            'project_id' => $this->projectId,
            'client_email' => null,
            'oauth_token_generated' => false,
            'oauth_token_preview' => null,
            'google_cloud_status' => null,
            'firestore_status' => null,
            'auth_status' => null,
            'recommendation' => null,
        ];

        if ($this->serviceAccount) {
            $result['has_service_account'] = true;
            $result['client_email'] = $this->serviceAccount['client_email'] ?? null;

            $token = $this->getOAuthAccessToken();
            if ($token) {
                $result['oauth_token_generated'] = true;
                $result['oauth_token_preview'] = substr($token, 0, 20) . '...';

                $client = new Client(['http_errors' => false]);
                $headers = [
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json'
                ];

                // 1. Check Google Cloud Project Metadata
                try {
                    $res = $client->get("https://cloudresourcemanager.googleapis.com/v1/projects/{$this->projectId}", ['headers' => $headers]);
                    $result['google_cloud_status'] = [
                        'http_code' => $res->getStatusCode(),
                        'data' => json_decode((string)$res->getBody(), true)
                    ];
                } catch (\Throwable $e) {
                    $result['google_cloud_status'] = ['error' => $e->getMessage()];
                }

                // 2. Check Cloud Firestore
                try {
                    $fsRes = $client->patch(
                        "https://firestore.googleapis.com/v1/projects/{$this->projectId}/databases/(default)/documents/system/ping",
                        [
                            'headers' => array_merge($headers, ['Content-Type' => 'application/json']),
                            'json' => [
                                'fields' => [
                                    'ping' => ['stringValue' => 'pong'],
                                    'timestamp' => ['stringValue' => now()->toIso8601String()]
                                ]
                            ]
                        ]
                    );
                    $fsBody = json_decode((string)$fsRes->getBody(), true);
                    $result['firestore_status'] = [
                        'http_code' => $fsRes->getStatusCode(),
                        'body' => $fsBody
                    ];
                } catch (\Throwable $e) {
                    $result['firestore_status'] = ['error' => $e->getMessage()];
                }

                // 3. Check Auth Status
                try {
                    if ($this->auth) {
                        $users = $this->auth->listUsers(1);
                        $result['auth_status'] = [
                            'active' => true,
                            'message' => 'Firebase Auth is active.'
                        ];
                    }
                } catch (\Throwable $e) {
                    $result['auth_status'] = [
                        'active' => false,
                        'message' => $e->getMessage()
                    ];
                }
            }
        }

        return $result;
    }
}
