<?php

namespace App\Traits;

use App\Models\Users\User;
use Illuminate\Support\Str;

trait GenerateRandomPassword
{
    public static function getPassword(): string
    {
        return env('RANDOM_PASSWORD', false)
            ? 'password1'
            : Str::random(User::MIN_LENGTH_PASSWORD) . random_int(1, 50)
            ;
    }
}
