<?php

namespace App\Rules\User;

use App\Models\Users\User;
use Illuminate\Contracts\Validation\Rule;

class UserAsRole implements Rule
{
    public function __construct(protected string $role)
    {}

    public function passes($attribute, $value): bool
    {
        /** @var $user User */
        $user = User::find($value);

        return $user->role->isRole($this->role);
    }

    public function message(): string
    {
        return __('validation.custom.user.role.not_belong_to_role', [
            'role_name' => __('permissions.roles.' . $this->role)
        ]);
    }
}
