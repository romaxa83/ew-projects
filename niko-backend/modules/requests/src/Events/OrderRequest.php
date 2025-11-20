<?php

namespace WezomCms\Requests\Events;

use Illuminate\Queue\SerializesModels;
use WezomCms\ServicesOrders\Models\ServicesOrder;

class OrderRequest
{
    use SerializesModels;

    /**
     * @var ServicesOrder
     */
    public $order;

    public function __construct(ServicesOrder $order)
    {
        $this->order = $order;
    }
}
