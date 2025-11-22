<?php

namespace App\Http\Requests\Api\OneC\Catalog\Categories;

use App\Models\Catalog\Categories\Category;
use Illuminate\Validation\Rule;

class CategoryUpdateRequest extends CategoryCreateRequest
{
    public function rules(): array
    {
        $rules = parent::rules();

        unset($rules['slug'], $rules['guid']);

        $rules['slug'] = ['required', 'string', Rule::unique(Category::class, 'slug')->ignore($this->category)];

        return $rules;
    }
}
