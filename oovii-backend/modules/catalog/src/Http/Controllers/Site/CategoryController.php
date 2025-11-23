<?php

namespace WezomCms\Catalog\Http\Controllers\Site;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\Request;
use WezomCms\Catalog\Filter\Exceptions\IncorrectUrlParameterException;
use WezomCms\Catalog\Filter\Exceptions\NeedRedirectException;
use WezomCms\Catalog\Filter\Factory\UrlBuilderFactory;
use WezomCms\Catalog\Filter\Filter;
use WezomCms\Catalog\Filter\Handlers\BrandHandler;
use WezomCms\Catalog\Filter\Handlers\CategoryHandler;
use WezomCms\Catalog\Filter\Handlers\CostHandler;
use WezomCms\Catalog\Filter\Handlers\ProductFlagsHandler;
use WezomCms\Catalog\Filter\Handlers\SpecificationHandler;
use WezomCms\Catalog\Filter\Sort;
use WezomCms\Catalog\Models\CatalogSeoTemplate;
use WezomCms\Catalog\Models\Category;
use WezomCms\Catalog\Models\Product;
use WezomCms\Catalog\ViewModels\CategoryWithProductsViewModel;
use WezomCms\Core\Http\Controllers\SiteController;
use WezomCms\Core\Traits\LoadMoreTrait;
use WezomCms\Core\Traits\RecursiveBreadcrumbsTrait;

class CategoryController extends SiteController
{
    use LoadMoreTrait;
    use RecursiveBreadcrumbsTrait;

    /**
     * @param $slug
     * @param $id
     * @param  Request  $request
     * @return mixed|void
     * @throws \Throwable
     */
    public function __invoke($slug, $id, Request $request)
    {
        $category = Category::published()->findOrFail($id);

        // Redirect to new slug
        if ($category->slug !== $slug) {
            return redirect($category->getFrontUrl(), 301);
        }

        if (!$request->expectsJson()) {
            $this->addBreadcrumb(
                settings('categories.site.name', __('cms-catalog::site.catalog.Catalog')),
                route('catalog')
            );
            $this->addRecursiveBreadcrumbs($category);
        }

        // Select subcategories
        /** @var Paginator|Category[] $children */
        $children = $category->children()
            ->published()
            ->sorting()
            ->paginate(settings('categories.site.categories_limit', 10));

        // Render
        if ($children->isNotEmpty()) {
            if (!$request->expectsJson()) {
                $this->setLangSwitchers($category, 'catalog.category', ['slug' => 'slug', 'id' => 'model.id']);

                list($title, $h1, $keywords, $description) = CatalogSeoTemplate::applyDefaultTemplate(
                    $category,
                    $category->title,
                    $category->h1,
                    $category->keywords,
                    $category->description
                );

                $this->seo()
                    ->setTitle($title)
                    ->setPageName($category->name)
                    ->setH1($h1)
                    ->setDescription($description)
                    ->setSeoText($category->text)
                    ->setPrevNext($children)
                    ->metatags()
                    ->setKeywords($keywords);
            }

            // Render
            return $this->viewLoadMore(
                $children,
                function () use ($children) {
                    return view('cms-catalog::site.partials.categories-list', ['result' => $children]);
                },
                function () use ($children) {
                    return view('cms-catalog::site.category.categories', ['result' => $children]);
                }
            );
        } else {
            try {
                $filter = new Filter(new Product(), UrlBuilderFactory::category($category));

                // Sorting
                $sort = new Sort($request);

                // Set handlers
                $filter->addHandlers([
                    new BrandHandler($filter),
                    (new CategoryHandler($filter))->setCategory($category),
                    new CostHandler($filter),
                    new ProductFlagsHandler($filter),
                    new SpecificationHandler($filter),
                    $sort,
                ]);

                $filter->start();

                /** @var Paginator $products */
                $products = $filter->getFilteredItems(settings('categories.site.limit', 10));

                return (new CategoryWithProductsViewModel($category, $request, $products, $filter, $sort))
                    ->view('cms-catalog::site.category.products');
            } catch (NeedRedirectException $e) {
                return redirect($e->getUrl());
            } catch (IncorrectUrlParameterException $e) {
                abort(404);
            }
        }
    }
}
