<?php


namespace App\GraphQL\Types\Branches;


use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Locations\RegionType;
use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\PhoneType;
use App\Models\Branches\Branch;
use GraphQL\Type\Definition\Type;

class BranchType extends BaseType
{
    public const NAME = 'BranchType';
    public const MODEL = Branch::class;

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'name' => [
                    'type' => NonNullType::string(),
                ],
                'city' => [
                    'type' => NonNullType::string(),
                ],
                'region' => [
                    'type' => RegionType::nonNullType(),
                    'always' => ['id'],
                ],
                'address' => [
                    'type' => NonNullType::string(),
                ],
                'active' => [
                    'type' => NonNullType::boolean(),
                ],
                'phone' => [
                    'type' => NonNullType::string(),
                    'description' => 'Default phone',
                    'is_relation' => false,
                    'selectable' => false,
                    'resolve' => fn(Branch $branch) => $branch->phone->phone,
                ],
                'phones' => [
                    'type' => PhoneType::nonNullList(),
                    'description' => 'All phones list including default',
                    'is_relation' => true,
                ],
                'inspections_count' => [
                    'type' => Type::int(),
                ]
            ]
        );
    }
}
