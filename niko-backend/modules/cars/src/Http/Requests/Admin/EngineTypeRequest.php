<?php

namespace WezomCms\Cars\Http\Requests\Admin;
use Illuminate\Foundation\Http\FormRequest;
use WezomCms\Core\Http\Requests\ChangeStatus\RequiredIfMessageTrait;
use WezomCms\Core\Rules\PhoneMask;
use WezomCms\Core\Traits\LocalizedRequestTrait;

class EngineTypeRequest extends FormRequest
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
            [
                'name' => 'required|string',
            ],
            []
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
                'name' => __('cms-cars::admin.Name'),
            ],
            []
        );
    }
}

