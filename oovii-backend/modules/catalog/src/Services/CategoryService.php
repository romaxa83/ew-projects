<?php

namespace WezomCms\Catalog\Services;

use Illuminate\Support\Arr;
use WezomCms\Catalog\Models\Category;
use WezomCms\Catalog\Models\CategoryTranslation;

class CategoryService
{
    public function createFromImport(array $data): Category
    {
        $model = new Category();
        $model->save();

        foreach(app('locales') as $slug => $language){
            $t = new CategoryTranslation();
            $t->category_id = $model->id;
            $t->locale = $slug;
            $t->name = Arr::get($data, "translations." . $slug);
            $t->save();
        }

        return $model;
    }
}


