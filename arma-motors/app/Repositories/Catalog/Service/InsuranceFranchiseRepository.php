<?php

namespace App\Repositories\Catalog\Service;

use App\Models\Catalogs\Service\InsuranceFranchise;
use App\Repositories\AbstractRepository;

class InsuranceFranchiseRepository extends AbstractRepository
{
    public function query()
    {
        return InsuranceFranchise::query();
    }
}
