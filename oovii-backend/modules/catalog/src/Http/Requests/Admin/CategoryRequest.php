<?php

namespace WezomCms\Catalog\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use WezomCms\Core\Traits\LocalizedRequestTrait;

class CategoryRequest extends FormRequest
{
    use LocalizedRequestTrait;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return $this->localizeRules(
            [
                'name' => 'required|string|max:255',
//                'slug' => 'required|string|max:255',
                'text' => 'nullable|string|max:16777215',
//                'title' => 'nullable|string|max:255',
//                'h1' => 'nullable|string|max:255',
//                'keywords' => 'nullable|string|max:255',
//                'description' => 'nullable|string|max:255',
            ],
            [
                'published' => 'required',
                'parent_id' => 'nullable|int|exists:categories,id',
//                'show_on_main' => 'required',
//                'icon' => 'mimetypes:image/jpeg,image/png,image/svg+xml',
            ]
        );
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return $this->localizeAttributes(
            [
                'name' => __('cms-catalog::admin.categories.Name'),
//                'slug' => __('cms-core::admin.layout.Slug'),
                'text' => __('cms-catalog::admin.categories.Text'),
//                'title' => __('cms-core::admin.seo.Title'),
//                'h1' => __('cms-core::admin.seo.H1'),
//                'keywords' => __('cms-core::admin.seo.Keywords'),
//                'description' => __('cms-core::admin.seo.Description'),
            ],
            [
                'published' => __('cms-core::admin.layout.Published'),
                'parent_id' => __('cms-catalog::admin.categories.Parent'),
//                'show_on_main' => __('cms-catalog::admin.categories.Show on main'),
//                'icon' => __('cms-catalog::admin.categories.Icon'),
            ]
        );
    }
}
