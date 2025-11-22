<?php

namespace App\Taps\Orders\Parts;

use Illuminate\Database\Eloquent\Builder;

final readonly class NotDraft
{
    public function __invoke(Builder $builder): void
    {
        $builder->whereNull('draft_at');
    }
}
