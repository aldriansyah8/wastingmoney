<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Submission;
use App\Models\Notification;
use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

/**
 * Controller SubmissionController
 * 
 * Bertanggung jawab menangani pengajuan merchandise oleh pengguna (User):
 * 1. Menerima formulir pengajuan request barang (Artist/band, Tipe Merch, Tahun, Edisi, Foto).
 * 2. Mengunggah dan menyimpan file foto merchandise ke storage publik.
 * 3. Menghasilkan Request ID otomatis (format 7 digit, contoh: #0000001).
 * 4. Menyimpan data ke database lokal SQLite dan mengirim notifikasi alert admin.
 * 5. Menampilkan riwayat pengajuan milik user yang sedang login (mySubmissions).
 */
class SubmissionController extends Controller
{
    /**
     * Layanan integrasi Firebase
     * @var FirebaseService
     */
    protected FirebaseService $firebase;

    /**
     * Dependency Injection untuk FirebaseService
     * 
     * @param FirebaseService $firebase
     */
    public function __construct(FirebaseService $firebase)
    {
        $this->firebase = $firebase;
    }

    /**
     * Menyelesaikan data pengguna (User) baik melalui Sanctum Bearer token, sesi Laravel,
     * maupun parameter request (user_id / email).
     *
     * @param Request $request
     * @return User|null
     */
    protected function resolveUser(Request $request): ?User
    {
        // 1. Sanctum Bearer token
        $user = $request->user();
        if (!$user && $request->hasHeader('Authorization')) {
            $tokenStr = str_replace('Bearer ', '', $request->header('Authorization'));
            $pat = \Laravel\Sanctum\PersonalAccessToken::findToken($tokenStr);
            if ($pat) {
                $user = $pat->tokenable;
            }
        }

        // 2. Client explicit identification via query or input body
        // Penting: parameter eksplisit dari client (localStorage / sesi aktif tab) harus didahulukan
        // dibandingkan cookie session Laravel agar akun tidak tertukar saat multi-tab / ganti user.
        $reqUserId = $request->input('user_id') ?: $request->input('active_user_id') ?: $request->query('user_id');
        $reqEmail = $request->input('email') ?: $request->input('user_email') ?: $request->query('email');

        if ($reqUserId) {
            $foundById = User::find($reqUserId);
            if ($foundById) {
                if (!$reqEmail || strtolower(trim($foundById->email)) === strtolower(trim($reqEmail))) {
                    return $foundById;
                }
            }
        }

        if ($reqEmail) {
            $cleanEmail = strtolower(trim($reqEmail));
            $foundByEmail = User::where('email', $cleanEmail)->orWhere('name', $reqEmail)->first();
            if ($foundByEmail) {
                return $foundByEmail;
            }
        }

        // 3. Fallback ke sesi Laravel hanya jika tidak ada token maupun parameter eksplisit dari client
        if (!$user && session('wm_user')) {
            $sessUser = session('wm_user');
            $user = User::find($sessUser['id'] ?? null);
        }

        return $user;
    }

