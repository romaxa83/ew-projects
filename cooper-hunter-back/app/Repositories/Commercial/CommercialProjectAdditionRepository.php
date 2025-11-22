<?php

namespace App\Repositories\Commercial;

use App\Models\Commercial\CommercialProjectAddition;
use App\Repositories\AbstractRepository;
use Illuminate\Database\Eloquent\Builder;

final class CommercialProjectAdditionRepository extends AbstractRepository
{
    public function modelQuery(): Builder
    {
        return CommercialProjectAddition::query();
    }
}
