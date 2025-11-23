<?php

namespace WezomCms\Catalog\Http\Controllers\Admin;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use WezomCms\Catalog\Http\Requests\Admin\CategoryRequest;
use WezomCms\Catalog\ModelFilters\CategoryFilter;
use WezomCms\Catalog\Models\Category;
use WezomCms\Core\Contracts\ButtonInterface;
use WezomCms\Core\Contracts\Filter\FilterFieldInterface;
use WezomCms\Core\Foundation\Helpers;
use WezomCms\Core\Http\Controllers\AbstractCRUDController;
use WezomCms\Core\Settings\AdminLimit;
use WezomCms\Core\Settings\Fields\AbstractField;
use WezomCms\Core\Settings\MetaFields\SeoFields;
use WezomCms\Core\Settings\MultilingualGroup;
use WezomCms\Core\Settings\SiteLimit;
use WezomCms\Core\Traits\SettingControllerTrait;

class CategoriesController extends AbstractCRUDController
{
    use SettingControllerTrait;

    protected $model = Category::class;

    protected $view = 'cms-catalog::admin.categories';

    protected $paginate = false;

    protected $routeName = 'admin.categories';

    protected $request = CategoryRequest::class;

    protected function title(): string
    {
        return __('cms-catalog::admin.categories.Categories');
    }

    /**
     * @param  Builder  $query
     * @param  Request  $request
     */
    protected function selectionIndexResult($query, Request $request)
    {
        $query->sorting();
    }

    /**
     * @param $result
     * @param  array  $viewData
     * @return array
     */
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

    /**
     * @param  Category  $obj
     * @param  array  $viewData
     * @return array
     */
    protected function createViewData($obj, array $viewData): array
    {
        return [
            'tree' => $this->buildTree(),
            'categoriesTree' => Category::getForSelect(function ($query) use ($obj) {
                if ($obj->parent_id) {
                    $query->where('id', '<>', $obj->id);
                }
            }),
            'selectedCategories' => old('CATEGORIES', []),
        ];
    }

    /**
     * @param  Category  $obj
     * @param  array  $viewData
     * @return array
     */
    protected function editViewData($obj, array $viewData): array
    {
        return [
            'tree' => $this->buildTree($obj->id),
            'categoriesTree' => Category::getForSelect(function ($query) use ($obj) {
                if ($obj->parent_id) {
                    $query->where('id', '<>', $obj->id);
                }
            }),
            'selectedCategories' => old('CATEGORIES', $obj->similarCategories()->pluck('id')->toArray()),
        ];
    }

    /**
     * @param  Category  $obj
     * @param  Request  $request
     */
    protected function afterSuccessfulSave($obj, Request $request)
    {
        if ($obj->has('similarCategories')) {
            $obj->similarCategories()->sync($request->get('CATEGORIES', []));
        }
    }

    /**
     * @param  array  $groupedItems
     * @param  int  $maxDepth
     * @param  null  $parentId
     * @param  int  $depth
     * @return array
     */
    private function generateTree(array $groupedItems, int $maxDepth, $parentId = null, $depth = 0)
    {
        $result = [];

        if ($depth + 1 >= $maxDepth) {
            return $result;
        }

        $elements = $groupedItems[$parentId] ?? [];

        foreach ($elements as $item) {
            $result[$item->id] = str_repeat('&nbsp;', $depth * 4) . $item->name;

            $result += $this->generateTree($groupedItems, $maxDepth, $item->id, $depth + 1);
        }

        return $result;
    }

    /**
     * @param  int|null  $id
     * @return array
     */
    private function buildTree(int $id = null)
    {
        $tree = [
            '' => __('cms-catalog::admin.categories.Root'),
        ];

        $query = Category::select('id', 'parent_id');

        if ($id) {
            $query->where('id', '<>', $id);
        }

        $items = $query->whereDoesntHave('products')
            ->sorting()
            ->get();

        $groupedItems = Helpers::groupByParentId($items);

        $maxDepth = config('cms.catalog.categories.max_depth', 100);

        $tree += $this->generateTree($groupedItems, $maxDepth);

        return $tree;
    }

    /**
     * @return array|AbstractField[]|MultilingualGroup[]
     * @throws \Exception
     */
    protected function settings(): array
    {
        return [
            SiteLimit::make()
                ->setName(__('cms-catalog::admin.categories.Site categories limit at page'))
                ->setKey('categories_limit'),
            SiteLimit::make()
                ->setName(__('cms-catalog::admin.categories.Site root categories limit at menu'))
                ->setKey('categories_menu_limit'),
            SiteLimit::make()
                ->setName(__('cms-catalog::admin.categories.Site products limit at page')),
            SeoFields::make('Categories'),
            AdminLimit::make()->setName(__('cms-catalog::admin.categories.Number of root entries in the admin panel')),
        ];
    }

    /**
     * @param $action
     * @return array
     */
    protected function addParametersToRedirect($action): array
    {
        if ($action === ButtonInterface::ACTION_SAVE_AND_CREATE) {
            return ['parent_id' => app('request')->get('parent_id')]; // Restore parent_id
        }

        return [];
    }
}