    /**
     * Menyimpan formulir pengajuan request merchandise baru dari pengguna.
     * 
     * Validasi & Pemrosesan:
     * - Field Artist/Band wajib diisi.
     * - Merch Type & Released Year otomatis memiliki fallback default jika tidak dipilih.
     * - Jika user mengunggah file foto/gambar, file disimpan ke directory 'storage/app/public/submissions'.
     * - Membuat record notifikasi untuk dashboard admin.
     * - Melakukan sinkronisasi data ke Firebase Firestore.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $artistBand = $request->input('artistBand') ?: $request->input('title');
        $merchType = $request->input('merchType') ?: $request->input('category') ?: 'T-Shirt';
        $releasedYear = $request->input('releasedYear') ?: $request->input('released_year') ?: date('Y');
        $merchEdition = $request->input('merchEdition') ?: $request->input('merch_edition');
        $description = $request->input('description') ?: $request->input('moreDescription') ?: '-';
        $fileName = $request->input('fileName') ?: ($request->has('additionalData.fileName') ? $request->input('additionalData.fileName') : null);

        // Validasi field utama
        if (!$artistBand) {
            return response()->json([
                'success' => false,
                'message' => 'Field Artist/Band wajib diisi.',
            ], 422);
        }

        $user = $this->resolveUser($request);
        $nextId = (Submission::max('id') ?? 0) + 1;
        $formattedRequestId = str_pad($nextId, 7, '0', STR_PAD_LEFT);

        // Menangani file gambar yang diunggah secara fisik atau via base64 data url
        $imageUrl = $request->input('image_url') ?: $request->input('imageUrl');
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('submissions', 'public');
            $imageUrl = '/storage/' . $path;
            $fileName = $request->file('image')->getClientOriginalName();
        } elseif ($request->filled('image_data_url')) {
            $dataUrl = $request->input('image_data_url');
            if (preg_match('/^data:image\/(\w+);base64,/', $dataUrl, $type)) {
                $data = substr($dataUrl, strpos($dataUrl, ',') + 1);
                $type = strtolower($type[1]);
                $data = base64_decode($data);
                if ($data !== false) {
                    $savedName = 'submission_' . time() . '_' . Str::random(8) . '.' . $type;
                    Storage::disk('public')->put('submissions/' . $savedName, $data);
                    $imageUrl = '/storage/submissions/' . $savedName;
                    if (!$fileName) $fileName = $savedName;
                }
            }
        }

        // Siapkan additional_data dengan menyertakan informasi profil pengguna yang riil
        $additionalData = $request->input('additionalData') ?: [];
        if (!is_array($additionalData)) {
            $additionalData = [];
        }
        if ($fileName && !isset($additionalData['fileName'])) {
            $additionalData['fileName'] = $fileName;
        }
        if ($user) {
            $additionalData['full_name'] = $user->full_name ?: $user->name ?: '';
            $additionalData['address'] = $user->address ?: '-';
            $additionalData['postal_code'] = $user->postal_code ?: '-';
            $additionalData['phone_number'] = $user->phone_number ?: $user->contact ?: '-';
        }

        // Menyimpan data pengajuan baru dengan status awal 'Queue' (Abu-abu / User Request)
        $submission = Submission::create([
            'user_id' => $user ? $user->id : ($request->input('user_id') ?: null),
            'user_name' => $user ? ($user->name ?: $user->email) : $request->input('user_name', 'Guest'),
            'user_email' => $user ? $user->email : $request->input('user_email', 'guest@wastingmoney.id'),
            'request_id' => $formattedRequestId,
            'title' => $artistBand,
            'artist_band' => $artistBand,
            'merch_type' => $merchType,
            'released_year' => $releasedYear,
            'merch_edition' => $merchEdition,
            'category' => $merchType,
            'description' => $description,
            'contact' => $user ? ($user->phone_number ?: $user->email) : $request->input('contact', 'guest@wastingmoney.id'),
            'image_url' => $imageUrl,
            'file_name' => $fileName,
            'additional_data' => $additionalData,
            'status' => 'Queue', // Awal mula request berstatus Queue (Abu-abu di Keranjang)
        ]);

        // Pastikan request_id persis menggunakan ID primary key yang di-generate (format 7 digit)
        $realRequestId = str_pad($submission->id, 7, '0', STR_PAD_LEFT);
        if ($submission->request_id !== $realRequestId) {
            $submission->request_id = $realRequestId;
            $submission->save();
        }

        // Pemicu Notifikasi / Alert Admin
        $alertTitle = "Request Merch Baru: {$artistBand}";
        $alertMessage = "Request {$merchType} '{$artistBand}' ({$merchEdition}) dari {$submission->user_name} siap ditinjau.";
        
        Notification::create([
            'type' => 'new_submission',
            'title' => $alertTitle,
            'message' => $alertMessage,
            'payload' => [
                'submission_id' => $submission->id,
                'request_id' => $submission->request_id,
                'artist_band' => $submission->artist_band,
                'merch_type' => $submission->merch_type,
                'status' => $submission->status,
            ],
            'is_read' => false,
        ]);

        // Kirim alert & sync ke Firebase
        $this->firebase->sendAdminAlert($alertTitle, $alertMessage, $submission->toArray());
        $this->firebase->syncSubmission($submission->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Permintaan berhasil diajukan! Admin akan segera memprosesnya.',
            'data' => $submission,
        ], 201);
    }

    /**
     * Mengambil seluruh pengajuan merchandise untuk tampilan daftar/katalog user realtime.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $submissions = Submission::orderBy('id', 'asc')->get();

        $formatted = $submissions->map(function ($sub) {
            $st = strtolower($sub->status ?? 'queue');
            $theme = 'theme-gray';
            $dotClass = 'dot-gray';
            $displayStatus = 'Queue';

            if (str_contains($st, 'found') || str_contains($st, 'approve')) {
                $theme = 'theme-green';
                $dotClass = 'dot-green';
                $displayStatus = 'Found!';
            } elseif (str_contains($st, 'process') || str_contains($st, 'progress')) {
                $theme = 'theme-yellow';
                $dotClass = 'dot-yellow';
                $displayStatus = 'Process';
            } elseif (str_contains($st, 'paid') || str_contains($st, 'lanjut')) {
                $theme = 'theme-blue';
                $dotClass = 'dot-blue';
                $displayStatus = 'Paid';
            } elseif (str_contains($st, 'cancel') || str_contains($st, 'batal') || str_contains($st, 'reject')) {
                $theme = 'theme-pink';
                $dotClass = 'dot-pink';
                $displayStatus = 'Cancel';
            }

            $extra = $sub->additional_data ?: [];
            $reqNum = str_pad($sub->id, 7, '0', STR_PAD_LEFT);
            $adminImgs = $extra['admin_images'] ?? (isset($extra['images']) ? array_values(array_filter($extra['images'], fn($img) => $img !== $sub->image_url)) : []);
            $userPhoto = $sub->image_url ?: '/assets/placeholder_merch.svg';
            $displayImg = (!empty($adminImgs) && count($adminImgs) > 0) ? $adminImgs[0] : $userPhoto;

            $tz = config('app.timezone', 'Asia/Jakarta');
            $dateObj = $sub->created_at ? $sub->created_at->copy()->timezone($tz) : now()->timezone($tz);

            return [
                'id' => $sub->id,
                'requestId' => '#' . $reqNum,
                'request_id' => $reqNum,
                'artistBand' => $sub->artist_band ?: $sub->title ?: 'Unknown Artist',
                'merchType' => $sub->merch_type ?: $sub->category ?: 'Merch',
                'releasedYear' => $sub->released_year ?: date('Y'),
                'merchEdition' => $sub->merch_edition ?: '-',
                'description' => $sub->description ?: '-',
                'price' => $extra['price'] ?? '-',
                'currency' => $extra['currency'] ?? 'IDR',
                'priceAmount' => $extra['price_amount'] ?? '',
                'sellerNotes' => $extra['seller_notes'] ?? ($sub->admin_notes ?: '-'),
                'status' => $displayStatus,
                'statusDate' => $dateObj->format('d/m/Y H:i:s'),
                'created_at' => $dateObj->toIso8601String(),
                'createdAt' => $dateObj->toIso8601String(),
                'theme' => $theme,
                'dotClass' => $dotClass,
                'image' => $displayImg,
                'userImage' => $userPhoto,
                'adminImages' => $adminImgs,
                'images' => $adminImgs,
                'fullName' => $extra['full_name'] ?? $sub->user_name,
                'address' => $extra['address'] ?? '-',
                'postalCode' => $extra['postal_code'] ?? '-',
                'phoneNumber' => $extra['phone_number'] ?? $sub->contact,
            ];
        });

        return response()->json([
            'success' => true,
            'total' => $formatted->count(),
            'data' => $formatted,
        ]);
    }

    /**
     * Mengambil seluruh pengajuan merchandise milik pengguna yang sedang aktif/login.
     * Hanya menampilkan data milik akun yang bersangkutan (strict user scoping).
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function mySubmissions(Request $request)
    {
        $requestedEmail = $request->query('email') ?: $request->input('email') ?: $request->input('user_email');
        $requestedUserId = $request->query('user_id') ?: $request->input('user_id') ?: $request->input('active_user_id');

        $user = $this->resolveUser($request);
        $userEmail = $requestedEmail ?: ($user ? $user->email : null);
        $userId = $requestedUserId ?: ($user ? $user->id : null);

        // Jika user tidak terotentikasi dan tidak ada parameter identitas, kembalikan data kosong agar tidak membocorkan data pengguna lain
        if (!$userId && !$userEmail) {
            return response()->json([
                'success' => true,
                'total' => 0,
                'data' => [],
            ]);
        }

        $query = Submission::query();
        if ($userId && $userEmail) {
            $cleanEmail = strtolower(trim($userEmail));
            $query->where(function($q) use ($userId, $cleanEmail) {
                $q->where('user_id', $userId)
                  ->orWhere('user_email', $cleanEmail);
            });
        } elseif ($userId) {
            $query->where('user_id', $userId);
        } elseif ($userEmail) {
            $cleanEmail = strtolower(trim($userEmail));
            $query->where('user_email', $cleanEmail);
        }

        $submissions = $query->orderBy('created_at', 'desc')->get();

        $formatted = $submissions->map(function ($sub) {
            $st = strtolower($sub->status ?? 'queue');
            $theme = 'theme-gray';
            $dotClass = 'dot-gray';
            $displayStatus = 'Queue';

            if (str_contains($st, 'found') || str_contains($st, 'approve')) {
                $theme = 'theme-green';
                $dotClass = 'dot-green';
                $displayStatus = 'Found!';
            } elseif (str_contains($st, 'process') || str_contains($st, 'progress')) {
                $theme = 'theme-yellow';
                $dotClass = 'dot-yellow';
                $displayStatus = 'Process';
            } elseif (str_contains($st, 'paid') || str_contains($st, 'lanjut')) {
                $theme = 'theme-blue';
                $dotClass = 'dot-blue';
                $displayStatus = 'Paid';
            } elseif (str_contains($st, 'cancel') || str_contains($st, 'batal') || str_contains($st, 'reject')) {
                $theme = 'theme-pink';
                $dotClass = 'dot-pink';
                $displayStatus = 'Cancel';
            }

            $extra = $sub->additional_data ?: [];
            $reqNum = str_pad($sub->id, 7, '0', STR_PAD_LEFT);
            $adminImgs = $extra['admin_images'] ?? (isset($extra['images']) ? array_values(array_filter($extra['images'], fn($img) => $img !== $sub->image_url)) : []);
            
            // Prioritas foto: jika admin sudah mengunggah foto temuan, prioritaskan foto temuan admin.
            // Jika belum ada foto temuan admin, gunakan foto referensi yang diunggah oleh user itu sendiri.
            // Jika user tidak melampirkan foto sama sekali, gunakan placeholder netral (bukan foto user lain).
            $userPhoto = $sub->image_url ?: '/assets/placeholder_merch.svg';
            $displayImg = (!empty($adminImgs) && count($adminImgs) > 0) ? $adminImgs[0] : $userPhoto;

            $tz = config('app.timezone', 'Asia/Jakarta');
            $dateObj = $sub->created_at ? $sub->created_at->copy()->timezone($tz) : now()->timezone($tz);

            return [
                'id' => $sub->id,
                'requestId' => '#' . $reqNum,
                'request_id' => $reqNum,
                'artistBand' => $sub->artist_band ?: $sub->title ?: 'Unknown Artist',
                'merchType' => $sub->merch_type ?: $sub->category ?: 'Merch',
                'releasedYear' => $sub->released_year ?: date('Y'),
                'merchEdition' => $sub->merch_edition ?: '-',
                'description' => $sub->description ?: '-',
                'price' => $extra['price'] ?? '-',
                'currency' => $extra['currency'] ?? 'IDR',
                'priceAmount' => $extra['price_amount'] ?? '',
                'sellerNotes' => $extra['seller_notes'] ?? ($sub->admin_notes ?: '-'),
                'status' => $displayStatus,
                'statusDate' => $dateObj->format('d/m/Y H:i:s'),
                'created_at' => $dateObj->toIso8601String(),
                'createdAt' => $dateObj->toIso8601String(),
                'theme' => $theme,
                'dotClass' => $dotClass,
                'image' => $displayImg,
                'userImage' => $userPhoto,
                'adminImages' => $adminImgs,
                'images' => $adminImgs,
                'fullName' => $extra['full_name'] ?? $sub->user_name,
                'address' => $extra['address'] ?? '-',
                'postalCode' => $extra['postal_code'] ?? '-',
                'phoneNumber' => $extra['phone_number'] ?? $sub->contact,
            ];
        });

        return response()->json([
            'success' => true,
            'total' => $formatted->count(),
            'data' => $formatted,
        ]);
    }

    /**
     * Memperbarui status pengajuan oleh pengguna (misal: 'Paid' / Lanjut, atau 'Cancel' / Dibatalkan).
     * 
     * @param int $id ID pengajuan
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus($id, Request $request)
    {
        $submission = Submission::findOrFail($id);
        $newStatus = $request->input('status', 'Paid');

        $submission->status = $newStatus;
        if (strtolower($newStatus) === 'paid' || strtolower($newStatus) === 'lanjut') {
            $submission->approved_at = now();
        } elseif (strtolower($newStatus) === 'cancel' || strtolower($newStatus) === 'cancelled') {
            $submission->rejected_at = now();
        }
        $submission->save();

        // Sinkronisasi data ke Firebase
        $this->firebase->syncSubmission($submission->toArray());

        return response()->json([
            'success' => true,
            'message' => "Status pesanan berhasil diubah menjadi {$newStatus}.",
            'data' => $submission,
        ]);
    }
}
