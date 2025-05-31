<?php
// app/Application/Http/Controllers/OrderController.php

namespace App\Application\Http\Controllers;

use App\Application\UseCases\PlaceOrder\PlaceOrderCommand;
use App\Application\UseCases\PlaceOrder\PlaceOrderHandler;
use App\Application\UseCases\GetOrder\GetOrderQuery;
use App\Application\UseCases\GetOrder\GetOrderHandler;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    private PlaceOrderHandler $placeOrderHandler;
    private GetOrderHandler $getOrderHandler;

    public function __construct(
        PlaceOrderHandler $placeOrderHandler,
        GetOrderHandler $getOrderHandler
    ) {
        $this->placeOrderHandler = $placeOrderHandler;
        $this->getOrderHandler = $getOrderHandler;
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => 'required|uuid',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|integer|min:0'
        ]);

        $command = new PlaceOrderCommand(
            $validated['customer_id'],
            $validated['items']
        );

        $order = $this->placeOrderHandler->handle($command);

        return response()->json([
            'id' => $order->getId()->getValue(),
            'customer_id' => $order->getCustomerId()->getValue(),
            'status' => $order->getStatus()->getValue(),
            'total' => $order->getTotal()->getFormattedAmount(),
            'currency' => $order->getTotal()->getCurrency(),
            'items' => $order->getItems()->map(function ($item) {
                return [
                    'product_id' => $item->getProductId(),
                    'quantity' => $item->getQuantity(),
                    'price' => $item->getPrice()->getFormattedAmount(),
                    'subtotal' => $item->getSubtotal()->getFormattedAmount()
                ];
            })->toArray()
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $query = new GetOrderQuery($id);
        $order = $this->getOrderHandler->handle($query);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        return response()->json([
            'id' => $order->getId()->getValue(),
            'customer_id' => $order->getCustomerId()->getValue(),
            'status' => $order->getStatus()->getValue(),
            'total' => $order->getTotal()->getFormattedAmount(),
            'currency' => $order->getTotal()->getCurrency(),
            'created_at' => $order->getCreatedAt()->format('Y-m-d H:i:s'),
            'items' => $order->getItems()->map(function ($item) {
                return [
                    'product_id' => $item->getProductId(),
                    'quantity' => $item->getQuantity(),
                    'price' => $item->getPrice()->getFormattedAmount(),
                    'subtotal' => $item->getSubtotal()->getFormattedAmount()
                ];
            })->toArray()
        ]);
    }
}