<?php

namespace WezomCms\Catalog\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use WezomCms\Catalog\Models\Specifications\Specification;
use WezomCms\Catalog\Models\Specifications\SpecificationTranslation;

class SpecificationService
{
    public function createFromImport(array $data): Specification
    {
        $model = new Specification();
        $model->multiple = false;
        $model->slug = Str::slug(Arr::get($data, 'translations.ru'));
        $model->save();

        $tr = new SpecificationTranslation();
        $tr->specification_id = $model->id;
        $tr->locale = 'ru';
        $tr->name = Arr::get($data, 'translations.ru');
        $tr->save();

        $tk = new SpecificationTranslation();
        $tk->specification_id = $model->id;
        $tk->locale = 'kk';
        $tk->name = Arr::get($data, 'translations.kk');
        $tk->save();

        return $model;
    }
}
