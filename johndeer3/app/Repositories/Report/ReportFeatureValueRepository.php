<?php

namespace App\Repositories\Report;

use App\Abstractions\AbstractRepository;
use App\Models\Report\Feature\ReportFeatureValue;
use Illuminate\Database\Eloquent\Builder;

class ReportFeatureValueRepository extends AbstractRepository
{
    public function query(): Builder
    {
        return ReportFeatureValue::query();
    }
}
