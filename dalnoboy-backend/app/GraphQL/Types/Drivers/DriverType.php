<?php

namespace App\GraphQL\Types\Drivers;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Clients\ClientType;
use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\PhoneType;
use App\Models\Drivers\Driver;
use GraphQL\Type\Definition\Type;

class DriverType extends BaseType
{
    public const NAME = 'DriverType';
    public const MODEL = Driver::class;

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
                'email' => [
                    'type' => Type::string(),
                ],
                'phone' => [
                    'type' => NonNullType::string(),
                    'description' => 'Default phone',
                    'is_relation' => false,
                    'selectable' => false,
                    'resolve' => fn(Driver $driver) => $driver->phone->phone,
                ],
                'phones' => [
                    'type' => PhoneType::nonNullList(),
                    'description' => 'All phones list including default',
                    'is_relation' => true,
                ],
                'comment' => [
                    'type' => Type::string(),
                ],
                'is_moderated' => [
                    'type' => NonNullType::boolean(),
                ],
                'active' => [
                    'type' => NonNullType::boolean(),
                ],
                'client' => [
                    'type' => ClientType::type(),
                    'is_relation' => true,
                ],
            ]
        );
    }
}
