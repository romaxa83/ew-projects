<?php

namespace WezomCms\Cars\Repositories;

use WezomCms\Cars\Models\Transmission;
use WezomCms\Core\Repositories\AbstractRepository;

class TransmissionRepository extends AbstractRepository
{
    protected function query()
    {
        return Transmission::query();
    }
}
