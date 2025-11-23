<?php

namespace WezomCms\Catalog\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use WezomCms\Catalog\Models\Specifications\SpecValue;
use WezomCms\Catalog\Models\Specifications\SpecValueTranslation;

class SpecValueService
{
    public function createFromImport(array $data): SpecValue
    {
        $model = new SpecValue();
        $model->specification_id = $data['specification_id'];
        $model->slug = Str::slug($data['value']);
        $model->save();

        foreach(app('locales') as $slug => $language){
            $t = new SpecValueTranslation();
            $t->spec_value_id = $model->id;
            $t->locale = $slug;
            $t->name = $data['value'];
            $t->save();
        }

        return $model;
    }

    public function createFromImportWithTranslation(array $data): SpecValue
    {
        $model = new SpecValue();
        $model->specification_id = $data['specification_id'];
        $model->slug = Str::slug(Arr::get($data, "translations.ru"));
        $model->save();

        foreach(app('locales') as $slug => $language){
            $t = new SpecValueTranslation();
            $t->spec_value_id = $model->id;
            $t->locale = $slug;
            $t->name = Arr::get($data, "translations." . $slug);
            $t->save();
        }

        return $model;
    }
}

