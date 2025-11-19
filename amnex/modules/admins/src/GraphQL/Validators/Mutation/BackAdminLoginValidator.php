<?php

namespace Wezom\Admins\GraphQL\Validators\Mutation;

use Nuwave\Lighthouse\Validation\Validator;
use Wezom\Admins\Rules\LoginAdmin;

final class BackAdminLoginValidator extends Validator
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email:filter', 'max:60'],
            'password' => ['required', 'string', 'min:8', new LoginAdmin($this->args->toArray())],
            'remember' => ['required', 'boolean'],
        ];
    }
}
