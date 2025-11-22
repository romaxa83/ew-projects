<?php

namespace App\Dto\Orders\Parts;

use App\Entities\Order\Parts\EcommerceClientEntity;
use App\Foundations\Entities\Locations\AddressEntity;
use App\Models\Inventories\Inventory;

class OrderEcomDto
{
    public null|string|int $customerId;
    public ?string $deliveryType;
    public string $paymentMethod;
    public bool $withTaxExemption;

    public ?AddressEntity $deliveryAddress;
    public ?AddressEntity $billingAddress;
    public ?EcommerceClientEntity $client;

    /** @var array<int, ItemDto> */
    public array $items = [];

    public static function byArgs(array $data): self
    {
        $self = new self();

        $self->customerId = $data['customer_id'] ?? null;
        $self->deliveryType = $data['delivery_type'];
        $self->deliveryAddress = isset($data['delivery_address']) && !empty($data['delivery_address'])
            ? AddressEntity::make($data['delivery_address'])
            : null
        ;
        $self->billingAddress = isset($data['billing_address']) && !empty($data['billing_address'])
            ? AddressEntity::make($data['billing_address'])
            : null
        ;
        $self->client = isset($data['client']) && !empty($data['client'])
            ? EcommerceClientEntity::make($data['client'])
            : null
        ;

        foreach ($data['items'] ?? [] as $item){
            $inventory = Inventory::query()
                ->select(['price_retail'])
                ->where('id', $item['inventory_id'])
                ->first();
            $item['price'] = $inventory->price_retail;

            $self->items[] = ItemDto::byArgs($item);
        }

        $self->paymentMethod = $data['payment_method'];
        $self->withTaxExemption = $data['with_tax_exemption'];

        return $self;
    }
}
