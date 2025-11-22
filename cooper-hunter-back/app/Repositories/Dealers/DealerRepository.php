<?php

namespace App\Repositories\Dealers;

use App\Models\Dealers\Dealer;
use App\Repositories\AbstractRepository;
use Illuminate\Database\Eloquent\Builder;

final class DealerRepository extends AbstractRepository
{
    public function modelQuery(): Builder
    {
        return Dealer::query();
    }
}
