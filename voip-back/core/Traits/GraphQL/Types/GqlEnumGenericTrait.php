<?php

declare(strict_types=1);

namespace Core\Traits\GraphQL\Types;

use Core\GraphQL\Helpers\GqlTypeDescription;
use Core\GraphQL\Helpers\GqlTypeName;
use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\NullableType;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;

trait GqlEnumGenericTrait
{
    public function __construct($enumValue = null)
    {
        if (is_null($enumValue)) {
            $enumValue = static::getValues()[0];
        }

        parent::__construct($enumValue);
    }

    public static function nonNullType(): NonNull|Type
    {
        return Type::nonNull(static::type());
    }

    public static function type(): Type|NullableType
    {
        return GraphQL::type(
            self::getGqlTypeInstance()->readGqlTypeName()
        );
    }

    protected function readGqlTypeName(): string
    {
        return GqlTypeName::readGqlTypeName($this);
    }

    protected static function getGqlTypeInstance(): static
    {
        return new static();
    }

    public function toType(): Type
    {
        return new EnumType(
            [
                'name' => $this->readGqlTypeName(),
                'description' => $this->readGqlTypeDescription(),
                'values' => $this->readGqlTypeEnumValues(),
            ]
        );
    }

    protected function readGqlTypeDescription(): ?string
    {
        return GqlTypeDescription::readGqlTypeDescription($this);
    }

    protected function readGqlTypeEnumValues(): array
    {
        return collect(self::getValues())
            ->mapWithKeys(static fn(string $type) => [$type => $type])
            ->toArray();
    }

    public function __get(string $name)
    {
        if ($name === 'name') {
            return $this->readGqlTypeName();
        }

        if ($name === 'description') {
            return $this->readGqlTypeDescription();
        }

        if ($name === 'values') {
            return $this->readGqlTypeEnumValues();
        }

        return null;
    }

    public function __set(string $name, $value): void
    {
    }

    public function __isset(string $name): bool
    {
        return in_array($name, ['name', 'description', 'values'], true);
    }
}
