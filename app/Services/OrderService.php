<?php

namespace App\Services;

use App\Jobs\ProcessOrderJob;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    public function create(array $items): Order
    {
        return DB::transaction(function () use ($items) {
            $subtotal = 0;
            $itemsData = [];

            foreach ($items as $item) {
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
                    'product_id' => $product->id,
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

            ProcessOrderJob::dispatch($order);

            return $order;
        });
    }
}
