<?php

namespace WezomCms\Services\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use WezomCms\Core\Traits\LocalizedRequestTrait;

class ServiceRequest extends FormRequest
{
    use LocalizedRequestTrait;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = ['published' => 'required'];
        if (config('cms.services.services.use_groups')) {
            $rules['service_group_id'] = 'required|exists:service_groups,id';
        }

        return $this->localizeRules(
            [
                'name' => 'required|string|max:255',
                'text' => 'nullable|string|max:16777215',
            ],
            $rules
        );
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        $attributes = ['published' => __('cms-core::admin.layout.Published')];
        if (config('cms.services.services.use_groups')) {
            $attributes['service_group_id'] = __('cms-services::admin.Group');
        }

        return $this->localizeAttributes(
            [
                'name' => __('cms-services::admin.Name'),
                'slug' => __('cms-core::admin.layout.Slug'),
                'text' => __('cms-services::admin.Text'),
                'title' => __('cms-core::admin.seo.Title'),
                'h1' => __('cms-core::admin.seo.H1'),
                'keywords' => __('cms-core::admin.seo.Keywords'),
                'description' => __('cms-core::admin.seo.Description'),
            ],
            $attributes
        );
    }
}
