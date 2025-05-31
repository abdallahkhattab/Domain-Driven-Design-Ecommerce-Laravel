<?php

namespace App\Application\UseCases\PlaceOrder;

use App\Domain\Order\Services\OrderService;
use App\Domain\Customer\ValueObjects\CustomerId;
use App\Domain\Order\Entities\Order;

class PlaceOrderHandler
{
    private OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function handle(PlaceOrderCommand $command): Order
    {
        $customerId = new CustomerId($command->customerId);
        
        return $this->orderService->placeOrder($customerId, $command->items);
    }
}