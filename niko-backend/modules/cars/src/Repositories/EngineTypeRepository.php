<?php

namespace WezomCms\Cars\Repositories;

use WezomCms\Cars\Models\EngineType;
use WezomCms\Core\Repositories\AbstractRepository;

class EngineTypeRepository extends AbstractRepository
{
    protected function query()
    {
        return EngineType::query();
    }
}
