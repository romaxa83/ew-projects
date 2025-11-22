<?php

namespace App\GraphQL\InputTypes\Orders;

use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Products\ProductSerialNumber;
use App\Models\Projects\Project;
use App\Rules\Orders\OrderProjectRule;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;

class OrderInput extends OrderShippingInput
{
    public const NAME = 'OrderInput';

    public function fields(): array
    {
        return array_merge(
            [
                'project_id' => [
                    'type' => Type::id(),
                    'rules' => [
                        'nullable',
                        'integer',
                        Rule::exists(Project::class, 'id'),
                        new OrderProjectRule()
                    ]
                ],
                'serial_number' => [
                    'type' => NonNullType::string(),
                    'rules' => [
                        'required',
                        'string',
                        Rule::exists(ProductSerialNumber::class, 'serial_number')
                    ],
                ],
                'parts' => [
                    'type' => OrderPartsInput::nonNullList(),
                    'rules' => [
                        'required',
                        'array',
                    ]
                ],
                'comment' => [
                    'type' => Type::string()
                ]
            ],
            parent::fields()
        );
    }
}
