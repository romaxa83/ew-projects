<?php

declare(strict_types=1);

namespace Core\Traits\GraphQL\Types;

use Core\GraphQL\Helpers\GqlTypeDescription;
use Core\GraphQL\Helpers\GqlTypeFieldsReader;
use Core\GraphQL\Helpers\GqlTypeName;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\NullableType;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;

trait GqlBaseGenericTrait
{
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

    abstract public function toType(): Type|InputObjectType;

    public function __get(string $name)
    {
        if ($name === 'name') {
            return $this->readGqlTypeName();
        }

        if ($name === 'description') {
            return $this->readGqlTypeDescription();
        }

        return null;
    }

    public function __set(string $name, $value): void
    {
    }

    protected function readGqlTypeDescription(): ?string
    {
        return GqlTypeDescription::readGqlTypeDescription($this);
    }

    public function __isset(string $name): bool
    {
        return in_array($name, ['name', 'description'], true);
    }

    protected function readGqlTypeFields(): array
    {
        return GqlTypeFieldsReader::readGqlTypeFields($this);
    }
}
