<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CheckoutRequest;
use App\Http\Resources\Api\OrderResource;
use App\Http\Resources\Api\PaymentResource;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    /**
     * Process checkout:
     * 1. Validate request
     * 2. Open DB transaction
     * 3. Call products-service (PATCH /api/products/{id}/stock) with retry & timeout
     * 4. If fails -> rollback & return 422
     * 5. If ok -> create Payment with status 'success' and mark Order as 'paid'
     */
    public function checkout(CheckoutRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $productsUrl = rtrim(config('services.microservices.products', 'http://localhost:8002'), '/');
        $serviceToken = config('services.internal_token');

        $deductedProducts = [];

        try {
            return DB::transaction(function () use ($validated, $productsUrl, $serviceToken, &$deductedProducts) {
                // 1. Calculate total
                $total = 0;
                foreach ($validated['items'] as $item) {
                    $total += $item['quantity'] * $item['unit_price'];
                }

                // 2. Create Order & OrderItems
                $order = Order::create([
                    'user_id' => $validated['user_id'],
                    'total' => $total,
                    'status' => 'pending',
                ]);

                foreach ($validated['items'] as $item) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                    ]);
                }

                // 3. Decrement stock for each product in products-service
                foreach ($validated['items'] as $item) {
                    $response = Http::withHeaders([
                        'X-Service-Token' => $serviceToken,
                        'Accept' => 'application/json',
                    ])
                    ->timeout(5)
                    ->retry(2, 100)
                    ->patch("{$productsUrl}/api/products/{$item['product_id']}/stock", [
                        'quantity' => $item['quantity'],
                        'action' => 'decrement',
                    ]);

                    if (!$response->successful()) {
                        $errorMsg = $response->json('message') ?? 'Failed to update product stock';
                        throw new Exception("Product #{$item['product_id']}: {$errorMsg}");
                    }

                    $deductedProducts[] = [
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                    ];
                }

                // 4. Create Payment
                $payment = Payment::create([
                    'order_id' => $order->id,
                    'payment_method' => $validated['payment_method'],
                    'transaction_reference' => 'TXN-'.Str::upper(Str::random(12)),
                    'amount' => $total,
                    'status' => 'success',
                ]);

                // 5. Mark Order as paid
                $order->update(['status' => 'paid']);

                return response()->json([
                    'message' => 'Checkout completed successfully',
                    'data' => [
                        'order' => new OrderResource($order->load(['items', 'payment'])),
                        'payment' => new PaymentResource($payment),
                    ],
                ], 200);
            });
        } catch (Exception $e) {
            // Compensate already deducted products if needed
            foreach ($deductedProducts as $deducted) {
                try {
                    Http::withHeaders([
                        'X-Service-Token' => $serviceToken,
                        'Accept' => 'application/json',
                    ])
                    ->timeout(5)
                    ->patch("{$productsUrl}/api/products/{$deducted['product_id']}/stock", [
                        'quantity' => $deducted['quantity'],
                        'action' => 'increment',
                    ]);
                } catch (\Throwable $rollbackError) {
                    Log::error("Failed to compensate stock for product #{$deducted['product_id']}: {$rollbackError->getMessage()}");
                }
            }

            return response()->json([
                'message' => 'Checkout failed. Stock could not be reserved.',
                'errors' => [
                    'checkout' => [$e->getMessage()],
                ],
            ], 422);
        }
    }

    /**
     * Display the specified payment.
     */
    public function show(string $id): JsonResponse
    {
        $payment = Payment::with('order')->find($id);

        if (!$payment) {
            return response()->json(['message' => 'Payment not found'], 404);
        }

        return response()->json([
            'data' => new PaymentResource($payment),
        ]);
    }
}
