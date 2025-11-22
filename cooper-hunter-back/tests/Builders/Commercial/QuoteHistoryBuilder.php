<?php

namespace Tests\Builders\Commercial;

use App\Models\Commercial\QuoteHistory;
use Tests\Builders\BaseBuilder;

class QuoteHistoryBuilder extends BaseBuilder
{
    protected function modelClass(): string
    {
        return QuoteHistory::class;
    }

    public function setQuoteId($value): self
    {
        $this->data['quote_id'] = $value;

        return $this;
    }

    public function setAdminId($value): self
    {
        $this->data['admin_id'] = $value;

        return $this;
    }

    public function setPosition($value): self
    {
        $this->data['position'] = $value;

        return $this;
    }
}




