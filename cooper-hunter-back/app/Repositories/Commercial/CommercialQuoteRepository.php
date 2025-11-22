<?php

namespace App\Repositories\Commercial;

use App\Enums\Commercial\CommercialQuoteStatusEnum;
use App\Models\Commercial\CommercialQuote;
use Illuminate\Support\Collection;
use App\Repositories\AbstractRepository;
use Illuminate\Database\Eloquent\Builder;

final class CommercialQuoteRepository extends AbstractRepository
{
    public function modelQuery(): Builder
    {
        return CommercialQuote::query();
    }

    public function getCounterData(): Collection
    {
        $total = $this->modelQuery()
            ->selectRaw(
                "
            SUM(CASE
                WHEN status = ? THEN 1
                ELSE 0
            END) AS pending,
            SUM(CASE
                WHEN status = ? THEN 1
                ELSE 0
            END) AS done,
            SUM(CASE
                WHEN status = ? THEN 1
                ELSE 0
            END) AS final,
            COUNT(*) AS counter_total
        ",
                [
                    CommercialQuoteStatusEnum::PENDING,
                    CommercialQuoteStatusEnum::DONE,
                    CommercialQuoteStatusEnum::FINAL,
                ]
            )
            ->first();

        return collect(
            [
                'pending' => $total->pending ?? 0,
                'done' => $total->done ?? 0,
                'final' => $total->final ?? 0,
                'total' => $total->counter_total,
            ]
        );
    }
}
