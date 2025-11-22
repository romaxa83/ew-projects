<?php

namespace App\Repositories\Orders\Dealer;

use App\Models\Orders\Dealer\Item;
use App\Repositories\AbstractRepository;
use Illuminate\Database\Eloquent\Builder;

final class OrderItemRepository extends AbstractRepository
{
    public function modelQuery(): Builder
    {
        return Item::query();
    }
}
