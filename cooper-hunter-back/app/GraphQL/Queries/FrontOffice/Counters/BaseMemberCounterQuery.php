<?php

namespace App\GraphQL\Queries\FrontOffice\Counters;

use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\Type;

abstract class BaseMemberCounterQuery extends BaseQuery
{
    public function args(): array
    {
        return [];
    }

    public function type(): Type
    {
        return Type::int();
    }
}
