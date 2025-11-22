<?php


namespace App\ModelFilters\Saas\CompanyRegistration;


use EloquentFilter\ModelFilter;

class CompanyRegistrationFilter extends ModelFilter
{
    public function order(string $value): void
    {
        $this->orderBy($value, request('order_type') ?? 'asc');
    }
}
