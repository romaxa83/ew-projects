<?php

namespace WezomCms\Catalog\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use WezomCms\Catalog\Models\Collections\Collection;
use WezomCms\Core\Traits\LocalizedRequestTrait;

class CollectionRequest extends FormRequest
{
    use LocalizedRequestTrait;

    public function rules(): array
    {
        return $this->localizeRules(
            [
                'name' => 'required|string|max:255',
            ],
            [
                'published' => ['nullable'],
                'type' => ['required'],
                'moderator_id' => ['required', 'exists:administrators,id'],
//                'category_id' => ['required'],
                'start_at' => ['required', 'date'],
                'end_at' => ['nullable', 'date', 'after:start_at', 'required_if:time_counter,' . Collection::END_AT_COUNTER],
                'time_counter' => ['required', Rule::in([Collection::START_AT_COUNTER, Collection::END_AT_COUNTER])],
            ]
        );
    }

    public function attributes(): array
    {
        return $this->localizeAttributes(
            [
                'name' => __('cms-catalog::admin.brands.Name'),
            ],
            [
                'published' => __('cms-core::admin.layout.Published'),
                'type' => __('cms-core::admin.collection.type.title'),
                'moderator_id' => __('cms-core::admin.moderator.moderator'),
//                'category_id' => __('cms-core::admin.collection.category.name'),
                'start_at' => __('cms-core::admin.collection.start_at'),
                'end_at' => __('cms-core::admin.collection.end_at'),
            ]
        );
    }
}

