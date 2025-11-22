<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Support\Facades\Crypt;

class EncryptValueCast implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes): string
    {
        return Crypt::decryptString($value);
    }

    public function set($model, string $key, $value, array $attributes): string
    {
        return Crypt::encryptString($value);
    }
}
