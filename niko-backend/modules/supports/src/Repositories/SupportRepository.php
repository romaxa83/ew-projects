<?php

namespace WezomCms\Supports\Repositories;

use WezomCms\Core\Repositories\AbstractRepository;
use WezomCms\Supports\Models\Support;

class SupportRepository extends AbstractRepository
{
    protected function query()
    {
        return Support::query();
    }
}
