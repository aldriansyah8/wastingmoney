<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use App\Models\Notification;
use App\Services\FirebaseService;
use Illuminate\Http\Request;

class SubmissionController extends Controller
{
    protected FirebaseService $firebase;

    public function __construct(FirebaseService $firebase)
    {
        $this->firebase = $firebase;
    }

    public function store(Request $request)
    {
        $artistBand = $request->input('artistBand') ?: $request->input('title');
        $merchType = $request->input('merchType') ?: $request->input('category') ?: 'T-Shirt';
        $releasedYear = $request->input('releasedYear') ?: $request->input('released_year') ?: date('Y');
        $merchEdition = $request->input('merchEdition') ?: $request->input('merch_edition');
        $description = $request->input('description') ?: $request->input('moreDescription') ?: '-';
        $fileName = $request->input('fileName') ?: ($request->has('additionalData.fileName') ? $request->input('additionalData.fileName') : null);

        if (!$artistBand) {
            return response()->json([
                'success' => false,
                'message' => 'Field Artist/Band wajib diisi.',
            ], 422);
        }

        $user = $request->user();
        $nextId = (Submission::max('id') ?? 0) + 1;
        $formattedRequestId = str_pad($nextId, 7, '0', STR_PAD_LEFT);

        // Handle uploaded file if present
        $imageUrl = $request->input('image_url') ?: $request->input('imageUrl');
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('submissions', 'public');
            $imageUrl = '/storage/' . $path;
            $fileName = $request->file('image')->getClientOriginalName();
        }

        $submission = Submission::create([
            'user_id' => $user ? $user->id : null,
            'user_name' => $user ? ($user->name ?: $user->email) : $request->input('user_name', 'uccloud26'),
            'user_email' => $user ? $user->email : $request->input('user_email', 'uccloud26@gmail.com'),
            'request_id' => $formattedRequestId,
            'title' => $artistBand,
            'artist_band' => $artistBand,
            'merch_type' => $merchType,
            'released_year' => $releasedYear,
            'merch_edition' => $merchEdition,
            'category' => $merchType,
            'description' => $description,
            'contact' => $user ? $user->email : $request->input('contact', 'uccloud26@gmail.com'),
            'image_url' => $imageUrl,
            'file_name' => $fileName,
            'additional_data' => $request->input('additionalData'),
            'status' => 'In Progress',
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

    public function mySubmissions(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Pengguna belum terautentikasi.',
            ], 401);
        }

        $submissions = Submission::where('user_id', $user->id)
            ->orWhere('user_email', $user->email)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'total' => $submissions->count(),
            'data' => $submissions,
        ]);
    }
}
