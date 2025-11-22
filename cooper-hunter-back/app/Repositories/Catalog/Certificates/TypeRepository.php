<?php

namespace App\Repositories\Catalog\Certificates;

use App\Models\Catalog\Certificates\CertificateType;
use App\Repositories\AbstractRepository;
use Illuminate\Database\Eloquent\Builder;

final class TypeRepository extends AbstractRepository
{
    public function modelQuery(): Builder
    {
        return CertificateType::query();
    }
}
