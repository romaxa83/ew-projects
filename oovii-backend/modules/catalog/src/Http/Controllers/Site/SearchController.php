<?php

namespace WezomCms\Catalog\Http\Controllers\Site;

use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use WezomCms\Catalog\Filter\Exceptions\IncorrectUrlParameterException;
use WezomCms\Catalog\Filter\Exceptions\NeedRedirectException;
use WezomCms\Catalog\Filter\Factory\UrlBuilderFactory;
use WezomCms\Catalog\Filter\Filter;
use WezomCms\Catalog\Filter\Handlers\CategoryWithSubCategoriesHandler;
use WezomCms\Catalog\Filter\Handlers\CostHandler;
use WezomCms\Catalog\Filter\Handlers\SpecificationHandler;
use WezomCms\Catalog\Filter\SelectionHandlers\KeywordSearch;
use WezomCms\Catalog\Filter\Sort;
use WezomCms\Catalog\Models\Category;
use WezomCms\Catalog\Models\Product;
use WezomCms\Catalog\ViewModels\SearchViewModel;
use WezomCms\Core\Foundation\JsResponse;
use WezomCms\Core\Http\Controllers\SiteController;

class SearchController extends SiteController
{
    /**
     * @param  Request  $request
     * @return mixed|void
     * @throws \Throwable
     */
    public function index(Request $request)
    {
        try {
            // Search
            $query = $request->get('search');
            if (is_array($query)) {
                $query = array_first($query);
            }
            $search = new KeywordSearch($query);

            // Empty query
            if (!$search->hasWords()) {
                $this->seo()->noIndex('noindex, nofollow');

                // Render
                return view('cms-catalog::site.search.index', [
                    'query' => $query,
                    'products' => collect(),
                ]);
            }

            if ($categorySlug = $request->get('category')) {
                $currentCategory = Category::publishedWithSlug($categorySlug)->first();
            } else {
                $currentCategory = null;
            }

            $filter = new Filter(new Product(), UrlBuilderFactory::search());

            // Sorting
            $sort = new Sort($request);

            // Set handlers
            $filter->addHandlers([
                $search,
                new CostHandler($filter),
                new SpecificationHandler($filter),
                $sort,
            ]);
            if ($currentCategory) {
                $filter->addHandler((new CategoryWithSubCategoriesHandler($filter))->setCategory($currentCategory));
            }

            $filter->start();

            /** @var Paginator|Product[] $products */
            $products = $filter->getFilteredItems(settings('search.search.limit', 10));

            return (new SearchViewModel($query, $search, $currentCategory, $request, $products, $filter, $sort))
                ->view('cms-catalog::site.search.index');
        } catch (NeedRedirectException $e) {
            return redirect($e->getUrl());
        } catch (IncorrectUrlParameterException $e) {
            abort(404);
        }
    }

    /**
     * @param  Request  $request
     * @return JsResponse
     * @throws \Throwable
     */
    public function liveSearch(Request $request)
    {
        $query = $request->get('query', '');
        if (!$query || is_array($query)) {
            return JsResponse::make()->success(false)->reset(false);
        }

        $search = new KeywordSearch($query);

        if ($search->hasWords()) {
            $limit = 3;

            // Search products
            $productsQuery = Product::query();

            $search->apply($productsQuery);

            $result = $productsQuery->limit($limit)
                ->sorting()
                ->get();

            // Search categories
            $categoryQuery = Category::query();

            $search->apply($categoryQuery);

            $categoryQuery->limit($limit)
                ->sorting()
                ->get()
                ->each(function (Category $category) use ($result) {
                    $result->push($category);
                });

            if ($result->isNotEmpty()) {
                $html = view('cms-catalog::site.search.live', compact('result', 'query'))->render();
            } else {
                $html = '';
            }
        } else {
            $html = '';
        }

        return JsResponse::make()->reset(false)->set('html', $html);
    }
}
