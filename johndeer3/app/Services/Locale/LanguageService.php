<?php

namespace App\Services\Locale;

use App\DTO\Locale\LanguageDTO;
use App\Models\Languages;

class LanguageService
{
    public function create(LanguageDTO $dto): Languages
    {
        $model = new Languages();
        $model->name = $dto->name;
        $model->native = $dto->native;
        $model->slug = $dto->slug;
        $model->locale = $dto->locale;
        $model->default = $dto->default;

        $model->save();

        return $model;
    }
}

