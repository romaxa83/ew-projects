<?php

namespace App\Casts;

use App\ValueObjects\Token;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;

class TokenCast implements CastsAttributes
{

    public function get($model, string $key, $value, array $attributes): ?Token
    {
        if (is_null($value)) {
            return null;
        }

        return new Token(
            $attributes[$key],
            CarbonImmutable::createFromFormat('Y-m-d H:i:s', $attributes[$key . '_expires'])
        );
    }

    public function set($model, string $key, $value, array $attributes)
    {
        if (!is_null($value) && !$value instanceof Token) {
            throw new InvalidArgumentException('The given value is not an Token instance.');
        }

        return [
            $key => $value->getValue(),
            $key . '_expires' => $value->getExpires()
        ];
    }
}
