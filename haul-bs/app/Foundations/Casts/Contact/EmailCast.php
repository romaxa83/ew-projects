<?php

namespace App\Foundations\Casts\Contact;

use App\Foundations\ValueObjects\Email;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;

class EmailCast implements CastsAttributes
{

    public function get($model, string $key, $value, array $attributes): null|Email
    {
        if(!$value){
            return null;
        }

        return new Email($attributes['email']);
    }

    public function set($model, string $key, $value, array $attributes): null|string
    {
        if (is_null($value)) {
            return $value;
        }

        if (is_string($value)) {
            $value = new Email($value);
        }

        if (!$value instanceof Email) {
            throw new InvalidArgumentException(
                __('exceptions.value_object.object_must_be_instance_class', [
                    'class' => Email::class
                ])
            );
        }

        return (string)$value;
    }
}

