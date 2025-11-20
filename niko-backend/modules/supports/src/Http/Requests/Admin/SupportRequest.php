<?php

namespace WezomCms\Supports\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use WezomCms\Core\Http\Requests\ChangeStatus\RequiredIfMessageTrait;
use WezomCms\Core\Traits\LocalizedRequestTrait;
use WezomCms\Promotions\Models\Promotions;

class SupportRequest extends FormRequest
{
    use LocalizedRequestTrait;
    use RequiredIfMessageTrait;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return $this->localizeRules(
            [],
            [
                'name' => 'required|string',
                'email' => 'required|string',
                'text' => 'required|string',
                'read' => 'required',
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
            [],
            [
                'name' => __('cms-supports::admin.Name'),
                'email' => __('cms-supports::admin.Email'),
                'text' => __('cms-supports::admin.Text'),
                'read' => __('cms-supports::admin.Read')
            ]
        );
    }
}

