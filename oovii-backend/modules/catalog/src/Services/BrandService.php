<?php

namespace WezomCms\Catalog\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use WezomCms\Catalog\Models\Brand;
use WezomCms\Catalog\Models\BrandTranslation;

class BrandService
{
    public function createFromImport(array $data): Brand
    {
        $model = new Brand();
        $model->slug = Str::slug(Arr::get($data, "translations.ru" ));
        $model->save();

        foreach(app('locales') as $slug => $language){
            $t = new BrandTranslation();
            $t->brand_id = $model->id;
            $t->locale = $slug;
            $t->name = Arr::get($data, "translations." . $slug);
            $t->save();
        }

        return $model;
    }
}
