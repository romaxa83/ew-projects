<?php

namespace WezomCms\Catalog\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use WezomCms\Catalog\Http\Requests\Admin\CollectionCategoryRequest;
use WezomCms\Catalog\ModelFilters\CategoryFilter;
use WezomCms\Catalog\Models\Collections\Category;
use WezomCms\Core\Contracts\Filter\FilterFieldInterface;
use WezomCms\Core\Foundation\Helpers;
use WezomCms\Core\Http\Controllers\AbstractCRUDController;

class CollectionCategoryController extends AbstractCRUDController
{
    protected $model = Category::class;

    protected $view = 'cms-catalog::admin.collection-categories';

    protected $paginate = false;

    protected $routeName = 'admin.collection-categories';

    protected $request = CollectionCategoryRequest::class;

    public function __construct()
    {
        parent::__construct();
    }

    protected function title(): string
    {
        return __('cms-catalog::admin.collection.category.many');
    }

    protected function selectionIndexResult($query, Request $request)
    {
        $query->sorting();
    }

    protected function indexViewData($result, array $viewData): array
    {
        $request = app('request');

        $presentFilterInputs = array_filter(
            (new CategoryFilter($this->model()::query(), $request->all()))->getFields(),
            function (FilterFieldInterface $filterField) use ($request) {
                $name = $filterField->getName();

                return $name !== 'per_page' && $request->get($name) !== '' && $request->get($name) !== null;
            }
        );

        if (count($presentFilterInputs)) {
            // Render groups as one level
            $firstLevel = $result->all();
            $result = [null => $result->all()];
        } else {
            // Multidimensional tree
            $result = Helpers::groupByParentId($result);
            $firstLevel = $result[null] ?? [];
        }

        $limit = $this->getLimit($request);

        // Paginate only first level items
        $pagination = new LengthAwarePaginator(
            $firstLevel,
            count($firstLevel),
            $limit,
            null,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $paginatedResult = array_slice($firstLevel, ($pagination->currentPage() - 1) * $limit, $limit);

        return ['result' => $result, 'paginatedResult' => $paginatedResult, 'pagination' => $pagination];
    }
}


