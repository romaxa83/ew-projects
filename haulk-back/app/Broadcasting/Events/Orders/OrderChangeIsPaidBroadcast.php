<?php


namespace App\Broadcasting\Events\Orders;

class OrderChangeIsPaidBroadcast extends OrderBroadcast
{

    public const NAME = 'order.change-is-paid';

    public bool $is_paid;

    public function __construct(int $orderId, int $companyId, bool $isPaid)
    {
        parent::__construct($orderId, $companyId);

        $this->is_paid = $isPaid;
    }

    protected function getName(): string
    {
        return self::NAME;
    }
}
