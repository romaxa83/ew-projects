<?php

namespace App\Repositories\Orders\Dealer;

use App\Models\Orders\Dealer\PackingSlip;
use App\Repositories\AbstractRepository;
use Illuminate\Database\Eloquent\Builder;

final class PackingSlipRepository extends AbstractRepository
{
    public function modelQuery(): Builder
    {
        return PackingSlip::query();
    }
}
