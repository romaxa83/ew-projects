<?php

namespace Tests\Builders\Commercial;

use App\Models\Commercial\QuoteItem;
use Tests\Builders\BaseBuilder;

class QuoteItemBuilder extends BaseBuilder
{
    protected function modelClass(): string
    {
        return QuoteItem::class;
    }

    public function setQuoteId($value): self
    {
        $this->data['commercial_quote_id'] = $value;

        return $this;
    }
}



