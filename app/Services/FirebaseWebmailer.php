<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirebaseWebmailer
{
    protected ?string $clientApiKey;

    public function __construct()
    {
        $this->clientApiKey = env('FIREBASE_API_KEY');
    }

    /**
     * Mengirimkan email verifikasi ke Gmail pengguna.
     * 
     * @param string $idToken Token otentikasi aman dari akun yang baru terdaftar
     * @return bool True jika berhasil dikirim, false jika gagal
     */
    public function sendVerificationEmail(string $idToken): bool
    {
        if (!$this->clientApiKey) {
            Log::warning('FirebaseWebmailer: FIREBASE_API_KEY belum dikonfigurasi di .env');
            return false;
        }

        try {
            $url = "https://identitytoolkit.googleapis.com/v1/accounts:sendOobCode?key={$this->clientApiKey}";
            $response = Http::post($url, [
                'requestType' => 'VERIFY_EMAIL',
                'idToken' => $idToken,
            ]);

            if ($response->successful()) {
                return true;
            } else {
                Log::warning('FirebaseWebmailer error response: ' . $response->body());
            }
        } catch (\Throwable $e) {
            Log::warning('FirebaseWebmailer exception: ' . $e->getMessage());
        }

        return false;
    }
}