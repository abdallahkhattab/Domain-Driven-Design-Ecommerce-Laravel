<?php

namespace App\Application\UseCases\GetOrder;

class GetOrderQuery
{
    public function __construct(
        public readonly string $orderId
    ) {}
}