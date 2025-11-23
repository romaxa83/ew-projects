<?php

namespace WezomCms\Catalog\Widgets\Site;

use WezomCms\Catalog\Models\Category;
use WezomCms\Core\Foundation\Helpers;
use WezomCms\Core\Foundation\Widgets\AbstractWidget;

class ExpandedMultipleCategories extends AbstractWidget
{
    /**
     * @return array|null
     */
    public function execute(): ?array
    {
        $matchedCategories = Category::published()
            ->whereKey($this->parameter('ids'))
            ->get();

        if ($matchedCategories->isEmpty()) {
            return null;
        }

        $parentIds = $matchedCategories->map(function (Category $category) {
            $rootIds = $category->getAllRootsCategoriesId();
            $rootIds[] = $category->id;

            return $rootIds;
        })->flatten()->unique();

        $dbCategories = Category::published()
            ->whereKey($parentIds)
            ->sorting()
            ->get();

        $categories = Helpers::groupByParentId($dbCategories);

        if (empty($categories)) {
            return null;
        }

        return compact('categories');
    }
}
