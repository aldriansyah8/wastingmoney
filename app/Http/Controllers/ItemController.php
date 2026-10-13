<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $query = Item::query();

        if ($request->has('q') && !empty($request->q)) {
            $searchTerm = $request->q;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%")
                  ->orWhere('category', 'like', "%{$searchTerm}%");
            });
        }

        if ($request->has('category') && !empty($request->category)) {
            $query->where('category', $request->category);
        }

        $items = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'total' => $items->count(),
            'data' => $items,
        ]);
    }

    public function show($slug)
    {
        $item = Item::where('slug', $slug)->first();

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Item dengan slug tersebut tidak ditemukan.',
            ], 404);
        }

        $item->increment('views');

        return response()->json([
            'success' => true,
            'data' => $item,
        ]);
    }
}
