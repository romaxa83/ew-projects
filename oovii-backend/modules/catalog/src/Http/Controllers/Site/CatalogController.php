<?php

namespace WezomCms\Catalog\Http\Controllers\Site;

use WezomCms\Catalog\Models\Category;
use WezomCms\Core\Http\Controllers\SiteController;
use WezomCms\Core\Traits\LoadMoreTrait;

class CatalogController extends SiteController
{
    use LoadMoreTrait;

    /**
     * @return mixed
     * @throws \Throwable
     */
    public function __invoke()
    {
        $settings = settings('categories.site', []);

        $categories = Category::published()
            ->whereNull('parent_id')
            ->sorting()
            ->paginate(array_get($settings, 'categories_limit', 10));

        // Render
        return $this->viewLoadMore(
            $categories,
            function () use ($categories) {
                return view('cms-catalog::site.partials.categories-list', ['result' => $categories]);
            },
            function () use ($categories, $settings) {
                $this->addBreadcrumb(array_get($settings, 'name'), route('catalog'));

                $this->seo()->fill($settings, false);

                return view('cms-catalog::site.category.categories', ['result' => $categories]);
            }
        );
    }
}
