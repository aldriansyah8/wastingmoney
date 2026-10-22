<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use App\Models\Item;
use App\Models\User;
use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Controller AdminSubmissionController
 * 
 * Bertanggung jawab menangani alur manajemen dan verifikasi pengajuan (requests)
 * merchandise oleh Administrator pada sistem Wasting Money:
 * 1. Menampilkan daftar request dengan opsi filter status.
 * 2. Memperbarui status (Pending, In Progress, Approved, Rejected).
 * 3. Menentukan mata uang dan nominal harga penawaran serta seller notes.
 * 4. Otomatis mempublikasikan barang yang disetujui ke katalog publik (Item).
 * 5. Melakukan sinkronisasi data ke Firebase Firestore / Realtime Database.
 */
class AdminSubmissionController extends Controller
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
     * Mengambil daftar seluruh pengajuan merchandise untuk dashboard Admin.
     * 
     * Fitur:
     * - Mendukung query filter status (contoh: ?status=Pending atau ?status=Approved)
     * - Mengurutkan data terbaru (created_at DESC)
     * - Memformat atribut secara konsisten untuk dikonsumsi antarmuka Admin
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = Submission::with('user');

        // Filter berdasarkan status jika parameter status disertakan
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', 'like', '%' . $request->status . '%');
        }

        $submissions = $query->orderBy('created_at', 'desc')->get();

        // Format data sesuai kebutuhan tampilan dashboard admin
        $formatted = $submissions->map(function ($sub) {
            $extra = $sub->additional_data ?: [];
            $userObj = $sub->user;
            $reqId = str_pad($sub->id, 7, '0', STR_PAD_LEFT);
            $tz = config('app.timezone', 'Asia/Jakarta');
            $dateObj = $sub->created_at ? $sub->created_at->copy()->timezone($tz) : now()->timezone($tz);

            return [
                'id' => $sub->id,
                'requestId' => $reqId,
                'request_id' => $reqId,
                'artistBand' => $sub->artist_band ?: $sub->title,
                'artist_band' => $sub->artist_band ?: $sub->title,
                'title' => $sub->title,
                'merchType' => $sub->merch_type ?: $sub->category ?: 'T-Shirt',
                'merch_type' => $sub->merch_type ?: $sub->category ?: 'T-Shirt',
                'category' => $sub->category,
                'releasedYear' => $sub->released_year ?: date('Y'),
                'released_year' => $sub->released_year ?: date('Y'),
                'merchEdition' => $sub->merch_edition ?: 'North American Tour',
                'merch_edition' => $sub->merch_edition ?: 'North American Tour',
                'description' => $sub->description,
                'contact' => $sub->contact,
                'fileName' => $sub->file_name,
                'imageUrl' => $sub->image_url,
                'image_url' => $sub->image_url,
                'userImage' => $sub->image_url,
                'adminImages' => $extra['admin_images'] ?? (isset($extra['images']) ? array_values(array_filter($extra['images'], fn($img) => $img !== $sub->image_url)) : []),
                'images' => $extra['admin_images'] ?? $extra['images'] ?? [],
                'status' => $sub->status,
                'adminNotes' => $sub->admin_notes,
                'admin_notes' => $sub->admin_notes,
                'price' => $extra['price'] ?? '-',
                'currency' => $extra['currency'] ?? 'IDR',
                'priceAmount' => $extra['price_amount'] ?? '',
                'sellerNotes' => $extra['seller_notes'] ?? ($sub->admin_notes ?: '-'),
                'fullName' => $extra['full_name'] ?? ($userObj ? ($userObj->full_name ?: $userObj->name) : null) ?? $sub->user_name ?? '-',
                'address' => $extra['address'] ?? ($userObj ? $userObj->address : null) ?? '-',
                'postalCode' => $extra['postal_code'] ?? ($userObj ? $userObj->postal_code : null) ?? '-',
                'phoneNumber' => $extra['phone_number'] ?? ($userObj ? $userObj->phone_number : null) ?? ($sub->contact ?: '-'),
                'foundAt' => $dateObj->format('d/m/Y H:i:s'),
                'submittedBy' => [
                    'name' => $sub->user_name ?: 'Guest',
                    'username' => $sub->user_name ?: 'guest',
                    'email' => $sub->user_email ?: 'guest@wastingmoney.id',
                ],
                'createdAt' => $dateObj->toIso8601String(),
                'created_at' => $dateObj->toIso8601String(),
            ];
        });

        return response()->json([
            'success' => true,
            'total' => $submissions->count(),
            'data' => $formatted,
        ]);
    }

    /**
     * Memperbarui status dan rincian penawaran (harga, catatan) suatu pengajuan.
     * 
     * Alur kerja:
     * 1. Mencari data submission berdasarkan ID.
     * 2. Menyimpan mata uang terpilih (IDR, USD, EUR, dsb.) dan nominal harga ke additional_data.
     * 3. Menyimpan catatan seller / admin.
     * 4. Jika status diubah menjadi 'Approved', otomatis membuat entitas katalog publik (Item).
     * 5. Melakukan sinkronisasi data terbaru ke Firebase.
     * 
     * @param int $id ID pengajuan
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus($id, Request $request)
    {
        $submission = Submission::findOrFail($id);

        // Jika pengajuan sudah berstatus 'Cancel' atau 'Paid', tolak pembaruan karena sudah terkunci permanen
        $currentDbStatus = strtolower(trim($submission->status ?? ''));
        if (in_array($currentDbStatus, ['cancel', 'cancelled', 'batal', 'rejected'])) {
            return response()->json([
                'success' => false,
                'message' => 'Pengajuan ini sudah dibatalkan (Cancel) dan terkunci, sehingga tidak dapat diedit kembali.',
            ], 422);
        }
        if ($currentDbStatus === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Pengajuan ini sudah Paid (Lunas) dan terkunci, sehingga tidak dapat diedit kembali.',
            ], 422);
        }

        $newStatus = $request->input('status', 'In Progress');
        $adminNotes = $request->input('adminNotes', $request->input('admin_notes', ''));
        $price = $request->input('price');
        $currency = $request->input('currency');
        $priceAmount = $request->input('priceAmount', $request->input('price_amount'));
        $sellerNotes = $request->input('sellerNotes', $request->input('seller_notes'));

        // Simpan data tambahan (harga komposit, mata uang, nominal terpisah, catatan penjual)
        $extra = $submission->additional_data ?: [];
        if ($price !== null) {
            $extra['price'] = $price !== '' ? $price : '-';
        }
        if ($currency !== null && $currency !== '') {
            $extra['currency'] = $currency;
        }
        if ($priceAmount !== null) {
            $extra['price_amount'] = $priceAmount;
        }
        if ($sellerNotes !== null) {
            $extra['seller_notes'] = $sellerNotes !== '' ? $sellerNotes : '-';
            $adminNotes = $sellerNotes;
        }

        // Handle daftar gambar yang ditambahkan oleh admin (Maksimal 3 Foto, tidak bisa didelete)
        // Aturan: Admin hanya bisa menambahkan gambar ketika posisi status barang sudah 'In Progress' atau lebih lanjut (bukan Queue)
        $statusCheck = strtolower($newStatus ?: $submission->status);
        $isBeforeInProgress = ($statusCheck === 'queue' || $statusCheck === 'pending');

        if (($request->has('admin_images') || $request->has('images')) && !$isBeforeInProgress) {
            $inputImages = $request->input('admin_images') ?: $request->input('images');
            if (is_array($inputImages)) {
                $processedAdminImages = [];
                foreach ($inputImages as $img) {
                    if (empty($img)) continue;
                    // Jika dikirim sebagai base64 Data URL (foto baru dari admin)
                    if (is_string($img) && preg_match('/^data:image\/(\w+);base64,/', $img, $type)) {
                        $data = substr($img, strpos($img, ',') + 1);
                        $type = strtolower($type[1]);
                        $data = base64_decode($data);
                        if ($data !== false) {
                            $savedName = 'submission_' . $submission->id . '_admin_' . time() . '_' . Str::random(6) . '.' . $type;
                            Storage::disk('public')->put('submissions/' . $savedName, $data);
                            $processedAdminImages[] = '/storage/submissions/' . $savedName;
                        }
                    } else {
                        // Hindari menduplikasi user image ke dalam admin_images
                        if ($img !== $submission->image_url) {
                            $processedAdminImages[] = $img;
                        }
                    }
                }
                // Batasi maksimal 3 foto untuk admin
                $extra['admin_images'] = array_slice($processedAdminImages, 0, 3);
                $extra['images'] = $extra['admin_images'];
            }
        }

        $updateData = [
            'status' => $newStatus,
            'admin_notes' => $adminNotes,
            'additional_data' => $extra,
        ];

        // Handle pembaruan atau penghapusan foto referensi dari user oleh admin
        if ($request->has('user_image')) {
            $userImg = $request->input('user_image');
            if (empty($userImg)) {
                $updateData['image_url'] = '';
            } elseif (is_string($userImg) && preg_match('/^data:image\/(\w+);base64,/', $userImg, $type)) {
                $data = substr($userImg, strpos($userImg, ',') + 1);
                $type = strtolower($type[1]);
                $data = base64_decode($data);
                if ($data !== false) {
                    $savedName = 'submission_' . $submission->id . '_user_' . time() . '_' . Str::random(6) . '.' . $type;
                    Storage::disk('public')->put('submissions/' . $savedName, $data);
                    $updateData['image_url'] = '/storage/submissions/' . $savedName;
                }
            } else {
                $updateData['image_url'] = $userImg;
            }
        }

        // Catat timestamp persetujuan atau penolakan
        $statusLower = strtolower($newStatus);
        if ($statusLower === 'approved' || $statusLower === 'found!' || $statusLower === 'paid') {
            $updateData['approved_at'] = now();
        } elseif ($statusLower === 'rejected' || $statusLower === 'cancel' || $statusLower === 'cancelled') {
            $updateData['rejected_at'] = now();
        }

        $submission->update($updateData);

        // Jika disetujui (Approved / Found!), otomatis publikasikan ke katalog Item publik
        if ($statusLower === 'approved' || $statusLower === 'found!' || $statusLower === 'paid') {
            $baseTitle = ($submission->artist_band ?: $submission->title) . ' - ' . ($submission->merch_edition ?: $submission->merch_type ?: 'Edition');
            $baseSlug = Str::slug($baseTitle);
            $slug = $baseSlug ?: 'item';
            $counter = 1;
            while (Item::where('slug', $slug)->exists()) {
                $slug = "{$baseSlug}-" . ($counter++);
            }

            Item::updateOrCreate(
                ['submission_id' => $submission->id],
                [
                    'title' => $baseTitle,
                    'slug' => $slug,
                    'category' => $submission->merch_type ?: $submission->category ?: 'T-Shirt',
                    'description' => $submission->description,
                    'contact' => $submission->contact ?: $submission->user_email,
                    'image_url' => $submission->image_url,
                    'views' => 10,
                ]
            );
        }

        // Sinkronisasi data ke Firebase
        $this->firebase->syncSubmission($submission->toArray());

        $responseData = array_merge($submission->toArray(), [
            'userImage' => $submission->image_url,
            'adminImages' => $extra['admin_images'] ?? [],
            'images' => $extra['admin_images'] ?? ($extra['images'] ?? []),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Status pengajuan berhasil diubah menjadi: {$newStatus}",
            'data' => $responseData,
            'submission' => $submission,
        ]);
    }

    /**
     * Endpoint shortcut untuk menyetujui pengajuan secara langsung (Status: Approved).
     * 
     * @param int $id ID pengajuan
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function approve($id, Request $request)
    {
        $request->merge(['status' => 'Approved']);
        return $this->updateStatus($id, $request);
    }

    /**
     * Endpoint shortcut untuk menolak pengajuan secara langsung (Status: Rejected).
     * 
     * @param int $id ID pengajuan
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function reject($id, Request $request)
    {
        $request->merge(['status' => 'Rejected']);
        return $this->updateStatus($id, $request);
    }

    /**
     * Menyelesaikan objek user administrator yang melakukan request.
     * 
     * @param Request $request
     * @return User|null
     */
    protected function resolveAdminUser(Request $request): ?User
    {
        try {
            if ($user = auth('sanctum')->user()) {
                return $user;
            }
        } catch (\Throwable $e) {}

        if ($user = auth()->user()) {
            return $user;
        }

        if ($request->hasSession() && $sessUser = session('wm_user')) {
            if (isset($sessUser['id'])) {
                $user = User::find($sessUser['id']);
                if ($user) return $user;
            }
            if (isset($sessUser['email'])) {
                $user = User::where('email', strtolower($sessUser['email']))->first();
                if ($user) return $user;
            }
        }

        // Cek bearer token jika token dikirimkan
        if ($bearer = $request->bearerToken()) {
            try {
                $tokenObj = \Laravel\Sanctum\PersonalAccessToken::findToken($bearer);
                if ($tokenObj && $tokenObj->tokenable) {
                    return $tokenObj->tokenable;
                }
            } catch (\Throwable $e) {}
        }

        // Cek parameter active_user_id jika ada
        if ($request->filled('active_user_id')) {
            $user = User::find($request->input('active_user_id'));
            if ($user) return $user;
        }

        // Cek parameter caller_email jika ada
        if ($request->filled('caller_email')) {
            $user = User::where('email', strtolower($request->input('caller_email')))->first();
            if ($user) return $user;
        }

        return null;
    }

    /**
     * Mengambil daftar seluruh akun administrator (admin dan superadmin).
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAdmins(Request $request)
    {
        $admins = User::whereIn('role', ['admin', 'superadmin'])
            ->orWhere('email', 'like', '%admin%')
            ->select('id', 'name', 'email', 'role', 'created_at')
            ->orderBy('id', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $admins,
        ]);
    }

    /**
     * Menambahkan akun Admin baru oleh Superadmin.
     * Mengikuti format dan validasi seperti registrasi user (email, username, password).
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createAdmin(Request $request)
    {
        $caller = $this->resolveAdminUser($request);

        if (!$caller || ($caller->role !== 'superadmin' && !str_contains(strtolower($caller->email ?? ''), 'superadmin'))) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak: Hanya Superadmin yang memiliki wewenang untuk menambahkan akun Admin baru.',
            ], 403);
        }

        // Kebijakan Sistem: Akun Superadmin hanya boleh ada 1 akun dan tidak dapat ditambah lagi.
        if ($request->input('role') === 'superadmin') {
            return response()->json([
                'success' => false,
                'message' => 'Akun Superadmin dalam sistem dibatasi hanya 1 akun dan tidak dapat ditambah lagi. Anda hanya dapat mendaftarkan akun Admin biasa.',
            ], 422);
        }

        $validated = $request->validate([
            'email' => 'required|string|email|max:255|unique:users',
            'username' => 'nullable|string|max:255',
            'name' => 'nullable|string|max:255',
            'password' => 'required|string|min:6',
            'role' => 'nullable|string|in:admin',
        ]);

        $name = $validated['username'] ?? $validated['name'] ?? explode('@', $validated['email'])[0];
        $role = 'admin'; // Selalu admin biasa, tidak dapat membuat superadmin kedua

        // Daftarkan ke Firebase jika aktif
        $firebaseUid = null;
        try {
            $firebaseUid = $this->firebase->createAuthUser(
                $validated['email'],
                $validated['password'],
                $name
            );
        } catch (\Throwable $e) {}

        $newAdmin = User::create([
            'name' => $name,
            'email' => strtolower($validated['email']),
            'password' => Hash::make($validated['password']),
            'role' => $role,
            'firebase_uid' => $firebaseUid,
        ]);

        try {
            $this->firebase->syncUser($newAdmin->toArray());
        } catch (\Throwable $e) {}

        return response()->json([
            'success' => true,
            'message' => "Admin baru ({$newAdmin->name}) berhasil ditambahkan sebagai {$role}!",
            'data' => [
                'id' => $newAdmin->id,
                'name' => $newAdmin->name,
                'email' => $newAdmin->email,
                'role' => $newAdmin->role,
                'created_at' => $newAdmin->created_at,
            ],
        ], 201);
    }

    /**
     * Mengambil daftar seluruh akun (User, Admin, Superadmin) khusus untuk Superadmin (Manage Account).
     * Mendukung filter role ('all', 'admin', 'user') dan pencarian kata kunci nama/email.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllAccounts(Request $request)
    {
        $caller = $this->resolveAdminUser($request);

        // Hanya administrator (Admin Biasa atau Superadmin) yang boleh mengakses Manage Account
        if (!$caller || !in_array($caller->role, ['admin', 'superadmin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak: Hanya Administrator yang berwenang mengakses Manage Account.',
            ], 403);
        }

        $isSuperadmin = ($caller->role === 'superadmin' || str_contains(strtolower($caller->email ?? ''), 'superadmin'));

        $query = User::query();

        // Admin Biasa hanya dapat mengelola akun role User
        if (!$isSuperadmin) {
            $query->where('role', 'user');
        } else {
            // Superadmin dapat memfilter berdasarkan kategori role jika diminta
            $roleFilter = $request->query('role');
            if ($roleFilter === 'admin') {
                $query->whereIn('role', ['admin', 'superadmin']);
            } elseif ($roleFilter === 'user') {
                $query->where('role', 'user');
            }
        }

        // Filter pencarian berdasarkan nama atau email
        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('full_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $accounts = $query->select('id', 'name', 'full_name', 'email', 'role', 'created_at')
            ->orderBy('id', 'asc')
            ->get();

        $totalUsers = User::where('role', 'user')->count();
        $totalAdmins = User::whereIn('role', ['admin', 'superadmin'])->count();
        $totalSuperadmins = User::where('role', 'superadmin')->count();

        return response()->json([
            'success' => true,
            'is_superadmin' => $isSuperadmin,
            'data' => $accounts,
            'stats' => [
                'total' => $isSuperadmin ? User::count() : $totalUsers,
                'user_count' => $totalUsers,
                'admin_count' => $isSuperadmin ? $totalAdmins : 0,
                'superadmin_count' => $isSuperadmin ? $totalSuperadmins : 0,
            ],
        ]);
    }

    /**
     * Memperbarui tingkatan/role dari suatu akun (User <-> Admin <-> Superadmin).
     * Hanya dapat dijalankan oleh Superadmin.
     * 
     * @param int $id ID user target
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateAccountRole($id, Request $request)
    {
        $caller = $this->resolveAdminUser($request);

        if (!$caller || ($caller->role !== 'superadmin' && !str_contains(strtolower($caller->email ?? ''), 'superadmin'))) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak: Hanya Superadmin yang berwenang mengubah role akun.',
            ], 403);
        }

        $targetUser = User::find($id);
        if (!$targetUser) {
            return response()->json([
                'success' => false,
                'message' => 'Akun tidak ditemukan.',
            ], 404);
        }

        // Kebijakan Sistem: Akun Superadmin hanya ada 1 dan rolenya tidak dapat diubah oleh siapapun
        if ($targetUser->role === 'superadmin' || strtolower($targetUser->email) === 'superadmin@wastingmoney.id') {
            return response()->json([
                'success' => false,
                'message' => 'Role Superadmin Utama bersifat permanen dan tidak dapat diubah.',
            ], 422);
        }

        $validated = $request->validate([
            'role' => 'required|string|in:user,admin',
        ]);

        $newRole = $validated['role'];

        // Proteksi: Tidak boleh menjadikan akun lain sebagai Superadmin
        if ($newRole === 'superadmin') {
            return response()->json([
                'success' => false,
                'message' => 'Akun Superadmin dalam sistem dibatasi hanya 1 akun. Akun lain tidak dapat dijadikan Superadmin.',
            ], 422);
        }

        $targetUser->role = $newRole;
        $targetUser->save();

        try {
            $this->firebase->syncUser($targetUser->toArray());
        } catch (\Throwable $e) {}

        return response()->json([
            'success' => true,
            'message' => "Role akun {$targetUser->name} berhasil diperbarui menjadi {$newRole}.",
            'data' => [
                'id' => $targetUser->id,
                'name' => $targetUser->name,
                'email' => $targetUser->email,
                'role' => $targetUser->role,
            ],
        ]);
    }

    /**
     * Menghapus akun (User atau Admin) oleh Superadmin.
     * 
     * @param int $id ID user target
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteAccount($id, Request $request)
    {
        $caller = $this->resolveAdminUser($request);

        if (!$caller || !in_array($caller->role, ['admin', 'superadmin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak: Hanya Administrator yang berwenang mengelola dan menghapus akun.',
            ], 403);
        }

        $isSuperadmin = ($caller->role === 'superadmin' || str_contains(strtolower($caller->email ?? ''), 'superadmin'));

        $targetUser = User::find($id);
        if (!$targetUser) {
            return response()->json([
                'success' => false,
                'message' => 'Akun tidak ditemukan.',
            ], 404);
        }

        // Tidak boleh menghapus diri sendiri
        if ($caller->id == $targetUser->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak dapat menghapus akun Anda sendiri.',
            ], 422);
        }

        // Akun Superadmin terlindungi secara mutlak dan tidak boleh dihapus
        if ($targetUser->role === 'superadmin' || strtolower($targetUser->email) === 'superadmin@wastingmoney.id') {
            return response()->json([
                'success' => false,
                'message' => 'Akun Superadmin Utama terlindungi dan tidak dapat dihapus dari sistem.',
            ], 422);
        }

        // Admin biasa hanya boleh menghapus akun User, tidak dapat menghapus akun Admin/Superadmin
        if (!$isSuperadmin && $targetUser->role !== 'user') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak: Admin biasa hanya berwenang mengelola dan menghapus akun User, tidak dapat mengelola akun Administrator.',
            ], 403);
        }

        // Proteksi akun default
        if (in_array(strtolower($targetUser->email), ['superadmin@wastingmoney.id', 'sysadmin@wastingmoney.id', 'admin@rakapteman.id'])) {
            return response()->json([
                'success' => false,
                'message' => 'Akun sistem utama terlindungi dan tidak dapat dihapus.',
            ], 422);
        }

        $targetUserName = $targetUser->name;
        $targetUser->delete();

        return response()->json([
            'success' => true,
            'message' => "Akun {$targetUserName} berhasil dihapus dari sistem.",
        ]);
    }

    /**
     * Menghapus formulir pengajuan (submission) merchandise oleh Administrator atau Superadmin.
     * 
     * Prosedur Operasional:
     * 1. Memverifikasi hak akses pemanggil (hanya role 'admin' atau 'superadmin').
     * 2. Mencari data pengajuan berdasarkan ID unik.
     * 3. Menghapus item katalog terkait jika pengajuan sudah sempat disetujui (Item::where('submission_id')).
     * 4. Melakukan sinkronisasi penghapusan dokumen pada Cloud Firestore & Realtime Database Firebase.
     * 5. Menghapus rekaman pengajuan dari database lokal (MySQL/SQLite).
     * 
     * @param int|string $id ID unik record pengajuan yang ingin dihapus
     * @param Request $request Request HTTP yang membawa informasi sesi / token admin
     * @return \Illuminate\Http\JsonResponse Respon status keberhasilan penghapusan
     */
    public function deleteSubmission($id, Request $request)
    {
        $caller = $this->resolveAdminUser($request);

        // Validasi otorisasi: Hanya admin biasa atau superadmin yang boleh menghapus pengajuan
        if (!$caller || !in_array($caller->role, ['admin', 'superadmin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak: Hanya Administrator yang berwenang menghapus pengajuan request.',
            ], 403);
        }

        $submission = Submission::find($id);
        if (!$submission) {
            return response()->json([
                'success' => false,
                'message' => 'Data pengajuan formulir tidak ditemukan atau sudah terhapus sebelumnya.',
            ], 404);
        }

        $requestId = $submission->request_id ?: str_pad($submission->id, 7, '0', STR_PAD_LEFT);

        // Jangan izinkan penghapusan untuk pengajuan yang sudah lunas (Paid)
        if (strtolower(trim($submission->status)) === 'paid') {
            return response()->json([
                'success' => false,
                'message' => "Permintaan #{$requestId} berstatus Paid (Lunas) dan terkunci sehingga tidak dapat dihapus.",
            ], 422);
        }

        // 1. Hapus katalog item yang terhubung jika sudah pernah disetujui
        try {
            Item::where('submission_id', $submission->id)->delete();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Hapus item katalog terkait submission gagal: ' . $e->getMessage());
        }

        // 2. Hapus data dokumen pengajuan dari Firebase Firestore & Realtime DB
        try {
            $this->firebase->deleteSubmission($submission->id);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Firebase deleteSubmission error: ' . $e->getMessage());
        }

        // 3. Hapus data pengajuan dari database lokal
        $submission->delete();

        return response()->json([
            'success' => true,
            'message' => "Permintaan request #{$requestId} berhasil dihapus dari sistem.",
        ]);
    }
}
