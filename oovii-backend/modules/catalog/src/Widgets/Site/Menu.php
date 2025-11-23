<?php

namespace WezomCms\Catalog\Widgets\Site;

use WezomCms\Catalog\Models\Category;
use WezomCms\Core\Foundation\Widgets\AbstractWidget;

class Menu extends AbstractWidget
{
    /**
     * A list of models that, when changed, will clear the cache of this widget.
     *
     * @var array
     */
    public static $models = [Category::class];

    /**
     * @return array|null
     */
    public function execute(): ?array
    {
        $categories = Category::published()
            ->whereNull('parent_id')
            ->sorting()
            ->get();

        if (!($this->parameter('force_render', false)) && $categories->isEmpty()) {
            return null;
        }

        return compact('categories');
    }
}
