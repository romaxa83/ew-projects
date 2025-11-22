<?php

namespace App\Repositories\Catalog\Labels;

use App\Models\Catalog\Labels\Label;
use App\Repositories\AbstractRepository;
use Illuminate\Database\Eloquent\Builder;

final class LabelRepository extends AbstractRepository
{
    public function modelQuery(): Builder
    {
        return Label::query();
    }
}
