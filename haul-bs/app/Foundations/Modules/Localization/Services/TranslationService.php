<?php

namespace App\Foundations\Modules\Localization\Services;

use App\Foundations\Enums\CacheKeyEnum;
use App\Foundations\Modules\Localization\Models\Translation;
use Illuminate\Support\Facades\Cache;

final readonly class TranslationService
{
    public function createOrUpdate(array $data): bool
    {
        Cache::tags(CacheKeyEnum::Translations->value)->flush();

        return (bool)Translation::query()->upsert($data, ['place', 'key', 'lang'], ['text']);
    }
}
