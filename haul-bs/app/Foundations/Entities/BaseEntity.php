<?php

namespace App\Foundations\Entities;

use App\Foundations\ValueObjects\BaseValueObject;
use JsonException;

class BaseEntity
{
    /** @throws JsonException */
    public function toJson($options = 0): string
    {
        return json_encode($this->jsonSerialize(), JSON_THROW_ON_ERROR | $options);
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function toArray(): array
    {
        $tmp = get_object_vars($this);

        foreach ($tmp as $key => $value) {
            if ($value instanceof BaseValueObject) {
                $tmp[$key] = $value->getValue();
            }
        }

        return $tmp;
    }
}
