<?php

namespace App\GraphQL\Types\Orders;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Locations\CountryType;
use App\GraphQL\Types\Locations\StateType;
use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\Orders\Deliveries\OrderDeliveryTypeType;
use App\Models\Commercial\CommercialProject;
use App\Models\Locations\Country;
use App\Models\Locations\State;
use App\Models\Orders\OrderShipping;
use GraphQL\Type\Definition\Type;

class OrderShippingType extends BaseType
{
    public const NAME = 'OrderShippingType';
    public const MODEL = OrderShipping::class;

    public function fields(): array
    {
        return [
            'order_id' => [
                'type' => NonNullType::id(),
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
            'address_first_line' => [
                'type' => NonNullType::string(),
            ],
            'address_second_line' => [
                'type' => Type::string(),
            ],
            'city' => [
                'type' => NonNullType::string(),
            ],
            // todo после удаления поля state и country, убрать resolve
            'state' => [
                'type' => StateType::type(),
                'is_relation' => true,
                'resolve' => fn (OrderShipping $m): State => $m->state()->first()
            ],
            'country' => [
                'type' => CountryType::type(),
                'is_relation' => true,
                'resolve' => fn (OrderShipping $m): Country => $m->country()->first()
            ],
            'zip' => [
                'type' => NonNullType::string(),
            ],
            'trk_number' => [
                'type' => OrderShippingTrkNumberType::type(),
                'resolve' => fn (OrderShipping $shipping) => !$shipping->trk_number ? null : ['number' => $shipping->trk_number],
                'is_relation' => false
            ],
            'deliveryType' => [
                'type' => OrderDeliveryTypeType::nonNullType(),
                'is_relation' => true
            ]
        ];
    }
}
