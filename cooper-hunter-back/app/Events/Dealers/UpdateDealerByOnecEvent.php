<?php

namespace App\Events\Dealers;

use App\Models\Dealers\Dealer;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UpdateDealerByOnecEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(protected Dealer $model)
    {}

    public function getDealer(): Dealer
    {
        return $this->model;
    }
}

