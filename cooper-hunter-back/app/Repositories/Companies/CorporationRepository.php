<?php

namespace App\Repositories\Companies;

use App\Models\Companies\Corporation;
use App\Repositories\AbstractRepository;
use Illuminate\Database\Eloquent\Builder;

final class CorporationRepository extends AbstractRepository
{
    public function modelQuery(): Builder
    {
        return Corporation::query();
    }
}
