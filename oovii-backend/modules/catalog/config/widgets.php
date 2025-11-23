<?php

use WezomCms\Catalog\Dashboards;
use WezomCms\Catalog\Widgets;

return [
    'widgets' => [
        // Admin
        'catalog:brand-with-model' => Widgets\Admin\BrandWithModel::class,
        // Site
        'catalog:viewed' => Widgets\Site\Viewed::class,
        'catalog:same-products' => Widgets\Site\SameProducts::class,
        'catalog:expanded-categories-tree' => Widgets\Site\ExpandedCategoriesTree::class,
        'catalog:expanded-multiple-categories' => Widgets\Site\ExpandedMultipleCategories::class,
        'catalog:menu' => Widgets\Site\Menu::class,
        'catalog:menu-tree' => Widgets\Site\MenuTree::class,
        'catalog:novelty' => Widgets\Site\Novelty::class,
        'catalog:popular' => Widgets\Site\Popular::class,
        'catalog:sale' => Widgets\Site\Sale::class,
        'catalog:search' => Widgets\Site\Search::class,
        'catalog:buy-with-this-product' => Widgets\Site\BuyWithThisProduct::class,
        'catalog:main-categories' => Widgets\Site\MainCategories::class,
    ],
    'dashboards' => [
        Dashboards\CategoriesDashboard::class,
        Dashboards\ProductsDashboard::class,
        Dashboards\PublishedProductsDashboard::class,
        Dashboards\CollectionsDashboard::class,
        //Dashboards\AvailableProductsDashboard::class,
    ],
];
