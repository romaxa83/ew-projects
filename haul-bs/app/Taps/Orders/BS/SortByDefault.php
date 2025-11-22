<?php

namespace App\Taps\Orders\BS;

use App\Enums\Orders\BS\OrderStatus;
use App\Foundations\Enums\EnumHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

final readonly class SortByDefault
{
    public function __invoke(Builder $builder)
    {
        $statuses = '{' . implode(',', EnumHelper::values(OrderStatus::class)) . '}';

        return $builder
            ->addSelect(DB::raw('ARRAY_POSITION(\'' . $statuses . '\', status) as status_sort'))
            ->orderBy('status_sort', 'asc')
            ->orderBy('implementation_date', 'asc');
    }
}
