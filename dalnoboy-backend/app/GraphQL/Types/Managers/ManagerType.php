<?php


namespace App\GraphQL\Types\Managers;


use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Locations\RegionType;
use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\PhoneType;
use App\Models\Managers\Manager;
use GraphQL\Type\Definition\Type;

class ManagerType extends BaseType
{
    public const NAME = 'ManagerType';
    public const MODEL = Manager::class;

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'first_name' => [
                    'type' => NonNullType::string(),
                ],
                'last_name' => [
                    'type' => NonNullType::string(),
                ],
                'second_name' => [
                    'type' => Type::string(),
                ],
                'region' => [
                    'type' => RegionType::nonNullType(),
                    'is_relation' => true,
                ],
                'city' => [
                    'type' => NonNullType::string(),
                ],
                'phone' => [
                    'type' => NonNullType::string(),
                    'description' => 'Default phone',
                    'is_relation' => false,
                    'selectable' => false,
                    'resolve' => fn(Manager $manager) => $manager->phone->phone,
                ],
                'phones' => [
                    'type' => PhoneType::nonNullList(),
                    'description' => 'All phones list including default',
                    'is_relation' => true,
                ],
            ]
        );
    }
}
