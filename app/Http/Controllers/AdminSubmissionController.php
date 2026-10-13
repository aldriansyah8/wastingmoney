<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use App\Models\Item;
use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminSubmissionController extends Controller
{
    protected FirebaseService $firebase;

    public function __construct(FirebaseService $firebase)
    {
        $this->firebase = $firebase;
    }

    public function index(Request $request)
    {
        $query = Submission::query();

        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', 'like', '%' . $request->status . '%');
        }

        $submissions = $query->orderBy('created_at', 'desc')->get();

        // Format according to frontend expectation
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
                'sellerNotes' => $extra['seller_notes'] ?? ($sub->admin_notes ?: '-'),
                'fullName' => $extra['full_name'] ?? 'bukan manusia biasa',
                'address' => $extra['address'] ?? 'Jl. xxxxxxx, xxxx, xxxxxxxxxxxxxxxx, xxxxxxxx',
                'postalCode' => $extra['postal_code'] ?? '1945',
                'phoneNumber' => $extra['phone_number'] ?? ($sub->contact ?: '0813 888 8888'),
                'foundAt' => $sub->created_at ? $sub->created_at->format('d/m/Y H:i:s') : '10/11/2026 10:08:55',
                'submittedBy' => [
                    'name' => $sub->user_name ?: 'uccloud26',
                    'username' => $sub->user_name ?: 'uccloud26',
                    'email' => $sub->user_email ?: 'uccloud26@gmail.com',
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

    public function updateStatus($id, Request $request)
    {
        $submission = Submission::findOrFail($id);

        $newStatus = $request->input('status', 'In Progress');
        $adminNotes = $request->input('adminNotes', $request->input('admin_notes', ''));
        $price = $request->input('price');
        $sellerNotes = $request->input('sellerNotes', $request->input('seller_notes'));

        $extra = $submission->additional_data ?: [];
        if ($price !== null && $price !== '') {
            $extra['price'] = $price;
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

        if (strtolower($newStatus) === 'approved') {
            $updateData['approved_at'] = now();
        } elseif (strtolower($newStatus) === 'rejected') {
            $updateData['rejected_at'] = now();
        }

        $submission->update($updateData);

        // Jika approved, publikasikan otomatis ke katalog Item
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

        // Sync ke Firebase
        $this->firebase->syncSubmission($submission->toArray());

        return response()->json([
            'success' => true,
            'message' => "Status pengajuan berhasil diubah menjadi: {$newStatus}",
            'data' => $submission,
        ]);
    }

    public function approve($id, Request $request)
    {
        $request->merge(['status' => 'Approved']);
        return $this->updateStatus($id, $request);
    }

    public function reject($id, Request $request)
    {
        $request->merge(['status' => 'Rejected']);
        return $this->updateStatus($id, $request);
    }
}
