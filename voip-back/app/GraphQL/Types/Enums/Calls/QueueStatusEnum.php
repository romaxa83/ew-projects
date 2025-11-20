<?php

namespace App\GraphQL\Types\Enums\Calls;

use App\Enums;
use App\GraphQL\Types\GenericBaseEnumType;
use Core\GraphQL\Types\Enums\GraphQLTypeCastedEnum;
use GraphQL\Type\Definition\Type as GraphqlType;

class QueueStatusEnum extends GenericBaseEnumType
{
    public const NAME = 'CallQueueStatusEnumType';
    public const ENUM_CLASS = Enums\Calls\QueueStatus::class;

    public function toType(): GraphqlType
    {
        return new GraphQLTypeCastedEnum($this->toArray());
    }
}
