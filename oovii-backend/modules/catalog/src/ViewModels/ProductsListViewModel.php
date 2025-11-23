<?php

namespace WezomCms\Catalog\ViewModels;

use Artesaos\SEOTools\Traits\SEOTools;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Spatie\ViewModels\ViewModel;
use Symfony\Component\HttpFoundation\Response;
use WezomCms\Catalog\Filter\Contracts\FilterInterface;
use WezomCms\Catalog\Filter\Contracts\SortInterface;

abstract class ProductsListViewModel extends ViewModel
{
    use SEOTools;

    /**
     * @var Request
     */
    public $request;
    /**
     * @var LengthAwarePaginator
     */
    public $products;
    /**
     * @var FilterInterface
     */
    public $filter;
    /**
     * @var SortInterface
     */
    public $sort;
    /**
     * @var \Illuminate\Support\Collection|iterable
     */
    public $searchForm;

    /**
     * ProductsListViewModel constructor.
     * @param  Request  $request
     * @param  Paginator  $products
     * @param  FilterInterface  $filter
     * @param  SortInterface  $sort
     */
    public function __construct(Request $request, $products, FilterInterface $filter, SortInterface $sort)
    {
        $this->request = $request;
        $this->products = $products;
        $this->filter = $filter;
        $this->sort = $sort;

        if ($this->request->expectsJson()) {
            $this->searchForm = $this->request->header('x-filter') ? $this->filter->buildWidgetData() : collect();
        } else {
            $this->searchForm = $this->filter->buildWidgetData();
        }
    }

    /**
     * Base url of current page.
     *
     * @return string
     */
    abstract public function baseUrl(): string;

    /**
     * @return iterable
     */
    public function selected(): iterable
    {
        return $this->filter->getSelectedAttributes();
    }

    /**
     * @param  Request  $request
     * @return Response
     */
    public function toResponse($request): Response
    {
        $response = parent::toResponse($request);

        // Disable browser caching for json response.
        if ($response instanceof JsonResponse) {
            $response->setCache(['no_cache' => true, 'no_store' => true, 'must_revalidate' => true]);
        }

        return $response;
    }

    /**
     * @return Collection
     * @throws \Throwable
     */
    protected function items(): Collection
    {
        if ($this->request->expectsJson()) {
            $pagination = view('cms-catalog::site.partials.products-pagination', ['products' => $this->products])
                ->render();

            $response = collect([
                'pagination' => $pagination,
                'url' => $this->request->fullUrl(),
                'moreUrl' => $this->products->nextPageUrl(),
                'more' => $this->products->hasMorePages(),
                'titleDocument' => $this->seo()->getTitle(),
                'langSwitcher' => app('widget')->show('ui:lang-switcher'),
            ]);

            // If with filter
            if ($this->request->header('x-filter')) {
                $response->put('filter', view('cms-catalog::site.partials.filter', [
                    'baseUrl' => $this->baseUrl(),
                    'searchForm' => $this->searchForm,
                ])->render());

                $content = view('cms-catalog::site.partials.main-catalog', [
                    'products' => $this->products,
                    'selected' => $this->selected(),
                    'baseUrl' => $this->baseUrl(),
                    'sort' => $this->sort,
                    'queryParams' => method_exists($this, 'queryParams') ? $this->queryParams() : null,
                    'filter' => $this->filter,
                ])->render();

                $response->put('content', $content);
            } else {
                $response->put(
                    'products',
                    view('cms-catalog::site.partials.products-list', ['products' => $this->products])->render()
                );
            }

            return $response;
        }

        return parent::items();
    }
}
