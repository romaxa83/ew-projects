<?php

namespace App\Repositories\Companies;

use App\Models\Companies\ShippingAddress;
use App\Repositories\AbstractRepository;
use Illuminate\Database\Eloquent\Builder;

final class ShippingAddressRepository extends AbstractRepository
{
    public function modelQuery(): Builder
    {
        return ShippingAddress::query();
    }
}
