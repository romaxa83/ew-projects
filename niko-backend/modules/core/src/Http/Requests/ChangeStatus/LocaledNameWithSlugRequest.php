<?php

namespace WezomCms\Core\Http\Requests\ChangeStatus;

use Illuminate\Foundation\Http\FormRequest;
use WezomCms\Core\Traits\LocalizedRequestTrait;

class LocaledNameWithSlugRequest extends FormRequest
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
                'name' => 'nullable|string|max:255|required_if:{locale}.published,1',
                'slug' => 'nullable|string|max:255|required_if:{locale}.published,1',
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
        $attributes = [];

        foreach (app('locales') as $locale => $language) {
            $attributes[$locale . '.published'] = __('cms-core::admin.layout.Published');
            $attributes[$locale . '.name'] = __('cms-core::admin.layout.Name');
            $attributes[$locale . '.slug'] = __('cms-core::admin.layout.Slug');
        }

        return $attributes;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'required_if' => __('cms-core::admin.layout.The attribute field is required'),
        ];
    }
}
