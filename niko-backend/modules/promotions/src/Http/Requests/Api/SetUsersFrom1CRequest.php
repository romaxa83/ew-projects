<?php

namespace WezomCms\Promotions\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use WezomCms\Core\Rules\Phone;
use WezomCms\Users\Models\User;
use WezomCms\Users\Rules\EmailOrPhone;

class SetUsersFrom1CRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'ActionID' => ['required'],
            'ActionClients' => 'required|array|min:1',
            'ActionClients.*' => 'required',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [];
    }
}
