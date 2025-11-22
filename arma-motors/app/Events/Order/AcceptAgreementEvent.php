<?php

namespace App\Events\Order;

use App\Models\Agreement\Agreement;
use Illuminate\Queue\SerializesModels;

class AcceptAgreementEvent
{
    use SerializesModels;

    public function __construct(
        public Agreement $model,
    )
    {}
}

