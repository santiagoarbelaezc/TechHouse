<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreProductRequest;
use App\Http\Requests\Api\UpdateStockRequest;
use App\Http\Resources\Api\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of products with optional filters.
     * Supports: category, min_price, max_price, search
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::with('category');

        if ($request->filled('category')) {
            $categoryParam = $request->query('category');
            $query->whereHas('category', function ($q) use ($categoryParam) {
                if (is_numeric($categoryParam)) {
                    $q->where('id', $categoryParam);
                } else {
                    $q->where('slug', $categoryParam);
                }
            });
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', (float) $request->query('min_price'));
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', (float) $request->query('max_price'));
        }

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $perPage = (int) $request->query('per_page', 15);
        $products = $query->paginate($perPage);

        return response()->json([
            'data' => ProductResource::collection($products),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    /**
     * Display the specified product.
     */
    public function show(string $id): JsonResponse
    {
        $product = Product::with('category')->find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return response()->json([
            'data' => new ProductResource($product),
        ]);
    }

    /**
     * Store a newly created product.
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        $data = $request->validated();

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $product = Product::create($data);
        $product->load('category');

        return response()->json([
            'message' => 'Product created successfully',
            'data' => new ProductResource($product),
        ], 201);
    }

    /**
     * Update the specified product.
     */
    public function update(StoreProductRequest $request, string $id): JsonResponse
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $product->update($request->validated());
        $product->load('category');

        return response()->json([
            'message' => 'Product updated successfully',
            'data' => new ProductResource($product),
        ]);
    }

    /**
     * Remove the specified product.
     */
    public function destroy(string $id): JsonResponse
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully',
        ]);
    }

    /**
     * Update product stock (internal endpoint with lockForUpdate to prevent race conditions).
     */
    public function updateStock(UpdateStockRequest $request, string $id): JsonResponse
    {
        return DB::transaction(function () use ($request, $id) {
            $product = Product::lockForUpdate()->find($id);

            if (!$product) {
                return response()->json([
                    'message' => 'Product not found',
                ], 404);
            }

            $quantity = (int) $request->input('quantity', 0);
            $action = $request->input('action', 'decrement');

            if ($action === 'decrement') {
                if ($product->stock < $quantity) {
                    return response()->json([
                        'message' => 'Insufficient stock',
                        'errors' => [
                            'stock' => ["Only {$product->stock} items available, requested {$quantity}."],
                        ],
                    ], 422);
                }
                $product->stock -= $quantity;
            } elseif ($action === 'increment') {
                $product->stock += $quantity;
            } elseif ($action === 'set') {
                if ($quantity < 0) {
                    return response()->json([
                        'message' => 'Stock cannot be negative',
                    ], 422);
                }
                $product->stock = $quantity;
            }

            $product->save();

            return response()->json([
                'message' => 'Stock updated successfully',
                'data' => new ProductResource($product),
            ]);
        });
    }
}
