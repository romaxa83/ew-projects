<?php

namespace WezomCms\Catalog\Widgets\Site;

use WezomCms\Catalog\Models\Category;
use WezomCms\Core\Foundation\Helpers;
use WezomCms\Core\Foundation\Widgets\AbstractWidget;

class ExpandedCategoriesTree extends AbstractWidget
{
    /**
     * @return array|null
     */
    public function execute(): ?array
    {
        /** @var Category|null $currentCategory */
        $currentCategory = $this->parameter('currentCategory');

        if ($currentCategory) {
            $ids = $currentCategory->getAllRootsCategoriesId();

            $defaultIndex = end($ids);

            $ids[] = $currentCategory->id;

            $dbCategories = Category::published()
                ->whereIn('parent_id', $ids)
                ->sorting()
                ->get();
        } else {
            // Render all tree
            $dbCategories = Category::published()
                ->sorting()
                ->get();
            $defaultIndex = null;
        }

        $categories = Helpers::groupByParentId($dbCategories);

        if (empty($categories)) {
            return null;
        }

        return compact('categories', 'defaultIndex', 'currentCategory');
    }
}
