<?php

namespace App\Foundations\Modules\Location\Repositories;

use App\Foundations\Enums\CacheKeyEnum;
use App\Foundations\Modules\Location\Models\State;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

final readonly class StateRepository
{
    public function getStatesCaching(
        array $select = ['*'],
        array $filters = []
    ): Collection
    {
        return Cache::tags(CacheKeyEnum::States->value)
            ->rememberForever(cache_key(CacheKeyEnum::States->value, $select, $filters),
                fn() => State::query()
                    ->select($select)
                    ->filter($filters)
                    ->get()
            );
    }
}
