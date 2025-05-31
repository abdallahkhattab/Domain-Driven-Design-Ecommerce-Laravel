<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Order\Entities\Order;
use App\Domain\Order\Entities\OrderItem;
use App\Domain\Order\ValueObjects\OrderId;
use App\Domain\Order\ValueObjects\OrderStatus;
use App\Domain\Order\ValueObjects\Money;
use App\Domain\Customer\ValueObjects\CustomerId;
use App\Domain\Order\Repositories\OrderRepositoryInterface;
use App\Infrastructure\Models\OrderModel;
use Illuminate\Support\Collection;

class EloquentOrderRepository implements OrderRepositoryInterface
{
    public function save(Order $order): void
    {
        $orderModel = OrderModel::updateOrCreate(
            ['id' => $order->getId()->getValue()],
            [
                'customer_id' => $order->getCustomerId()->getValue(),
                'status' => $order->getStatus()->getValue(),
                'total_amount' => $order->getTotal()->getAmount(),
                'total_currency' => $order->getTotal()->getCurrency(),
                'created_at' => $order->getCreatedAt(),
            ]
        );

        // Save order items
        $orderModel->items()->delete();
        foreach ($order->getItems() as $item) {
            $orderModel->items()->create([
                'product_id' => $item->getProductId(),
                'quantity' => $item->getQuantity(),
                'price_amount' => $item->getPrice()->getAmount(),
                'price_currency' => $item->getPrice()->getCurrency(),
            ]);
        }
    }

    public function findById(OrderId $id): ?Order
    {
        $orderModel = OrderModel::with('items')->find($id->getValue());
        
        if (!$orderModel) {
            return null;
        }

        return $this->toDomainEntity($orderModel);
    }

    public function findByCustomerId(CustomerId $customerId): array
    {
        $orderModels = OrderModel::with('items')
            ->where('customer_id', $customerId->getValue())
            ->get();

        return $orderModels->map(function ($orderModel) {
            return $this->toDomainEntity($orderModel);
        })->toArray();
    }

    public function nextIdentity(): OrderId
    {
        return OrderId::generate();
    }

    private function toDomainEntity(OrderModel $orderModel): Order
    {
        $items = $orderModel->items->map(function ($itemModel) {
            return new OrderItem(
                $itemModel->product_id,
                $itemModel->quantity,
                new Money($itemModel->price_amount, $itemModel->price_currency)
            );
        });

        // Using reflection to create Order with private constructor
        $reflection = new \ReflectionClass(Order::class);
        $order = $reflection->newInstanceWithoutConstructor();

        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($order, new OrderId($orderModel->id));

        $customerIdProperty = $reflection->getProperty('customerId');
        $customerIdProperty->setAccessible(true);
        $customerIdProperty->setValue($order, new CustomerId($orderModel->customer_id));

        $itemsProperty = $reflection->getProperty('items');
        $itemsProperty->setAccessible(true);
        $itemsProperty->setValue($order, $items);

        $statusProperty = $reflection->getProperty('status');
        $statusProperty->setAccessible(true);
        
        $statusValue = match($orderModel->status) {
            'pending' => OrderStatus::pending(),
            'confirmed' => OrderStatus::confirmed(),
            'shipped' => OrderStatus::shipped(),
            'delivered' => OrderStatus::delivered(),
            'cancelled' => OrderStatus::cancelled(),
        };
        $statusProperty->setValue($order, $statusValue);

        $totalProperty = $reflection->getProperty('total');
        $totalProperty->setAccessible(true);
        $totalProperty->setValue($order, new Money($orderModel->total_amount, $orderModel->total_currency));

        $createdAtProperty = $reflection->getProperty('createdAt');
        $createdAtProperty->setAccessible(true);
        $createdAtProperty->setValue($order, new \DateTimeImmutable($orderModel->created_at));

        return $order;
    }
}