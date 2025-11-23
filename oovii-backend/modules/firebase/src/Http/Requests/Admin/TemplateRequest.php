<?php

namespace WezomCms\Firebase\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use WezomCms\Core\Traits\LocalizedRequestTrait;

class TemplateRequest extends FormRequest
{
    use LocalizedRequestTrait;

    public function rules(): array
    {
        return $this->localizeRules(
            [
                'title' => 'required|string|max:255',
                'text' => 'required|string|max:500',
            ],
            [
                'active' => 'required',
            ]
        );
    }

    public function attributes(): array
    {
        return $this->localizeAttributes(
            [
                'title' => __('cms-firebase::admin.template.form.title'),
                'text' => __('cms-firebase::admin.template.form.text'),
            ],
            [
                'active' => __('cms-core::admin.layout.Published'),
            ]
        );
    }
}

