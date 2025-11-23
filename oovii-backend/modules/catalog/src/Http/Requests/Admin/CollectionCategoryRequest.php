<?php

namespace WezomCms\Catalog\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use WezomCms\Core\Traits\LocalizedRequestTrait;

class CollectionCategoryRequest extends FormRequest
{
    use LocalizedRequestTrait;

    public function rules(): array
    {
        return $this->localizeRules(
            [
                'name' => 'required|string|max:255',
            ],
            [
                'published' => ['required'],
            ]
        );
    }

    public function attributes(): array
    {
        return $this->localizeAttributes(
            [
                'name' => __('cms-catalog::admin.collection.category.name'),
            ],
            [
                'published' => __('cms-core::admin.layout.Published'),
            ]
        );
    }
}


