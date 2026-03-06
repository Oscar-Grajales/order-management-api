<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request, OrderService $orderService)
    {
        $orders = $orderService->list($request->all());

        return response()->json($orders);
    }

    public function store(StoreOrderRequest $request, OrderService $orderService)
    {
        $order = $orderService->create($request->items);

        return response()->json([
            'order_code' => $order->code,
            'total' => $order->total,
        ], 201);
    }
}
