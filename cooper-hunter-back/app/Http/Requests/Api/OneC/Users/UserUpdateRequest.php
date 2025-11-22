<?php

namespace App\Http\Requests\Api\OneC\Users;

use App\Http\Requests\BaseFormRequest;
use App\Models\Locations\State;
use App\Permissions\Users\UserUpdatePermission;
use App\Rules\ExistsLanguages;
use App\Rules\NameRule;
use Illuminate\Validation\Rule;

class UserUpdateRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can(UserUpdatePermission::KEY);
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', new NameRule('first_name')],
            'last_name' => ['required', 'string', new NameRule('last_name')],
            'state_id' => ['nullable', 'int', Rule::exists(State::class, 'id')],
            'license' => ['nullable', 'string'],
            'email' => ['required', 'string', 'email:filter'],
            'phone' => ['nullable', 'string'],
            'lang' => ['nullable', 'string', 'min:2', 'max:3', new ExistsLanguages()],
            'guid' => ['nullable', 'string', 'uuid']
        ];
    }
}
