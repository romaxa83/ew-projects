<?php

namespace App\Http\Requests\Saas\Support\Crm;

use App\Http\Requests\Saas\Support\StoreMessageRequest;
use App\Models\Admins\Admin;
use App\Models\Users\User;
use App\Traits\ValidationRulesTrait;

class StoreRequest extends StoreMessageRequest
{
    use ValidationRulesTrait;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        if ($this->user(Admin::GUARD) !== null) {
            return false;
        }

        return $this->user(User::GUARD) === null || $this->user(User::GUARD)->can('support-requests create');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return array_merge(
            [
                'user_name' => [
                    'required',
                    'string',
                    'min:2',
                    'max:255'
                ],
                'user_email' => [
                    'required',
                    'email:rfc'
                ],
                'user_phone' => [
                    'required',
                    $this->USAPhone()
                ],
                'subject' => [
                    'required',
                    'string',
                    'min:2',
                    'max:255'
                ]
            ],
            parent::rules()
        );
    }
}
