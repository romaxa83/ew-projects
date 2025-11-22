<?php

namespace App\Foundations\Entities\Locations;

use App\Foundations\ValueObjects\BaseValueObject;
use App\Foundations\ValueObjects\Phone;
use JsonException;

class AddressEntity
{
    public string $first_name;
    public string $last_name;
    public ?string $company;
    public string $address;
    public string $city;
    public string $state;
    public string $zip;
    public Phone $phone;
    public bool $save;
    public ?int $customer_address_id;

    public static function make(?array $arr): ?self
    {
        if(!$arr) return null;

        $self = new self();

        $self->first_name = $arr['first_name'];
        $self->last_name = $arr['last_name'];
        $self->company = $arr['company'] ?? null;
        $self->address = $arr['address'];
        $self->city = $arr['city'];
        $self->state = $arr['state'];
        $self->zip = $arr['zip'];
        $self->phone = new Phone($arr['phone']);
        $self->save = $arr['save'] ?? false;
        $self->customer_address_id = $arr['customer_address_id'] ?? null;

        return $self;
    }


    public function getFullAddress(): string
    {
        return $this->address . ', ' . $this->city . ', ' . $this->state . ' ' . $this->zip;
    }

    public function isIllinois(): bool
    {
        $zips = ['60001', '62999'];

        return is_in_range($this->zip, $zips[0], $zips[1]);
    }

    /** @throws JsonException */
    public function toJson($options = 0): string
    {
        return json_encode($this->jsonSerialize(), JSON_THROW_ON_ERROR | $options);
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function toArray(): array
    {
        $tmp = get_object_vars($this);

        foreach ($tmp as $key => $value) {
            if ($value instanceof BaseValueObject) {
                $tmp[$key] = $value->getValue();
            }
        }

        return $tmp;
    }
}
