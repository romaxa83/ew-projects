<?php

namespace WezomCms\Catalog\ModelFilters;

use Auth;
use DB;
use EloquentFilter\ModelFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use WezomCms\Catalog\Models\Brand;
use WezomCms\Catalog\Models\Model;
use WezomCms\Catalog\Models\Specifications\Specification;
use WezomCms\Catalog\Repositories\CollectionRepository;
use WezomCms\Catalog\Repositories\LabelRepository;
use WezomCms\Catalog\Repositories\ProductRepository;
use WezomCms\Core\Contracts\Filter\FilterFieldInterface;
use WezomCms\Core\Contracts\Filter\FilterListFieldsInterface;
use WezomCms\Core\Filter\FilterField;
use WezomCms\Core\Foundation\Helpers;
use WezomCms\Core\Models\Administrator;
use WezomCms\Core\Models\Role;
use WezomCms\Core\Repositories\AdminRepository;

/**
 * Class ProductFilter
 * @package WezomCms\Catalog\ModelFilters
 */
class ProductFilter extends ModelFilter implements FilterListFieldsInterface
{
    protected array $brands = [];
    protected array $models = [];

    /**
     * Generate array with fields
     * @return iterable|FilterField[]
     */
    public function getFields(): iterable
    {
        /** @var Administrator $admin */
        $admin = Auth::user();
        $adminRepo = app(AdminRepository::class);
        $collectionRepo = app(CollectionRepository::class);
        $labelsRepo = app(LabelRepository::class);

        /** @var $superAdmin Administrator */
        $superAdmin = $adminRepo->getSuperAdmin(['id', 'name']);
        $providers = $adminRepo->getByRoleForSelect(Role::DEFAULT_PROVIDER);
        $providers[$superAdmin->id] = $superAdmin->name;
        $moderators = $adminRepo->getByRoleForSelect(Role::DEFAULT_MODERATOR);
        $moderators[$superAdmin->id] = $superAdmin->name;

        $result = [
            FilterField::id(),
            FilterField::makeName(),
        ];

        $result[] = FilterField::make()
            ->type(FilterField::TYPE_SELECT)
            ->options($this->getProductGroups())
            ->name('group_key')
            ->label(__('cms-catalog::admin.products.Group key'))
            ->class('js-select2');

        $result[] = FilterField::make()
            ->type(FilterField::TYPE_SELECT)
            ->options($labelsRepo->forSelect([], 'id', 'name', true, false, 'published'))
            ->name('label')
            ->label(__('cms-catalog::admin.products.Labels'))
            ->class('js-select2');

        $result[] = FilterField::make()
            ->type(FilterField::TYPE_RANGE)
            ->name('cost')
            ->step(0.01)
            ->label(__('cms-catalog::admin.products.Cost'));

        if (!$admin->onlyProvider()) {
            $result[] = FilterField::make()
                ->type(FilterField::TYPE_SELECT)
                ->options($providers)
                ->name('provider_id')
                ->label(__('cms-providers::admin.provider.Provider'))
                ->class('js-select2');
        }

        $result[] = FilterField::make()
            ->type(FilterField::TYPE_SELECT)
            ->options($moderators)
            ->name('moderator_id')
            ->label(__('cms-catalog::admin.moderator'))
            ->class('js-select2');

        $result[] = FilterField::make()
            ->type(FilterField::TYPE_SELECT)
            ->options($collectionRepo->forSelect())
            ->name('collection_id')
            ->label(__('cms-catalog::admin.collection.collection'))
            ->class('js-select2');

        $result[] = FilterField::published();

        $result[] = FilterField::make()
            ->name('published_at')
            ->label(__('cms-catalog::admin.products.Published at'))
            ->size(3)
            ->type(FilterField::TYPE_DATE_RANGE);

        return array_merge($result, $this->buildSpecificationsFilterInputs());
    }

    protected function getProductGroups(): array
    {
        $productRepo = app(ProductRepository::class);

        return $productRepo->groupKeyForFilter();
    }

    /**
     * @return array
     */
    protected function buildSpecificationsFilterInputs(): array
    {
        return Specification::with(
            [
                'specValues' => function ($query) {
                    $query->sorting();
                }
            ]
        )
            ->sorting()
            ->get()
            ->filter(
                function (Specification $specification) {
                    return $specification->name !== null;
                }
            )
            ->map(
                function (Specification $specification) {
                    return FilterField::make()
                        ->type(FilterFieldInterface::TYPE_SELECT)
                        ->label($specification->name)
                        ->name('specifications[' . $specification->id . '][]')
                        ->attributes(['multiple' => true])
                        ->class('js-select2')
                        ->options($specification->specValues->pluck('name', 'id')->all());
                }
            )->all();
    }

