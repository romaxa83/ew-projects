<?php

namespace App\Repositories\Customers;

use App\Foundations\Models\BaseModel;
use App\Foundations\Repositories\BaseEloquentRepository;
use App\Models\Customers\Customer;

final readonly class CustomerRepository extends BaseEloquentRepository
{
    protected function modelClass(): string
    {
        return Customer::class;
    }

    public function getById(int $id): BaseModel|Customer
    {
        return $this->getBy(['id' => $id],
            withException: true,
            exceptionMessage: __("exceptions.customer.not_found"));
    }
}
