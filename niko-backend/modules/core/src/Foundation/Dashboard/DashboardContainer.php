<?php

namespace WezomCms\Core\Foundation\Dashboard;

use Illuminate\Support\Collection;
use WezomCms\Core\Contracts\DashboardWidgetInterface;

class DashboardContainer
{
    /**
     * @var Collection
     */
    protected $items;

    public function __construct()
    {
        $this->items = new Collection();
    }

    /**
     * @param $widget
     * @return $this
     */
    public function addWidget($widget)
    {
        $this->items->push($widget);

        return $this;
    }

    /**
     * @return Collection
     */
    public function getWidgets()
    {
        return $this->items;
    }

    /**
     * @return Collection|DashboardWidgetInterface[]
     */
    public function makeWidgets()
    {
        $sort = config('cms.core.dashboard.sort', []);

        $items = $this->items->sortBy(function ($item) use ($sort) {
            if (in_array($item, $sort)) {
                return array_search($item, $sort);
            }
        });

        return $items->map(function ($className) {
            return new $className();
        });
    }
}
