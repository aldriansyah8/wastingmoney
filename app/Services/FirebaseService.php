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

    /**
     * Constructor FirebaseService.
     * Mengambil konfigurasi dari .env (FIREBASE_API_KEY, FIREBASE_PROJECT_ID)
     * dan menginisialisasi service account atau client mode.
     */
    public function __construct()
    {
        $this->clientApiKey = env('FIREBASE_API_KEY');
        $this->projectId = env('FIREBASE_PROJECT_ID', 'wastingmoney-1cd45');
        $this->initializeFirebase();
    }

    /**
     * Menginisialisasi koneksi Firebase.
     * Prioritas:
     * 1. Service Account JSON (Admin SDK / OAuth2 IAM Bearer token untuk full access Firestore & Auth).
     * 2. Web Client REST API Key (fallback mode).
     * 3. Local fallback mode jika credentials belum diisi.
     */
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

                    // Inisialisasi Kreait Firebase SDK Factory
                    $factory = (new Factory)->withServiceAccount($credentialsPath);

                    if (env('FIREBASE_DATABASE_URL')) {
                        $factory = $factory->withDatabaseUri(env('FIREBASE_DATABASE_URL'));
                    }

                    $this->auth = $factory->createAuth();
                    try {
                        $this->database = $factory->createDatabase();
                    } catch (\Throwable $e) {
                        // Realtime DB opsional
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

    /**
     * Memeriksa apakah integrasi Firebase siap digunakan (active).
     * 
     * @return bool True jika Firebase aktif atau client key tersedia, false jika tidak
     */
    public function isConnected(): bool
    {
        return $this->isAvailable;
    }

    /**
     * Menghasilkan Bearer Access Token OAuth2 dari Google IAM Service Account.
     * Digunakan untuk otentikasi request REST API ke Cloud Firestore & Google APIs.
     * Token di-cache secara otomatis selama valid (biasanya 1 jam).
     * 
     * @return string|null Token string atau null jika credentials tidak valid
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
     * Mendaftarkan pengguna baru (user) ke sistem Firebase Authentication.
     * Mencoba melalui Firebase Admin SDK terlebih dahulu, lalu fallback ke REST API.
     * 
     * @param string $email Email pengguna
     * @param string $password Password pengguna
     * @param string $displayName Nama lengkap atau username pengguna
     * @return string|null Firebase UID (User ID) yang berhasil dibuat
     */
    public function createAuthUser(string $email, string $password, string $displayName): ?string
    {
        // 1. Coba daftarkan via Firebase Admin SDK
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

        // 2. Fallback via Firebase Identity Toolkit Client REST API
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

        // Fallback ID unik lokal
        return 'fb_' . substr(md5($email . time()), 0, 16);
    }

    /**
     * Melakukan verifikasi kredensial login pengguna langsung ke Firebase Auth REST API.
     * 
     * @param string $email Email pengguna
     * @param string $password Password pengguna
     * @return array|null Response data dari Firebase (termasuk idToken dan localId) atau null jika gagal
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
     * Mengkonversi array PHP standar menjadi format skema Typed Value Firestore.
     * Contoh: ['title' => 'Baju'] menjadi ['title' => ['stringValue' => 'Baju']]
     * 
     * @param array $data Data array asosiatif PHP
     * @return array Format fields dokumen Firestore REST API
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
                if (array_is_list($value)) {
                    $itemValues = [];
                    foreach ($value as $item) {
                        $itemValues[] = is_string($item) ? ['stringValue' => (string)$item] : ['stringValue' => json_encode($item)];
                    }
                    $fields[$key] = ['arrayValue' => ['values' => $itemValues]];
                } else {
                    $fields[$key] = ['stringValue' => json_encode($value)];
                }
            } else {
                $fields[$key] = ['stringValue' => (string)$value];
            }
        }
        return $fields;
    }

    /**
     * Menyinkronkan data profil pengguna (User) ke Cloud Firestore (koleksi 'users/{id}').
     * 
     * @param array $data Data pengguna (id, name, email, full_name, address, phone_number, postal_code, role, dll.)
     * @return void
     */
    public function syncUser(array $data): void
    {
        $id = $data['id'] ?? $data['firebase_uid'] ?? time();
        $token = $this->getOAuthAccessToken();

        // Hindari menyimpan password hash ke Firestore demi privasi dan keamanan
        unset($data['password'], $data['remember_token']);
        $data['updated_at'] = now()->toIso8601String();

        if ($token && $this->projectId) {
            try {
                $url = "https://firestore.googleapis.com/v1/projects/{$this->projectId}/databases/(default)/documents/users/{$id}";
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
                Log::warning('Firestore REST syncUser: ' . $e->getMessage());
            }
        }
    }

    /**
     * Menyinkronkan data pengajuan pembelian belanja (submission) ke Cloud Firestore dan Realtime Database.
     * Menggunakan format 7 digit Request ID (contoh: '0000021') sebagai Document ID Firestore
     * sehingga sama persis dengan yang ditampilkan pada halaman User dan Admin.
     * 
     * Menjamin foto yang tersimpan di Firebase (image, image_url, display_image, user_image, admin_images)
     * sama persis dengan gambar yang aktif tampil di website pengguna dan admin.
     * 
     * @param array $data Data pengajuan (id, title, user_id, price, status, dll.)
     * @return void
     */
    public function syncSubmission(array $data): void
    {
        $numericId = $data['id'] ?? time();
        $formattedRequestId = !empty($data['request_id']) 
            ? str_pad(preg_replace('/[^0-9]/', '', (string)$data['request_id']), 7, '0', STR_PAD_LEFT)
            : str_pad((int)$numericId, 7, '0', STR_PAD_LEFT);

        // Ambil additional_data jika ada
        $extra = [];
        if (!empty($data['additional_data'])) {
            $extra = is_array($data['additional_data']) 
                ? $data['additional_data'] 
                : (json_decode($data['additional_data'], true) ?: []);
        }

        // Tentukan foto referensi user dan foto temuan admin
        $userImage = $data['user_image'] ?? ($data['image_url'] ?? '');
        $adminImages = $data['admin_images'] ?? ($extra['admin_images'] ?? ($extra['images'] ?? []));
        if (!is_array($adminImages)) {
            $adminImages = [];
        }

        // Foto utama yang tampil di website (jika admin sudah upload foto temuan, prioritaskan foto admin)
        $firstAdminImage = (!empty($adminImages) && count($adminImages) > 0) ? $adminImages[0] : '';
        $activeDisplayImage = $firstAdminImage ?: $userImage;

        // Pastikan field request_id dan requestId konsisten di dalam payload data
        $data['id'] = (int)$numericId;
        $data['request_id'] = $formattedRequestId;
        $data['requestId'] = '#' . $formattedRequestId;

        // Sinkronkan gambar agar 100% konsisten dengan website
        $data['image'] = $activeDisplayImage;          // Gambar aktif yang tampil di website
        $data['display_image'] = $activeDisplayImage;  // Alias gambar utama website
        $data['image_url'] = $activeDisplayImage;      // Diselaraskan dengan gambar website
        $data['user_image'] = $userImage;              // Foto referensi asli dari user
        $data['admin_image'] = $firstAdminImage;       // Foto temuan admin (jika ada)
        $data['admin_images'] = $adminImages;          // Daftar semua foto temuan admin

        // 1. Sync ke Cloud Firestore via Google REST API dengan Document ID = 7 digit Request ID
        $token = $this->getOAuthAccessToken();
        if ($token && $this->projectId) {
            try {
                $url = "https://firestore.googleapis.com/v1/projects/{$this->projectId}/databases/(default)/documents/submissions/{$formattedRequestId}";
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

                // Bersihkan dokumen lama berformat integer murni (misal: '21') agar tidak berbeda di Firebase
                if ((string)$numericId !== (string)$formattedRequestId) {
                    $oldUrl = "https://firestore.googleapis.com/v1/projects/{$this->projectId}/databases/(default)/documents/submissions/{$numericId}";
                    $client->delete($oldUrl, [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $token,
                            'Accept' => 'application/json',
                        ]
                    ]);
                }
            } catch (\Throwable $e) {
                Log::warning('Firestore REST syncSubmission: ' . $e->getMessage());
            }
        }

        // 2. Sync ke Realtime Database (jika diaktifkan)
        if ($this->database) {
            try {
                $this->database->getReference('submissions/' . $formattedRequestId)->set($data);
                if ((string)$numericId !== (string)$formattedRequestId) {
                    $this->database->getReference('submissions/' . $numericId)->remove();
                }
            } catch (\Throwable $e) {
                // Realtime DB fallback
            }
        }
    }

    /**
     * Menghapus data pengajuan formulir request (submission) dari Cloud Firestore dan Realtime Database.
     * Dipanggil ketika Administrator atau Superadmin menghapus request pengajuan dari sistem.
     * 
     * @param int|string $id ID unik pengajuan formulir yang dihapus
     * @return void
     */
    public function deleteSubmission($id): void
    {
        $formattedRequestId = str_pad(preg_replace('/[^0-9]/', '', (string)$id), 7, '0', STR_PAD_LEFT);

        // 1. Hapus dokumen dari Cloud Firestore via Google REST API
        $token = $this->getOAuthAccessToken();
        if ($token && $this->projectId) {
            try {
                $client = new Client(['http_errors' => false]);
                $headers = [
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json',
                ];

                // Hapus dokumen 7 digit Request ID dan dokumen integer lama jika ada
                foreach (array_unique([$formattedRequestId, (string)$id]) as $docKey) {
                    $url = "https://firestore.googleapis.com/v1/projects/{$this->projectId}/databases/(default)/documents/submissions/{$docKey}";
                    $client->delete($url, ['headers' => $headers]);
                }
            } catch (\Throwable $e) {
                Log::warning('Firestore REST deleteSubmission error: ' . $e->getMessage());
            }
        }

        // 2. Hapus referensi data dari Realtime Database (jika diaktifkan)
        if ($this->database) {
            try {
                $this->database->getReference('submissions/' . $formattedRequestId)->remove();
                $this->database->getReference('submissions/' . $id)->remove();
            } catch (\Throwable $e) {
                // Realtime DB fallback
            }
        }
    }

    /**
     * Menyinkronkan item katalog yang telah disetujui (approved item) ke Cloud Firestore.
     * 
     * @param array $data Data item katalog (slug, title, price, category, views, dll.)
     * @return void
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
     * Mengirimkan notifikasi atau peringatan admin (admin alert) ke Firebase Realtime Database.
     * 
     * @param string $title Judul notifikasi
     * @param string $message Isi pesan notifikasi
     * @param array $payload Data payload tambahan opsional
     * @return void
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
     * Method Diagnostik: Memeriksa dan menguji kesehatan koneksi Firebase secara menyeluruh.
     * Mengetes IAM Service Account, Google OAuth2 token, Google Cloud Metadata, Firestore ping, dan Firebase Auth.
     * 
     * @return array Hasil pengujian diagnostik lengkap
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
