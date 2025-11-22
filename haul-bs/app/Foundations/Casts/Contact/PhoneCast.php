<?php

namespace App\Foundations\Casts\Contact;

use App\Foundations\ValueObjects\Phone;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;

class PhoneCast implements CastsAttributes
{

    public function get($model, string $key, $value, array $attributes): null|Phone
    {
        if(!$value){
            return null;
        }

        return new Phone($attributes['phone']);
    }

    public function set($model, string $key, $value, array $attributes): null|string
    {
        if (is_null($value)) {
            return $value;
        }

        if (is_string($value)) {
            $value = new Phone($value);
        }

        if (!$value instanceof Phone) {
            throw new InvalidArgumentException(
                __('exceptions.value_object.object_must_be_instance_class', [
                    'class' => Phone::class
                ])
            );
        }

        return (string)$value;
    }
}
