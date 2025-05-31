<?php 

namespace App\Domain\Order\Entities;

class Order
{
    private OrderId $id;
    private CustomerId $customerId;
    private Collection $items;
    private OrderStatus $status;
    private Money $total;
    private \DateTimeImmutable $createdAt;
    private array $domainEvents =[];

    public function __construct(
        OrderId $id , 
        CustomerId $customerid , 
        Collection $items
      
     ){
        $this->id = $id;
        $this->customerId = $customerId;
        $this->items = $items;
        $this->status = OrderStatus::pending();
        $this->total = $this->calculateTotal();
        $this->createdAt = new \DateTimeImmutable();

    }

    public static function place(OrderId $id , CustomerId $customerId , array $items): self {
        $orderItems = collect ($items)->map(function ($item) {
            return new OrderItem(
                $item ['product_id'],
                $item ['quantity'],
                new Money ($item['price'])
            );
        });

        $order = new self($id, $customerId, $orderItems);
        $order->recordDomainEvent(new OrderPlaced($order));
        
        return $order;
    }

    public function confirm(): void
    {
        if(!this->status->isPending()){
        throw new \DomainException('Only pending orders can be confirmed');
        }

        $this->status = OrderStatus::confirmed();
    }


}