    /**
     * @param Request $request
     */
    public function restoreSelectedOptions(Request $request)
    {
        if ($brandId = $request->get('brand_id')) {
            $brand = Brand::find($brandId);
            if ($brand) {
                $this->brands = [$brand->id => $brand->name];
            }
        }
        if ($modelId = $request->get('model_id')) {
            $model = Model::find($modelId);
            if ($model) {
                $this->models = [$model->id => $model->name];
            }
        }
    }

    public function id($id): void
    {
        if (is_array($id)) {
            $this->whereIn('id', $id);
            return;
        }
        $this->whereIn('id', explode(',', $id));
    }

    public function name($name): void
    {
        $this->related('translations', 'name', 'LIKE', '%' . Helpers::escapeLike($name) . '%');
    }

    public function category($id): void
    {
        if (is_array($id)) {
            $this->whereIn('category_id', $id);
            return;
        }

        $this->where('category_id', $id);
    }

    public function relatedCategory($id): void
    {
        if (!is_array($id)) {
            $id = [$id];
        }

        $this->published(true);
        $this->inStock();
        $this->whereIn('category_id', $id);
    }

    public function costFrom($cost): void
    {
        $this->where('cost', '>=', $cost);
    }

    public function costTo($cost): void
    {
        $this->where('cost', '<=', $cost);
    }

    public function priceFrom($price): void
    {
        $this->whereRaw("CASE WHEN cost_discount = 0 THEN cost >= {$price} ELSE cost_discount >= {$price} END");
    }

    public function priceTo($price): void
    {
        $this->whereRaw("CASE WHEN cost_discount = 0 THEN cost <= {$price} ELSE cost_discount <= {$price} END");
    }

    public function provider($id): void
    {
        $this->where('provider_id', $id);
    }

    public function moderator($id): void
    {
        $this->where('moderator_id', $id);
    }

    public function brand($id): void
    {
        if (is_array($id)) {
            $this->whereIn('brand_id', $id);
            return;
        }
        $this->where('brand_id', '=', $id);
    }

    public function model($id): void
    {
        $this->where('model_id', '=', $id);
    }

    public function groupKey($groupKey): void
    {
        $this->where('group_key', $groupKey);
    }

    public function published($published): void
    {
        $this->where('published', $published);
    }

    public function collection($id): void
    {
        $this->whereHas(
            'collections',
            function ($q) use ($id) {
                if (is_array($id)) {
                    $q->whereIn('id', $id);
                    return;
                }
                $q->where('id', $id);
            }
        );
    }

    public function relatedCollection($id): void
    {
        if (!is_array($id)) {
            $id = [$id];
        }

        $this->published(true);
        $this->inStock();
        $this->whereHas(
            'collections',
            fn (Builder $query) => $query->published()->active()->whereIn('id', $id)
        );
    }

    public function label($id): void
    {
        $this->whereHas(
            'labels',
            function ($q) use ($id) {
                if (is_array($id)) {
                    $q->whereIn('id', $id);
                    return;
                }
                $q->where('id', $id);
            }
        );
    }

    public function specValue($value): void
    {
        $this->whereHas(
            'specificationsValues',
            function ($q) use ($value) {
                if (is_array($value)) {
                    $q->whereIn('spec_value_id', $value);
                    return;
                }
                $q->where('spec_value_id', $value);
            }
        );
    }

    public function specifications($specifications): void
    {
        $specifications = array_filter(array_map('array_filter', $specifications));
        $count = count($specifications);
        if ($count) {
            $this->select($this->query->getModel()->getTable() . '.*');
            $this->join('product_specifications', 'product_specifications.product_id', '=', 'products.id');

            $this->where(
                function ($query) use ($specifications) {
                    foreach ($specifications as $specId => $specValues) {
                        $query->orWhere(
                            function ($query) use ($specId, $specValues) {
                                $query->where('spec_id', $specId)
                                    ->whereIn('spec_value_id', $specValues);
                            }
                        );
                    }
                }
            );

            $this->having(DB::raw('COUNT(DISTINCT product_specifications.spec_id)'), '>=', $count);

            $this->groupBy('products.id');
        }
    }

    public function inStock(): void
    {
        $this->where('available', true)->where('amount', '>', 0);
    }

    public function inActiveCollection(): void
    {
        $this->whereHas(
            'collections',
            fn (Builder $query) => $query->published()->active()
        );
    }

    public function relationsSearch($name): void
    {
        $this->activeProduct();
        $this->name($name);
    }

    public function availableProduct(): void
    {
        $this->published(true);
        $this->inStock();
        $this->unexpired();
    }

    public function activeProduct(): void
    {
        $this->availableProduct();
        $this->inActiveCollection();
    }

    public function unexpired(): void
    {
        $this->where(function(Builder $query) {
            $query
                ->whereNull('expires_at')
                ->orWhere('expires_at', '>=', Carbon::now()->startOfDay());
        });
    }

    public function publishedAtFrom($date): void
    {
        $this->where('published_at', '>=', Carbon::parse($date));
    }

    public function publishedAtTo($date): void
    {
        $this->where('published_at', '<=', Carbon::parse($date)->endOfDay());
    }
}
