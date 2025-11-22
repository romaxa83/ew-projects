<?php

namespace App\Repositories\Customers;

use App\Foundations\Models\BaseModel;
use App\Foundations\Repositories\BaseEloquentRepository;
use App\Models\Customers\Address;

final readonly class CustomerAddressRepository extends BaseEloquentRepository
{
    protected function modelClass(): string
    {
        return Address::class;
    }

    public function getById(int $id): BaseModel|Address
    {
        return $this->getBy(['id' => $id],
            withException: true,
            exceptionMessage: __("exceptions.customer.address.not_found"));
    }
}
