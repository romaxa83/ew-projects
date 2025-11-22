<?php

namespace App\GraphQL\InputTypes\Orders\BackOffice;

use App\Enums\Orders\OrderStatusEnum;
use App\GraphQL\Types\Enums\Orders\OrderStatusTypeEnum;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Products\ProductSerialNumber;
use App\Models\Projects\Project;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;

class OrderBackOfficeInput extends OrderShippingBackOfficeInput
{
    public const NAME = 'OrderBackOfficeInput';

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
                    ]
                ],
                'status' => [
                    'type' => OrderStatusTypeEnum::type(),
                    'defaultValue' => OrderStatusEnum::CREATED
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
                    'type' => OrderPartsBackOfficeInput::nonNullList()
                ],
                'comment' => [
                    'type' => Type::string()
                ]
            ],
            parent::fields(),
            [
                'payment' => [
                    'type' => OrderPaymentBackOfficeInput::type()
                ]
            ]
        );
    }
}
