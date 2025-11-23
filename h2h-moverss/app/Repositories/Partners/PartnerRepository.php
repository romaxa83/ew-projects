<?php

namespace App\Repositories\Partners;

use App\Models\Partners\Partner;

class PartnerRepository
{
    public function forSelect(): array
    {
        return Partner::query()
            ->select([
                'id',
                'name'
            ])
            ->get()
            ->pluck('name', 'id')
            ->toArray()
            ;
    }
}
