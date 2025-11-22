<?php

namespace App\Rules;

use App\Models\Admins\Admin;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class LoginAdmin implements Rule
{
    public function __construct(
        protected array $args
    ) {
    }

    public function passes($attribute, $value): bool
    {
        if (!$admin = Admin::where('email', $this->args['username'])->first()) {
            return false;
        }

        return Hash::check($this->args['password'], $admin->password);
    }

    public function message(): string
    {
        return __('auth.failed');
    }
}
