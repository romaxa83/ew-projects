<?php

namespace App\Dto\Orders\Parts;

use App\Foundations\Entities\Locations\AddressEntity;
use App\Models\Customers\Address;

class OrderDto
{
    public string|int|null $customerId;
    public string $source;
    public float|null $deliveryCost;
    public ?string $deliveryType;
    public ?string $paymentMethod;
    public ?string $paymentTerms;
    public bool $withTaxExemption;

    /** @var array<int, ShippingMethodDto> */
    public array $shippingMethods = [];

    public ?AddressEntity $deliveryAddress = null;
    public ?AddressEntity $billingAddress;

    public static function byArgs(array $data): self
    {
        $self = new self();

        $self->customerId = $data['customer_id'] ?? null;
        $self->source = $data['source'];
        $self->deliveryType = $data['delivery_type'] ?? null;

        if(isset($data['delivery_address']) && !empty($data['delivery_address'])){

            if(isset($data['delivery_address']['customer_address_id'])){
                $id = $data['delivery_address']['customer_address_id'];
                $address = Address::find($id);

                $self->deliveryAddress = AddressEntity::make([
                    'first_name' => $address->first_name,
                    'last_name' => $address->last_name,
                    'company' => $address->company_name,
                    'address' => $address->address,
                    'city' => $address->city,
                    'state' => $address->state,
                    'zip' => $address->zip,
                    'phone' => $address->phone->getValue(),
                    'customer_address_id' => $id,
                ]);

            } else {
                $self->deliveryAddress = AddressEntity::make($data['delivery_address']);
            }

        }

        $self->billingAddress = isset($data['billing_address']) && !empty($data['billing_address'])
            ? AddressEntity::make($data['billing_address'])
            : null
        ;

        foreach ($data['shipping_methods'] ?? [] as $method){
            $self->shippingMethods[] = ShippingMethodDto::byArgs($method);
        }

        $self->paymentMethod = $data['payment']['method'] ?? null;
        $self->paymentTerms = $data['payment']['terms'] ?? null;
        $self->withTaxExemption = $data['payment']['with_tax_exemption'] ?? false;
        $self->deliveryCost = $data['delivery_cost'] ?? 0;

        return $self;
    }
}
