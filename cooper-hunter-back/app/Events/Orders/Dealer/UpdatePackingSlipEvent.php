<?php

namespace App\Events\Orders\Dealer;

use App\Models\Orders\Dealer\PackingSlip;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UpdatePackingSlipEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(protected PackingSlip $model)
    {}

    public function getPackingSlip(): PackingSlip
    {
        return $this->model;
    }
}
