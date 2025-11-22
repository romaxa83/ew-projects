<?php

namespace App\Foundations\Modules\Localization\Repositories;

use App\Foundations\Enums\CacheKeyEnum;
use App\Foundations\Modules\Localization\Models\Translation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

final readonly class TranslationRepository
{
    public function getTranslationsAsArray(
        array $select = ['*'],
        array $filters = []
    )
    {
        $tmp = collect();

//        Cache::tags(CacheKeyEnum::Translations->value)
//            ->rememberForever(
//                cache_key(CacheKeyEnum::Translations->value, $select, $filters),
//                fn() => Translation::query()
//                    ->select($select)
//                    ->filter($filters)
//                    ->when(!array_key_exists('sort', $filters),
//                        fn(Builder $b) => $b->orderBy('id')
//                    )
//                    ->getQuery()
//                    ->get()
//                    ->map(function ($i) use (&$tmp){
//                        $tmp->put($i->key, $i->text);
//                    })
//                    ->toArray()
//            );

            Translation::query()
            ->select($select)
            ->filter($filters)
            ->when(!array_key_exists('sort', $filters),
                fn(Builder $b) => $b->orderBy('id')
            )
            ->getQuery()
            ->get()
            ->map(function ($i) use (&$tmp){
                $tmp->put($i->key, $i->text);
            })
            ->toArray();

        return $tmp->toArray();
    }
}


