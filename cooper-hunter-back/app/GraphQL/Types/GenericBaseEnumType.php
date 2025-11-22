<?php

namespace App\GraphQL\Types;

abstract class GenericBaseEnumType extends BaseEnumType
{
    public const BY_KEYS = false;

    public function attributes(): array
    {
        $attributes = [];

        if (defined(static::class . '::ENUM_CLASS')) {
            /** @var \Core\Enums\BaseEnum $class */
            $class = static::ENUM_CLASS;

            $values = !static::BY_KEYS ? $class::getValues() : $class::getKeys();

            $attributes = [
                'values' => collect($values)
                    ->mapWithKeys(static fn(string $type) => [$type => $type])
                    ->toArray()
            ];
        }

        return array_merge(
            parent::attributes(),
            $attributes
        );
    }
}
