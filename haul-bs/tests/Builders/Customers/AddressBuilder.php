<?php

namespace Tests\Builders\Customers;

use App\Foundations\ValueObjects\Phone;
use App\Models\Customers\Address;
use App\Models\Customers\Customer;
use Tests\Builders\BaseBuilder;

class AddressBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return Address::class;
    }

    public function first_name(string $value): self
    {
        $this->data['first_name'] = $value;
        return $this;
    }

    public function last_name(string $value): self
    {
        $this->data['last_name'] = $value;
        return $this;
    }

    public function phone(string $value): self
    {
        $this->data['phone'] = new Phone($value);
        return $this;
    }

    public function customer(Customer $model): self
    {
        $this->data['customer_id'] = $model->id;
        return $this;
    }

    public function default(bool $value = true): self
    {
        $this->data['is_default'] = $value;
        return $this;
    }

    public function sort(int $value): self
    {
        $this->data['sort'] = $value;
        return $this;
    }

    public function ecomm(bool $value = true): self
    {
        $this->data['from_ecomm'] = $value;
        return $this;
    }
}
