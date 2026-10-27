<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Jobs\SendOrderConfirmation;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'customer.name' => ['required', 'string', 'max:255'],
            'customer.email' => ['required', 'email', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'distinct', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        $order = DB::transaction(function () use ($validated) {
            $customer = Customer::firstOrCreate(
                ['email' => strtolower(trim($validated['customer']['email']))],
                ['name' => trim($validated['customer']['name'])]
            );

            $subtotal = 0;
            $taxTotal = 0;
            $orderItems = [];

            foreach ($validated['items'] as $item) {
                $product = Product::whereKey($item['product_id'])
                    ->lockForUpdate()
                    ->first();

                if (!$product) {
                    throw ValidationException::withMessages([
                        'items' => ['A selected product was not found.'],
                    ]);
                }

                if ($product->stock_on_hand < $item['quantity']) {
                    throw ValidationException::withMessages([
                        'items' => [
                            "Insufficient stock for product: {$product->name}.",
                        ],
                    ]);
                }

                $quantity = $item['quantity'];
                $unitPrice = (float) $product->price;
                $lineSubtotal = round($unitPrice * $quantity, 2);
                $lineTax = round(
                    $lineSubtotal * ((float) $product->tax_percentage / 100),
                    2
                );
                $lineTotal = round($lineSubtotal + $lineTax, 2);

                $subtotal += $lineSubtotal;
                $taxTotal += $lineTax;

                $orderItems[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'tax_amount' => $lineTax,
                    'line_total' => $lineTotal,
                ];
            }

            $order = Order::create([
                'customer_id' => $customer->id,
                'subtotal' => round($subtotal, 2),
                'tax_total' => round($taxTotal, 2),
                'tax_amount' => round($taxTotal, 2),
                'grand_total' => round($subtotal + $taxTotal, 2),
            ]);

            foreach ($orderItems as $item) {
                $order->items()->create([
                    'product_id' => $item['product']->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'tax_percentage' => $item['product']->tax_percentage,
                    'subtotal' => round(
                        $item['unit_price'] * $item['quantity'],
                        2
                    ),
                    'tax_amount' => $item['tax_amount'],
                    'total' => $item['line_total'],
                    'line_total' => $item['line_total'],
                ]);

                $item['product']->decrement(
                    'stock_on_hand',
                    $item['quantity']
                );
            }

            return $order;
        });

        $order->load(['customer', 'items.product']);

        SendOrderConfirmation::dispatch($order->id)->afterCommit();

        return response()->json([
            'message' => 'Order created successfully.',
            'data' => $order,
        ], 201);
    }
    public function customerOrders(string $email): JsonResponse
{
    $email = strtolower(trim($email));

    $customer = Customer::whereRaw('LOWER(email) = ?', [$email])
        ->first();

    if (!$customer) {
        return response()->json([
            'message' => 'Customer not found.',
        ], 404);
    }

    $orders = Order::with(['items.product'])
        ->where('customer_id', $customer->id)
        ->latest()
        ->get();

    return response()->json([
        'message' => 'Customer order history retrieved successfully.',
        'customer' => $customer,
        'orders' => $orders,
    ], 200);
}
}
