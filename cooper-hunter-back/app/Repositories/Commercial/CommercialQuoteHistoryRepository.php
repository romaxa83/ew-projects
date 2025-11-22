<?php

namespace App\Repositories\Commercial;

use App\Models\Commercial\QuoteHistory;
use App\Repositories\AbstractRepository;
use Illuminate\Database\Eloquent\Builder;

final class CommercialQuoteHistoryRepository extends AbstractRepository
{
    public function modelQuery(): Builder
    {
        return QuoteHistory::query();
    }

    public function getLast(int $quoteId): ?QuoteHistory
    {
        return $this->modelQuery()
            ->where('quote_id', $quoteId)
            ->latest('position')
            ->first();
    }
}
