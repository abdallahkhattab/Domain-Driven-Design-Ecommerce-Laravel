<?php
// app/Domain/Order/Services/OrderService.php

namespace App\Domain\Order\Services;

use App\Domain\Order\Entities\Order;
use App\Domain\Order\ValueObjects\OrderId;
use App\Domain\Customer\ValueObjects\CustomerId;
use App\Domain\Order\Repositories\OrderRepositoryInterface;

class OrderService
{
    private OrderRepositoryInterface $orderRepository;

    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function placeOrder(CustomerId $customerId, array $items): Order
    {
        $orderId = $this->orderRepository->nextIdentity();
        
        $order = Order::place($orderId, $customerId, $items);
        
        $this->orderRepository->save($order);
        
        return $order;
    }

    public function getCustomerOrderHistory(CustomerId $customerId): array
    {
        return $this->orderRepository->findByCustomerId($customerId);
    }
}