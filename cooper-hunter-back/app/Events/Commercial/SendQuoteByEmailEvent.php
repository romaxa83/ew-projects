<?php

namespace App\Events\Commercial;

use App\Models\Commercial\CommercialQuote;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SendQuoteByEmailEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(protected CommercialQuote $model)
    {}

    public function getCommercialQuote(): CommercialQuote
    {
        return $this->model;
    }
}
