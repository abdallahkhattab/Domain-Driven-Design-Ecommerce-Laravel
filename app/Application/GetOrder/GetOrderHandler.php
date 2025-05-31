<?php

namespace App\Application\UseCases\GetOrder;

use App\Domain\Order\Entities\Order;
use App\Domain\Order\ValueObjects\OrderId;
use App\Domain\Order\Repositories\OrderRepositoryInterface;

class GetOrderHandler
{
    private OrderRepositoryInterface $orderRepository;

    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function handle(GetOrderQuery $query): ?Order
    {
        $orderId = new OrderId($query->orderId);
        
        return $this->orderRepository->findById($orderId);
    }
}