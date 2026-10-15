<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use App\Models\Item;
use App\Services\FirebaseService;
use Illuminate\Http\Request;
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
        $query = Submission::query();

        // Filter berdasarkan status jika parameter status disertakan
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', 'like', '%' . $request->status . '%');
        }

        $submissions = $query->orderBy('created_at', 'desc')->get();

        // Format data sesuai kebutuhan tampilan dashboard admin
        $formatted = $submissions->map(function ($sub) {
            $extra = $sub->additional_data ?: [];
            return [
                'id' => $sub->id,
                'requestId' => $sub->request_id ?: str_pad($sub->id, 7, '0', STR_PAD_LEFT),
                'request_id' => $sub->request_id ?: str_pad($sub->id, 7, '0', STR_PAD_LEFT),
                'artistBand' => $sub->artist_band ?: $sub->title,
                'artist_band' => $sub->artist_band ?: $sub->title,
                'title' => $sub->title,
                'merchType' => $sub->merch_type ?: $sub->category ?: 'T-Shirt',
                'merch_type' => $sub->merch_type ?: $sub->category ?: 'T-Shirt',
                'category' => $sub->category,
                'releasedYear' => $sub->released_year ?: '2024',
                'released_year' => $sub->released_year ?: '2024',
                'merchEdition' => $sub->merch_edition ?: 'North American Tour',
                'merch_edition' => $sub->merch_edition ?: 'North American Tour',
                'description' => $sub->description,
                'contact' => $sub->contact,
                'fileName' => $sub->file_name,
                'imageUrl' => $sub->image_url,
                'image_url' => $sub->image_url,
                'status' => $sub->status,
                'adminNotes' => $sub->admin_notes,
                'admin_notes' => $sub->admin_notes,
                'price' => $extra['price'] ?? '-',
                'currency' => $extra['currency'] ?? 'IDR',
                'priceAmount' => $extra['price_amount'] ?? '',
                'sellerNotes' => $extra['seller_notes'] ?? ($sub->admin_notes ?: '-'),
                'fullName' => $extra['full_name'] ?? 'bukan manusia biasa',
                'address' => $extra['address'] ?? 'Jl. xxxxxxx, xxxx, xxxxxxxxxxxxxxxx, xxxxxxxx',
                'postalCode' => $extra['postal_code'] ?? '1945',
                'phoneNumber' => $extra['phone_number'] ?? ($sub->contact ?: '0813 888 8888'),
                'foundAt' => $sub->created_at ? $sub->created_at->format('d/m/Y H:i:s') : '10/11/2026 10:08:55',
                'submittedBy' => [
                    'name' => $sub->user_name ?: 'Guest',
                    'username' => $sub->user_name ?: 'guest',
                    'email' => $sub->user_email ?: 'guest@wastingmoney.id',
                ],
                'createdAt' => $sub->created_at ? $sub->created_at->toIso8601String() : now()->toIso8601String(),
                'created_at' => $sub->created_at ? $sub->created_at->toIso8601String() : now()->toIso8601String(),
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

        $newStatus = $request->input('status', 'In Progress');
        $adminNotes = $request->input('adminNotes', $request->input('admin_notes', ''));
        $price = $request->input('price');
        $currency = $request->input('currency');
        $priceAmount = $request->input('priceAmount', $request->input('price_amount'));
        $sellerNotes = $request->input('sellerNotes', $request->input('seller_notes'));

        // Simpan data tambahan (harga komposit, mata uang, nominal terpisah, catatan penjual)
        $extra = $submission->additional_data ?: [];
        if ($price !== null && $price !== '') {
            $extra['price'] = $price;
        }
        if ($currency !== null && $currency !== '') {
            $extra['currency'] = $currency;
        }
        if ($priceAmount !== null && $priceAmount !== '') {
            $extra['price_amount'] = $priceAmount;
        }
        if ($sellerNotes !== null && $sellerNotes !== '') {
            $extra['seller_notes'] = $sellerNotes;
            if (!$adminNotes) {
                $adminNotes = $sellerNotes;
            }
        }

        $updateData = [
            'status' => $newStatus,
            'admin_notes' => $adminNotes,
            'additional_data' => $extra,
        ];

        // Catat timestamp persetujuan atau penolakan
        if (strtolower($newStatus) === 'approved') {
            $updateData['approved_at'] = now();
        } elseif (strtolower($newStatus) === 'rejected') {
            $updateData['rejected_at'] = now();
        }

        $submission->update($updateData);

        // Jika disetujui (Approved), otomatis publikasikan ke katalog Item publik
        if (strtolower($newStatus) === 'approved') {
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

        return response()->json([
            'success' => true,
            'message' => "Status pengajuan berhasil diubah menjadi: {$newStatus}",
            'data' => $submission,
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
}
