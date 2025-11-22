<?php

namespace App\Dto\Orders\Dealer;

use App\Enums\Orders\Dealer\DeliveryType;
use App\Enums\Orders\Dealer\OrderType;
use App\Enums\Orders\Dealer\PaymentType;

class OrderDto
{
    public PaymentType $paymentType;
    public DeliveryType $deliveryType;
    public ?OrderType $type = null;
    public ?int $shippingAddressID;
    public ?int $paymentCardID;
    public ?string $comment;
    public ?string $po;

    public static function byArgs(array $args): self
    {
        $dto = new self();
        $dto->paymentType = PaymentType::fromValue(data_get($args, 'payment_type', PaymentType::NONE()));
        $dto->deliveryType = DeliveryType::fromValue(data_get($args, 'delivery_type', DeliveryType::NONE()));
        $dto->shippingAddressID = data_get($args, 'shipping_address_id');
        $dto->paymentCardID = data_get($args, 'payment_card_id');
        $dto->comment = data_get($args, 'comment');
        $dto->po = data_get($args, 'po');

        if(data_get($args, 'type')){
            $dto->type = OrderType::fromValue(data_get($args, 'type'));
        }

        return $dto;
    }
}

