<?php

namespace App\GraphQL\Types\Enums\Menu;

use App\Enums\Menu\MenuPositionEnum;
use App\GraphQL\Types\GenericBaseEnumType;
use Core\GraphQL\Types\Enums\GraphQLTypeCastedEnum;
use GraphQL\Type\Definition\Type as GraphqlType;

class MenuPositionTypeEnum extends GenericBaseEnumType
{
    public const NAME = 'MenuPositionTypeEnum';
    public const DESCRIPTION = 'List of all available menu positions';
    public const ENUM_CLASS = MenuPositionEnum::class;

    public function toType(): GraphqlType
    {
        return new GraphQLTypeCastedEnum($this->toArray());
    }
}
