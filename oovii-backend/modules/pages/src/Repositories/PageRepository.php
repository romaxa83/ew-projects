<?php

namespace WezomCms\Pages\Repositories;

use Illuminate\Database\Eloquent\Builder;
use WezomCms\Core\Repositories\AbstractRepository;
use WezomCms\Pages\Models\Page;

class PageRepository extends AbstractRepository
{
    protected function query(): Builder
    {
        return Page::query();
    }
}
