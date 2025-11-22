<?php


namespace App\GraphQL\Types\Enums\Vehicles;


use App\Enums\Vehicles\VehicleFormEnum;
use App\GraphQL\Types\GenericBaseEnumType;
use Core\GraphQL\Types\Enums\GraphQLTypeCastedEnum;
use GraphQL\Type\Definition\Type as GraphqlType;

class VehicleFormEnumType extends GenericBaseEnumType
{
    public const NAME = 'VehicleFormEnumType';
    public const ENUM_CLASS = VehicleFormEnum::class;

    public function toType(): GraphqlType
    {
        return new GraphQLTypeCastedEnum($this->toArray());
    }
}
