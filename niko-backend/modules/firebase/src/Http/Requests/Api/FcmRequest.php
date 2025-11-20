<?php

namespace WezomCms\Firebase\Http\Requests\Api;
use Illuminate\Foundation\Http\FormRequest;
use WezomCms\Core\Http\Requests\ChangeStatus\RequiredIfMessageTrait;
use WezomCms\Core\Rules\PhoneMask;
use WezomCms\Core\Traits\LocalizedRequestTrait;

class FcmRequest extends FormRequest
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
                'token' => 'required|string',
                'userId' => ['required', 'integer', 'exists:users,id']
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
            []
        );
    }
}


