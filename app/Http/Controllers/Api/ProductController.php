<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Product;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function lowStock(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'threshold' => ['sometimes', 'integer', 'min:0'],
        ]);

        $threshold = (int) ($validated['threshold'] ?? config('store.low_stock_threshold', 10));

        $products = Product::where('stock_on_hand', '<', $threshold)
            ->orderBy('stock_on_hand')
            ->get();

        return response()->json([
            'message' => 'Low-stock products retrieved successfully.',
            'threshold' => $threshold,
            'count' => $products->count(),
            'data' => $products,
        ], 200);
    }
}
