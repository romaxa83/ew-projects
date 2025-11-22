<?php

namespace App\Rules\Inventories;

use App\Models\Inventories\Category;
use Illuminate\Contracts\Validation\Rule;

/**
 * если это корневая категория (parent_id = null), то при редактирование parent_id может быть - null,
 * для всех остальных категория она обязательна
 **/

class CategoryParentNullableRule implements Rule
{
    public function __construct(protected string|int $id)
    {}

    public function passes($attribute, $value): bool
    {
        if(!$value){
            $model = Category::find($this->id);
            if($model->parent_id){
                return false;
            }
        }

        return true;
    }

    public function message(): string
    {
        return __("validation.required", ["attribute" => __('validation.attributes.parent_id')]);
    }
}


