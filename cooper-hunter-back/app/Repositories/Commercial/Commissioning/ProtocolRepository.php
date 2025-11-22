<?php

namespace App\Repositories\Commercial\Commissioning;

use App\Models\Commercial\Commissioning\Protocol;
use App\Repositories\AbstractRepository;
use Illuminate\Database\Eloquent\Builder;

final class ProtocolRepository extends AbstractRepository
{
    public function modelQuery(): Builder
    {
        return Protocol::query();
    }
}
