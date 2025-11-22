<?php

namespace App\Http\Requests\Api\OneC\Users;

use App\Http\Requests\BaseFormRequest;
use App\Permissions\Users\UserCreatePermission;
use App\Rules\MemberUniqueEmailRule;
use App\Rules\MemberUniquePhoneRule;
use App\Rules\NameRule;
use App\Rules\PasswordRule;

class UserCreateRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can(UserCreatePermission::KEY);
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', new NameRule('first_name')],
            'last_name' => ['required', 'string', new NameRule('last_name')],
            'email' => ['required', 'string', 'email:filter', new MemberUniqueEmailRule()],
            'phone' => ['nullable', 'string', new MemberUniquePhoneRule()],
            'password' => ['required', 'string', new PasswordRule(), 'confirmed'],
            'guid' => ['nullable', 'string', 'uuid']
        ];
    }
}
