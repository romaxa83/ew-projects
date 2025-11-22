<?php

namespace App\Dto\Orders\Dealer\Onec;

use App\Dto\Orders\Dealer\OrderItemDto;
use App\Enums\Orders\Dealer\DeliveryType;
use App\Enums\Orders\Dealer\OrderType;
use App\Enums\Orders\Dealer\PaymentType;
use App\Traits\SimpleHasher;

class OrderCreateDto
{
    use SimpleHasher;

    public DeliveryType $deliveryType;
    public PaymentType $paymentType;
    public OrderType $type;
    public string $guid;
    public string $companyGuid;
    public string $shippingAddressID;
    public string $po;
    public ?string $comment;

    public ?string $term;
    public ?float $tax;
    public ?float $shippingPrice;
    public ?float $total;
    public ?float $totalDiscount;
    public ?float $totalWithDiscount;

    /** @var array<OrderCreateDto> */
    public array $items = [];

    public ?string $hash;

    public static function byArgs(array $args): self
    {
        $dto = new self();
        $dto->guid = $args['guid'];
        $dto->companyGuid = $args['company_guid'];
        $dto->po = $args['po'];
        $dto->deliveryType = DeliveryType::fromValue($args['delivery_type']);
        $dto->paymentType = PaymentType::fromValue($args['payment_type']);
        $dto->type = OrderType::fromValue(data_get($args, 'type', OrderType::ORDINARY));
        $dto->comment = data_get($args, 'comment');

        $dto->shippingAddressID = data_get($args, 'shipping_address_id');
        $dto->term = data_get($args, 'term');

        $dto->tax = data_get($args, 'tax', 0);
        $dto->shippingPrice = data_get($args, 'shipping_price', 0);
        $dto->total = data_get($args, 'total', 0);
        $dto->totalDiscount = data_get($args, 'total_discount', 0);
        $dto->totalWithDiscount = data_get($args, 'total_with_discount', 0);

        $dto->hash = self::getHash($args);

        foreach (data_get($args, 'products', []) as $item){
            $dto->items[] = OrderItemDto::byArgs($item);
        }

        return $dto;
    }

    private static function getHash(array $data): string
    {
        return self::hash(data_get($data, 'products', []));
    }
}

