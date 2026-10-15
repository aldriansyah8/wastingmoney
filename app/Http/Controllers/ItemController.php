<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    /**
     * Mengambil daftar item katalog (barang belanja yang telah disetujui / approved).
     * Dapat difilter berdasarkan kata kunci pencarian (?q=...) dan kategori (?category=...).
     * 
     * @param Request $request HTTP Request dari client
     * @return \Illuminate\Http\JsonResponse JSON response berisi daftar item dan total count
     */
    public function index(Request $request)
    {
        $query = Item::query();

        // Filter pencarian berdasarkan judul, deskripsi, atau kategori
        if ($request->has('q') && !empty($request->q)) {
            $searchTerm = $request->q;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%")
                  ->orWhere('category', 'like', "%{$searchTerm}%");
            });
        }

        // Filter spesifik berdasarkan kategori produk
        if ($request->has('category') && !empty($request->category)) {
            $query->where('category', $request->category);
        }

        // Urutkan dari item terbaru yang disetujui
        $items = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'total' => $items->count(),
            'data' => $items,
        ]);
    }

    /**
     * Mengambil rincian detail satu item katalog berdasarkan slug unik.
     * Secara otomatis menambahkan jumlah views (view count) setiap kali dilihat.
     * 
     * @param string $slug Slug unik pengenal produk (contoh: 'taylor-swift-shirt-123')
     * @return \Illuminate\Http\JsonResponse JSON detail item atau 404 jika tidak ditemukan
     */
    public function show($slug)
    {
        $item = Item::where('slug', $slug)->first();

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Item dengan slug tersebut tidak ditemukan.',
            ], 404);
        }

        // Tambah counter view untuk tracking popularitas item
        $item->increment('views');

        return response()->json([
            'success' => true,
            'data' => $item,
        ]);
    }
}
