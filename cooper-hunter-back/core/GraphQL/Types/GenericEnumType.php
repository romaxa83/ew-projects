<?php

namespace Core\GraphQL\Types;

use Core\Enums\BaseEnum;
use Core\GraphQL\Types\Enums\GraphQLTypeCastedEnum;
use Core\Traits\GraphQL\Types\BaseTypeTrait;
use GraphQL\Type\Definition\NullableType;
use GraphQL\Type\Definition\Type as GraphqlType;
use Rebing\GraphQL\Support\EnumType;

abstract class GenericEnumType extends EnumType implements NullableType
{
    use BaseTypeTrait;

    public function attributes(): array
    {
        $attributes = [
            'name' => static::NAME,
        ];

        if (defined(static::class . '::DESCRIPTION')) {
            $attributes['description'] = static::DESCRIPTION;
        }

        if (defined(static::class . '::ENUM_CLASS')) {
            /** @var BaseEnum $class */
            $class = static::ENUM_CLASS;

            $values = $class::getValues();

            $attributes['values'] = collect($values)
                ->mapWithKeys(static fn(string $type): array => [$type => $type])
                ->toArray();
        }

        return array_merge(
            parent::attributes(),
            $attributes
        );
    }

    public function toType(): GraphqlType
    {
        return new GraphQLTypeCastedEnum($this->toArray());
    }
}
