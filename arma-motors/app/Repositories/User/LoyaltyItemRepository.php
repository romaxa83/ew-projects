<?php

namespace App\Repositories\User;

use App\Models\User\Loyalty\LoyaltyItem;
use App\Repositories\AbstractRepository;

class LoyaltyItemRepository extends AbstractRepository
{
    public function query()
    {
        return LoyaltyItem::query();
    }
}

