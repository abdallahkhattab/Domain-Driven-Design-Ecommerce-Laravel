<?php
namespace App\Domain\Order\ValueObjects;

class Money
{
    private int $amount;
    private string $currency;

    public function __construct(int $amount , string $currency = 'USD')
    {
        if($amount < 0) {
            throw new \InvalidArgumentException('Amount cannot be negative');
        }
    }
}