<?php

namespace App\GraphQL\Types\Orders\Dealer;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Companies\ShippingAddressType;
use App\GraphQL\Types\Dealers\DealerType;
use App\GraphQL\Types\Enums\Orders\Dealer;
use App\GraphQL\Types\Media\MediaType;
use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\Payments\MemberPaymentCardType;
use App\Models\Orders\Dealer\Order;
use Core\Traits\Auth\AuthGuardsTrait;
use GraphQL\Type\Definition\Type;

class OrderType extends BaseType
{
    use AuthGuardsTrait;

    public const NAME = 'dealerOrderType';
    public const MODEL = Order::class;

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'guid' => [
                    'type' => Type::string(),
                ],
                'status' => [
                    'type' => Dealer\OrderStatusTypeEnum::nonNullType(),
                ],
                'type' => [
                    'type' => Dealer\OrderTypeTypeEnum::nonNullType(),
                ],
                'delivery_type' => [
                    'type' => Dealer\DeliveryTypeTypeEnum::nonNullType(),
                ],
                'payment_type' => [
                    'type' => Dealer\PaymentTypeTypeEnum::nonNullType(),
                ],
                'po' => [
                    'type' => Type::string(),
                ],
                'term' => [
                    'type' => Type::string(),
                    'selectable' => false,
                    'resolve' => fn (Order $model) => $model->term
                ],
                'comment' => [
                    'type' => Type::string(),
                ],
                'shipping_address' => [
                    'type' => ShippingAddressType::type(),
                    'alias' => 'shippingAddress',
                    'is_relation' => true,
                ],
                'payment_card' => [
                    'type' => MemberPaymentCardType::type(),
                    'alias' => 'paymentCard',
                    'is_relation' => true,
                ],
                'dealer' => [
                    'type' => DealerType::nonNullType(),
                ],
                'items' => [
                    'type' => ItemType::list()
                ],
                'total_amount' => [
                    'type' => NonNullType::float(),
                    'resolve' => fn (Order $model) => $model->total_amount
                ],
                'media' => [
                    'type' => MediaType::list(),
                    'always' => 'id',
                    'alias' => 'media',
                ],
                'is_owner' => [
                    'type' => Type::boolean(),
                    'selectable' => false,
                    'resolve' => fn (Order $model) => $model->isOwner($this->getAuthUser())
                ],
                'tax' => [
                    'type' => Type::float(),
                    'description' => 'налог, пиходит от 1с'
                ],
                'invoice' => [
                    'type' => Type::string(),
                ],
                'has_invoice' => [
                    'type' => Type::boolean(),
                ],
                'shipping_price' => [
                    'type' => Type::float(),
                    'description' => 'стоимость доставки, пиходит от 1с'
                ],
                'total' => [
                    'type' => Type::float(),
                    'description' => 'общая сумма заказа, пиходит от 1с'
                ],
                'total_discount' => [
                    'type' => Type::float(),
                    'description' => 'общую сумма скидки, пиходит от 1с'
                ],
                'total_with_discount' => [
                    'type' => Type::float(),
                    'description' => 'общая сумма заказа со скидкой, пиходит от 1с'
                ],
                'files' => [
                    'type' => Type::listOf(OrderFileFromOnecType::type()),
                    'always' => 'files',
                    'selectable' => false,
                    'is_relation' => false,
                    'description' => 'массив файлов (название и линк), который пришли от 1с'
                ],
                'error' => [
                    'type' => Type::string(),
                    'description' => 'ошибка по заявке, которая может прийти от 1с, на предмет превышения лимита или др. причины'
                ],
                'serial_numbers' => [
                    'alias' => 'serialNumbers',
                    'type' => SerialNumberType::list()
                ],
                'packing_slips' => [
                    'alias' => 'packingSlips',
                    'type' => PackingSlipType::list()
                ],
            ]
        );
    }
}
