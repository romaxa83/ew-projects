<?php

namespace App\ModelFilters\Fueling;

use App\Enums\Fueling\FuelingHistoryStatusEnum;
use EloquentFilter\ModelFilter;
use Illuminate\Database\Eloquent\Builder;

class FuelingHistoryFilter extends ModelFilter
{
    public function status(string $status): FuelingHistoryFilter
    {
        return $this->where('status', $status);
    }

    public function notCompleted(bool $notCompleted): FuelingHistoryFilter
    {
        return $this
            ->when($notCompleted,
                fn(Builder $builder) => $builder->whereIn('status', [FuelingHistoryStatusEnum::IN_QUEUE, FuelingHistoryStatusEnum::IN_PROGRESS])
            )->when(!$notCompleted,
                fn(Builder $builder) => $builder->whereIn('status', [FuelingHistoryStatusEnum::SUCCESS])
            );
    }
}
