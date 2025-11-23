<?php

namespace WezomCms\Catalog\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use WezomCms\Core\Traits\LocalizedRequestTrait;

class CatalogSeoTemplateRequest extends FormRequest
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
                'title' => 'nullable|string|max:255',
                'h1' => 'nullable|string|max:255',
                'keywords' => 'nullable|string|max:255',
                'description' => 'nullable|string|max:255',
                'text' => 'nullable|string|max:16777215',
            ],
            [
                'name' => 'required|string|max:255',
                'published' => 'required',
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
                'title' => __('cms-catalog::admin.catalog-seo-templates.Title'),
                'h1' => __('cms-catalog::admin.catalog-seo-templates.H1'),
                'keywords' => __('cms-catalog::admin.catalog-seo-templates.Keywords'),
                'description' => __('cms-catalog::admin.catalog-seo-templates.Description'),
                'text' => __('cms-catalog::admin.catalog-seo-templates.Seo text'),
            ],
            [
                'name' => __('cms-catalog::admin.catalog-seo-templates.Name'),
                'published' => __('cms-core::admin.layout.Published'),
            ]
        );
    }
}
