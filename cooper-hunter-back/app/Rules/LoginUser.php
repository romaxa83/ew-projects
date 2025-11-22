<?php

namespace App\Rules;

use App\Models\Users\User;
use Illuminate\Support\Facades\Hash;

class LoginUser extends LoginAdmin
{
    public function passes($attribute, $value): bool
    {
        if (!$admin = User::query()->where('email', $this->args['username'])->first()) {
            return false;
        }

        return Hash::check($this->args['password'], $admin->password);
    }
}
