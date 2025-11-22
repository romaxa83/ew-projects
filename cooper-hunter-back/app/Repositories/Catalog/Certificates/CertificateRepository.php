<?php

namespace App\Repositories\Catalog\Certificates;

use App\Models\Catalog\Certificates\Certificate;
use App\Repositories\AbstractRepository;
use Illuminate\Database\Eloquent\Builder;

final class CertificateRepository extends AbstractRepository
{
    public function modelQuery(): Builder
    {
        return Certificate::query();
    }
}

