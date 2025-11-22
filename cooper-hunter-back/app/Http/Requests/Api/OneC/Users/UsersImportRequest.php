<?php

namespace App\Http\Requests\Api\OneC\Users;

use App\Http\Requests\BaseFormRequest;
use App\Permissions\Users\UserCreatePermission;
use App\Rules\MemberUniqueEmailRule;
use App\Rules\MemberUniquePhoneRule;
use App\Rules\NameRule;
use App\Rules\PasswordRule;
use App\Rules\UniqueValuesByFieldRule;

class UsersImportRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can(UserCreatePermission::KEY);
    }

    public function rules(): array
    {
        return [
            'users' => [
                'required',
                'array',
                'min:1',
                new UniqueValuesByFieldRule('email'),
                new UniqueValuesByFieldRule('phone')
            ],
            'users.*.first_name' => ['required', 'string', new NameRule('first_name')],
            'users.*.last_name' => ['required', 'string', new NameRule('last_name')],
            'users.*.email' => ['required', 'string', 'email:filter', new MemberUniqueEmailRule()],
            'users.*.phone' => ['nullable', 'string', new MemberUniquePhoneRule()],
            'users.*.password' => ['required', 'string', new PasswordRule(), 'confirmed'],
            'users.*.guid' => ['nullable', 'string', 'uuid']
        ];
    }
}
