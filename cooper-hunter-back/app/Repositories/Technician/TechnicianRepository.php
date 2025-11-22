<?php

namespace App\Repositories\Technician;

use App\Models\Technicians\Technician;
use App\Repositories\AbstractRepository;
use Illuminate\Database\Eloquent\Builder;

final class TechnicianRepository extends AbstractRepository
{
    public function modelQuery(): Builder
    {
        return Technician::query();
    }
}
