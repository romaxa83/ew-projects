<?php

namespace App\GraphQL\Types\Orders;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Catalog\Products\ProductType;
use App\GraphQL\Types\Enums\Orders\OrderStatusTypeEnum;
use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\Projects\ProjectType;
use App\GraphQL\Types\Technicians\TechnicianType;
use App\Models\Orders\Order;
use GraphQL\Type\Definition\Type;

class OrderType extends BaseType
{
    public const NAME = 'OrderType';
    public const MODEL = Order::class;

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'status' => [
                    'type' => OrderStatusTypeEnum::nonNullType()
                ],
                'project' => [
                    'type' => ProjectType::type(),
                    'is_relation' => true
                ],
                'product' => [
                    'type' => ProductType::Type(),
                    'is_relation' => true,
                ],
                'technician' => [
                    'type' => TechnicianType::Type(),
                    'is_relation' => true
                ],
                'serial_number' => [
                    'type' => Type::string(),
                ],
                'first_name' => [
                    'type' => NonNullType::string(),
                ],
                'last_name' => [
                    'type' => NonNullType::string(),
                ],
                'phone' => [
                    'type' => NonNullType::string(),
                ],
                'comment' => [
                    'type' => Type::string()
                ],
                'parts' => [
                    'type' => OrderPartType::nonNullList(),
                    'is_relation' => true,
                ],
                'shipping' => [
                    'type' => OrderShippingType::nonNullType(),
                    'is_relation' => true
                ],
                'payment' => [
                    'type' => OrderPaymentType::nonNullType(),
                    'is_relation' => true
                ]
            ]
        );
    }
}
