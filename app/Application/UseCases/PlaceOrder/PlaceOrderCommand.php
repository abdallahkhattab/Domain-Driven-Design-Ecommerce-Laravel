<?php

namespace App\Application\UseCases\PlaceOrder;

class PlaceOrderCommand
{
    public function __construct(
        public readonly string $customerId,
        public readonly array $items
    ) {}
}