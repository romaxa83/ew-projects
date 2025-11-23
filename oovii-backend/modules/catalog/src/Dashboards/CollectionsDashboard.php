<?php

namespace WezomCms\Catalog\Dashboards;

use WezomCms\Catalog\Models\Collections\Collection;
use WezomCms\Core\Foundation\Dashboard\AbstractValueDashboard;

class CollectionsDashboard extends AbstractValueDashboard
{
    protected $cacheTime = 5;

    protected $ability = 'collections.view';

    public function value(): int
    {
        return Collection::published()->count();
    }

    public function description(): string
    {
        return __('cms-catalog::admin.collection.collections');
    }

    public function icon(): string
    {
        return 'fa-list';
    }

    public function url(): ?string
    {
        return route('admin.collections.index');
    }
}

