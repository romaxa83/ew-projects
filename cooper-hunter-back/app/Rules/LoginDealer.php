<?php

namespace App\Rules;

use App\Models\Dealers\Dealer;
use Illuminate\Support\Facades\Hash;

class LoginDealer extends LoginAdmin
{
    public function passes($attribute, $value): bool
    {
        if (!$model = Dealer::query()->where('email', $this->args['username'])->first()) {
            return false;
        }

        return Hash::check($this->args['password'], $model->password);
    }
}
