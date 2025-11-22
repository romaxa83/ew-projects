<?php

namespace App\GraphQL\Types\Companies;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Locations\CountryType;
use App\GraphQL\Types\Locations\StateType;
use App\GraphQL\Types\NonNullType;
use App\Models\Companies\Contact;
use GraphQL\Type\Definition\Type;

class ContactType extends BaseType
{
    public const NAME = 'companyContactType';
    public const MODEL = Contact::class;

    public function fields(): array
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::id()),
            ],
            'name' => [
                'type' => NonNullType::string(),
            ],
            'phone' => [
                'type' => NonNullType::string(),
            ],
            'email' => [
                'type' => NonNullType::string(),
            ],
            'country' => [
                'type' => CountryType::type(),
                'is_relation' => true,
            ],
            'state' => [
                'type' => StateType::type(),
                'is_relation' => true,
            ],
            'city' => [
                'type' => NonNullType::string(),
            ],
            'address_line_1' => [
                'type' => NonNullType::string(),
            ],
            'address_line_2' => [
                'type' => Type::string(),
            ],
            'zip' => [
                'type' => NonNullType::string(),
            ],
            'po_box' => [
                'type' => Type::string(),
            ],
        ];
    }
}


