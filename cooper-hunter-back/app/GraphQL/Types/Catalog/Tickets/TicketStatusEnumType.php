<?php

namespace App\GraphQL\Types\Catalog\Tickets;

use App\Enums\Tickets\TicketStatusEnum;
use App\GraphQL\Types\GenericBaseEnumType;
use Core\GraphQL\Types\Enums\GraphQLTypeCastedEnum;
use GraphQL\Type\Definition\Type as GraphqlType;

class TicketStatusEnumType extends GenericBaseEnumType
{
    public const NAME = 'TicketStatusEnumType';
    public const ENUM_CLASS = TicketStatusEnum::class;

    public function toType(): GraphqlType
    {
        return new GraphQLTypeCastedEnum($this->toArray());
    }
}
