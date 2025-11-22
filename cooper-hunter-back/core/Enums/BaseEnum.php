<?php

namespace Core\Enums;

use BenSampo\Enum\Enum;
use Core\Testing\GraphQL\Scalar\EnumValue;
use Illuminate\Validation\Rule;

abstract class BaseEnum extends Enum
{
    public static function listToString(string $delimiter = ', '): string
    {
        return implode($delimiter, self::list());
    }

    public static function list(): array
    {
        return self::getValues();
    }

    public static function ruleIn(): string
    {
        return Rule::in(self::getValues());
    }

    /**
     * @param bool $byKey
     * @return EnumValue
     * @see \Core\Testing\GraphQL\QueryBuilder\GraphQLQuery::isScalar()
     * @see \Core\Testing\GraphQL\QueryBuilder\GraphQLQuery::toGraphQLValue()
     */
    public function toScalar(bool $byKey = false): EnumValue
    {
        return new EnumValue(!$byKey ? $this->value : $this->key);
    }
}
