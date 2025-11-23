<?php

namespace WezomCms\Catalog\Widgets\Site;

use WezomCms\Catalog\Models\Category;
use WezomCms\Core\Foundation\Widgets\AbstractWidget;

class MainCategories extends AbstractWidget
{
    /**
     * @return array|null
     */
    public function execute(): ?array
    {
        $categories = Category::published()
            ->sorting()
            ->whereShowOnMain(true)
            ->get();

        if ($categories->isEmpty()) {
            return null;
        }

        return compact('categories');
    }
}
