<?php

namespace App\Foundations\Modules\Localization\Repositories;

use App\Foundations\Enums\CacheKeyEnum;
use App\Foundations\Modules\Localization\Models\Language;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

final readonly class LanguageRepository
{
    public function getLanguages(
        array $select = ['*'],
        array $filters = []
    ): Collection
    {
        return Cache::tags(CacheKeyEnum::Languages->value)
            ->rememberForever(cache_key(CacheKeyEnum::Languages->value, $select, $filters),
                fn() => Language::query()
                    ->select($select)
                    ->filter($filters)
                    ->when(!array_key_exists('sort', $filters),
                        fn(Builder $b) => $b->orderBy('sort')
                    )
                    ->get()
            );
    }

    public function getDefault(): ?Language
    {
        return Cache::tags(CacheKeyEnum::Languages->value)
            ->rememberForever(CacheKeyEnum::Languages_default->value,
                fn() => Language::query()
                    ->where('default', true)
                    ->first()
            );
    }
}

