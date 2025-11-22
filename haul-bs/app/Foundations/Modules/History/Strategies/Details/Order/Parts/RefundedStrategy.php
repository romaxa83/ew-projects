<?php

namespace App\Foundations\Modules\History\Strategies\Details\Order\Parts;

use App\Foundations\Modules\History\Strategies\Details\BaseDetailsStrategy;
use App\Models\Orders\Parts\Order;

class RefundedStrategy extends BaseDetailsStrategy
{
    public function __construct(
        protected Order $model
    )
    {}

    public function getDetails(): array
    {
        $tmp['refunded_at'] = [
            'old' => null,
            'new' => $this->model->refunded_at->format('Y-m-d H:i'),
            'type' => self::TYPE_ADDED
        ];

        return $tmp;
    }
}
