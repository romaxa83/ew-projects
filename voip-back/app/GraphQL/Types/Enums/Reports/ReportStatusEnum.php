<?php

namespace App\GraphQL\Types\Enums\Reports;

use App\Enums;
use App\GraphQL\Types\GenericBaseEnumType;
use Core\GraphQL\Types\Enums\GraphQLTypeCastedEnum;
use GraphQL\Type\Definition\Type as GraphqlType;

class ReportStatusEnum extends GenericBaseEnumType
{
    public const NAME = 'ReportStatusEnumType';
    public const ENUM_CLASS = Enums\Reports\ReportStatus::class;

    public function toType(): GraphqlType
    {
        return new GraphQLTypeCastedEnum($this->toArray());
    }
}


