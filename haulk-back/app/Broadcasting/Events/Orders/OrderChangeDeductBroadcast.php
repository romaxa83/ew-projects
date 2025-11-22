<?php


namespace App\Broadcasting\Events\Orders;

class OrderChangeDeductBroadcast extends OrderBroadcast
{

    public const NAME = 'order.change-deduct-from-driver';

    public bool $is_deducted;

    public function __construct(int $orderId, int $companyId, bool $isDeducted)
    {
        parent::__construct($orderId, $companyId);

        $this->is_deducted = $isDeducted;
    }

    protected function getName(): string
    {
        return self::NAME;
    }
}
