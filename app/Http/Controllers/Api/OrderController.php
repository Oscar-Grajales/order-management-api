<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function store(StoreOrderRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $subtotal = 0;
            $itemsData = [];

            foreach ($request->items as $item) {
                $product = Product::query()
                    ->where('id', $item['product_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($product->stock < $item['quantity']) {
                    abort(400, 'Insufficient stock for product: ' . $product->name);
                }

                $product->decrement('stock', $item['quantity']);

                $total = $product->price * $item['quantity'];
                $subtotal += $total;

                $itemsData[] = [
                    'product_id' => $item['product_id'],
                    'product_name' => $product->name,
                    'quantity' => $item['quantity'],
                    'unit_price' => $product->price,
                    'total' => $total,
                ];
            }

            $order = Order::query()
                ->create([
                    'code' => 'ORD-' . Str::upper(Str::random(8)),
                    'status' => 'pending',
                    'subtotal' => $subtotal,
                    'total' => $subtotal,
                ]);

            foreach ($itemsData as $data) {
                $order->items()->create($data);
            }

            return response()->json([
                'order_code' => $order->code,
                'total' => $order->total,
            ], 201);
        });
    }
}
