<?php

namespace WezomCms\Firebase\Repositories;

use Illuminate\Database\Eloquent\Builder;
use WezomCms\Core\Repositories\AbstractRepository;
use WezomCms\Firebase\Models\Template;

class TemplateRepository extends AbstractRepository
{
    protected function query(): Builder
    {
        return Template::query();
    }
}
