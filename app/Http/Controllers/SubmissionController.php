<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use App\Models\Notification;
use App\Services\FirebaseService;
use Illuminate\Http\Request;

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

        $user = $request->user();
        $nextId = (Submission::max('id') ?? 0) + 1;
        $formattedRequestId = str_pad($nextId, 7, '0', STR_PAD_LEFT);

        // Menangani file gambar yang diunggah
        $imageUrl = $request->input('image_url') ?: $request->input('imageUrl');
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('submissions', 'public');
            $imageUrl = '/storage/' . $path;
            $fileName = $request->file('image')->getClientOriginalName();
        }

        // Menyimpan data pengajuan baru dengan status awal 'Queue' (Abu-abu / User Request)
        $submission = Submission::create([
            'user_id' => $user ? $user->id : null,
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
            'contact' => $user ? $user->email : $request->input('contact', 'guest@wastingmoney.id'),
            'image_url' => $imageUrl,
            'file_name' => $fileName,
            'additional_data' => $request->input('additionalData'),
            'status' => 'Queue', // Awal mula request berstatus Queue (Abu-abu di Keranjang)
        ]);

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

            return [
                'id' => $sub->id,
                'requestId' => $sub->request_id ? (str_starts_with($sub->request_id, '#') ? $sub->request_id : '#' . $sub->request_id) : '#' . str_pad($sub->id, 6, '0', STR_PAD_LEFT),
                'artistBand' => $sub->artist_band ?: $sub->title ?: 'Unknown Artist',
                'merchType' => $sub->merch_type ?: $sub->category ?: 'Merch',
                'releasedYear' => $sub->released_year ?: '2026',
                'merchEdition' => $sub->merch_edition ?: '-',
                'description' => $sub->description ?: '-',
                'price' => $extra['price'] ?? '-',
                'currency' => $extra['currency'] ?? 'IDR',
                'priceAmount' => $extra['price_amount'] ?? '',
                'sellerNotes' => $extra['seller_notes'] ?? ($sub->admin_notes ?: '-'),
                'status' => $displayStatus,
                'statusDate' => $sub->created_at ? $sub->created_at->format('d/m/Y H:i:s') : now()->format('d/m/Y H:i:s'),
                'theme' => $theme,
                'dotClass' => $dotClass,
                'image' => $sub->image_url ?: '/assets/taylor_swift_shirt.png',
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
     * Mengambil seluruh pengajuan merchandise milik pengguna.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function mySubmissions(Request $request)
    {
        $user = $request->user() ?: (session('wm_user') ? (object)session('wm_user') : null);
        $userEmail = $user ? ($user->email ?? null) : $request->query('email');
        $userId = $user ? ($user->id ?? null) : null;

        $query = Submission::query();
        if ($userId) {
            $query->where('user_id', $userId);
        } elseif ($userEmail) {
            $query->where('user_email', $userEmail);
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

            return [
                'id' => $sub->id,
                'requestId' => $sub->request_id ? (str_starts_with($sub->request_id, '#') ? $sub->request_id : '#' . $sub->request_id) : '#' . str_pad($sub->id, 6, '0', STR_PAD_LEFT),
                'artistBand' => $sub->artist_band ?: $sub->title ?: 'Unknown Artist',
                'merchType' => $sub->merch_type ?: $sub->category ?: 'Merch',
                'releasedYear' => $sub->released_year ?: '2026',
                'merchEdition' => $sub->merch_edition ?: '-',
                'description' => $sub->description ?: '-',
                'price' => $extra['price'] ?? '-',
                'currency' => $extra['currency'] ?? 'IDR',
                'priceAmount' => $extra['price_amount'] ?? '',
                'sellerNotes' => $extra['seller_notes'] ?? ($sub->admin_notes ?: '-'),
                'status' => $displayStatus,
                'statusDate' => $sub->created_at ? $sub->created_at->format('d/m/Y H:i:s') : now()->format('d/m/Y H:i:s'),
                'theme' => $theme,
                'dotClass' => $dotClass,
                'image' => $sub->image_url ?: '/assets/taylor_swift_shirt.png',
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
