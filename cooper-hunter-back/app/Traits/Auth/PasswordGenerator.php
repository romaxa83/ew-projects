<?php

namespace App\Traits\Auth;

use App\Models\Users\User;
use Illuminate\Support\Str;

trait PasswordGenerator
{
    public function createNewPassword(int $length = User::MIN_LENGTH_PASSWORD): string
    {
        $digitsCount = 2;

        if ($length < $digitsCount) {
            $length = $digitsCount + 1;
        }

        $source = Str::lower(Str::random($length - $digitsCount));

        $digits = substr(str_shuffle('1234567890'), 0, $digitsCount);

        return str_shuffle($source.$digits);
    }
}
