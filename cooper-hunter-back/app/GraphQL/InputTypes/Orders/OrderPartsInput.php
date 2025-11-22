<?php

namespace App\GraphQL\InputTypes\Orders;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;
use App\Models\Orders\Categories\OrderCategory;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;

class OrderPartsInput extends BaseInputType
{
    public const NAME = 'OrderPartsInput';

    public function fields(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'description' => 'Order category id',
                'rules' => [
                    'required',
                    'integer',
                    Rule::exists(OrderCategory::class, 'id')->where('active', true)
                ]
            ],
            'description' => [
                'type' => Type::string(),
                'description' => 'Additional data about "other" category',
                'rules' => [
                    'nullable',
                    'string',
                ]
            ],
            'quantity' => [
                'type' => Type::int(),
                'description' => 'This field doesn\'t use now',
                'defaultValue' => config('orders.categories.default_quantity'),
                'rules' => [
                    'nullable',
                    'integer'
                ]
            ]
        ];
    }
}
