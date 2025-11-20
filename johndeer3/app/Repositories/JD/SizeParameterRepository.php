<?php

namespace App\Repositories\JD;

use App\Abstractions\AbstractRepository;
use App\Models\JD\SizeParameters;
use Illuminate\Database\Eloquent\Builder;

class SizeParameterRepository extends AbstractRepository
{
    public function query(): Builder
    {
        return SizeParameters::query();
    }
}

