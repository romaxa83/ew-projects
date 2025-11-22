<?php

namespace App\GraphQL\Types\Enums\Menu;

use App\Enums\Menu\MenuBlockEnum;
use App\GraphQL\Types\GenericBaseEnumType;
use Core\GraphQL\Types\Enums\GraphQLTypeCastedEnum;
use GraphQL\Type\Definition\Type as GraphqlType;

class MenuBlockTypeEnum extends GenericBaseEnumType
{
    public const NAME = 'MenuBlockTypeEnum';
    public const DESCRIPTION = 'List of all available menu block';
    public const ENUM_CLASS = MenuBlockEnum::class;

    public function toType(): GraphqlType
    {
        return new GraphQLTypeCastedEnum($this->toArray());
    }
}
