<?php

namespace App\Taps\Customers;

use Illuminate\Database\Eloquent\Builder;

final readonly class NotHaulk
{
    public function __invoke(Builder $builder): void
    {
        $builder->where('from_haulk', false);
    }
}
