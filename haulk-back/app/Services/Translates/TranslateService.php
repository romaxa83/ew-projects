<?php

namespace App\Services\Translates;

use App\Models\Translates\Translate;
use App\Models\Translates\TranslateTranslates;
use Arr;
use Exception;
use Illuminate\Support\Collection;

class TranslateService
{
    /**
     * @throws Exception
     */
    public function clear(): void
    {
        Translate::query()->delete();
    }

    public function insert(array $translates): void
    {
        $translateCollection = Collection::make($translates)->keyBy('key');

        $modelInsertable = $translateCollection->map(
            function (array $translate) {
                return Arr::only($translate, ['key']);
            }
        );

        Translate::query()->insertOrIgnore($modelInsertable->values()->toArray());

        $translateInsertable = [];

        /** @var Translate[] $translates */
        $translates = Translate::query()->pluck('key', 'id');

        foreach ($translates as $id => $model) {
            $translate = $translateCollection->get($model);

            foreach (config('languages') as ['slug' => $slug]) {
                $translateInsertable[] = [
                    'language' => $slug,
                    'text' => $translate[$slug]['text'],
                    'row_id' => $id,
                ];
            }
        }

        TranslateTranslates::query()->insertOrIgnore($translateInsertable);
    }
}
