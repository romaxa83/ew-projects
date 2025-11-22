<?php

namespace App\Foundations\Modules\Localization\Services;

use App\Foundations\Enums\CacheKeyEnum;
use App\Foundations\Modules\Localization\Dto\LanguageDto;
use App\Foundations\Modules\Localization\Models\Language;
use Illuminate\Support\Facades\Cache;

final readonly class LanguageService
{
    public function create(LanguageDto $dto): Language
    {
        $model = new Language();

        $model->name = $dto->name;
        $model->slug = $dto->slug;
        $model->native = $dto->native;
        $model->default = $dto->default;
        $model->active = $dto->active;
        $model->sort = $dto->sort;

        $model->save();

        Cache::tags(CacheKeyEnum::Languages->value)->flush();

        return $model;
    }
}





