<?php

namespace App\Filters\Commercial;

use App\Filters\BaseModelFilter;
use App\Traits\Filter\IdFilterTrait;

class CommercialQuoteHistoryFilter extends BaseModelFilter
{
    use IdFilterTrait;

    public function quoteId(int $value): void
    {
        $this->where('quote_id', $value);
    }
}

