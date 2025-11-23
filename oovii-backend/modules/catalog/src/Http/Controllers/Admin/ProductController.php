<?php

namespace WezomCms\Catalog\Http\Controllers\Admin;

use Auth;
use DB;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use WezomCms\Catalog\Exports\ProductExport;
use WezomCms\Catalog\Http\Requests\Admin\ProductRequest;
use WezomCms\Catalog\ModelFilters\TrashedProductFilter;
use WezomCms\Catalog\Models\Category;
use WezomCms\Catalog\Models\Product;
use WezomCms\Catalog\Models\ProductSpecification;
use WezomCms\Catalog\Models\Specifications\Specification;
use WezomCms\Catalog\Repositories\BrandRepository;
use WezomCms\Catalog\Repositories\CollectionRepository;
use WezomCms\Catalog\Repositories\LabelRepository;
use WezomCms\Catalog\Repositories\ProductRepository;
use WezomCms\Core\Foundation\Buttons\Button;
use WezomCms\Core\Http\Controllers\AbstractCRUDController;
use WezomCms\Core\Models\Administrator;
use WezomCms\Core\Models\Role;
use WezomCms\Core\Repositories\AdminRepository;
use WezomCms\Core\Settings\Fields\File;
use WezomCms\Core\Settings\RenderSettings;
use WezomCms\Core\Settings\Tab;
use WezomCms\Core\Traits\AjaxResponseStatusTrait;
use WezomCms\Core\Traits\SettingControllerTrait;
use WezomCms\Core\Traits\SoftDeletesActionsTrait;
use WezomCms\Providers\Repositories\ProviderRepository;

class ProductController extends AbstractCRUDController
{
    use SettingControllerTrait;
    use AjaxResponseStatusTrait;
    use SoftDeletesActionsTrait;

    protected $model = Product::class;

    protected $view = 'cms-catalog::admin.products';

    protected $routeName = 'admin.products';

    protected ?string $exportUrl = 'admin.products.export';

    protected $request = ProductRequest::class;

    public function __construct(
        protected AdminRepository $adminRepository,
        protected ProviderRepository $providerRepo,
        protected CollectionRepository $collectionRepo,
        protected ProductRepository $repo,
        protected LabelRepository $labelRepo,
        protected BrandRepository $brandRepo,
    ) {
        parent::__construct();
    }

    protected function trashedFilter()
    {
        return TrashedProductFilter::class;
    }

    public function search(Request $request): JsonResponse
    {
        /** @var Collection|Product[]|LengthAwarePaginator $products */
        $products = Product::search($request->get('term'), $request->only(['category_id', 'provider_id']));

        $results = [];
        if (!$request->get('page') && !$request->get('multiple')) {
            $results[] = ['id' => '', 'text' => __('cms-core::admin.layout.Not set')];
        }

        foreach ($products as $product) {
            $results[] = [
                'id' => $product->id,
                'text' => sprintf('ID-%s %s (%s)', $product->id, $product->name, money($product->cost, true)),
                'data' => [
                    'name' => $product->name,
                    'cost' => money($product->cost),
                    'currency' => money()->adminCurrencySymbol(),
                    'image' => $product->getImageUrl(),
                    'amount' => $product->amount,
                    'qty' => __('cms-catalog::admin.qty', ['qty' => $product->amount]),
//                    'min' => $product->minCountForPurchase(),
//                    'step' => $product->stepForPurchase(),
//                    'unit' => $product->unit(),
                ]
            ];
        }

        return $this->success(
            [
                'results' => $results,
                'pagination' => [
                    'more' => $products->hasMorePages(),
                ]
            ]
        );
    }

    public function export(Request $request)
    {
        try {
            return Excel::download(new ProductExport($request, $this->repo), 'products.xlsx');
        } catch (Exception $e) {
            report($e);
            flash($e->getMessage())->error();

            return redirect()->route($this->makeRouteName('index'));
        }
    }

    public function setSort($id, Request $request): JsonResponse
    {
        $product = Product::findOrFail($id);

        $this->authorizeForAction('edit', $product);

        $product->sort = $request->get('sort', 0);

        $product->save();

        return $this->success(['message' => __('cms-core::admin.layout.Data successfully updated')]);
    }

    public function copy(int $id): RedirectResponse
    {
        $product = Product::findOrFail($id);
        $this->authorizeForAction('copy', $product);
        try {
            $newProduct = DB::transaction(
                function () use ($product) {
                    $item = tap(
                        $product->replicateWithTranslations(),
                        function (Product $product) {
                            $product->saveOrFail();
                        }
                    );

                    foreach ($product->productSpecifications as $relation) {
                        $item->productSpecifications()->create($relation->only('spec_id', 'spec_value_id'));
                    }

                    return $item;
                }
            );
            return redirect()->route($this->makeRouteName('edit'), $newProduct->id);
        } catch (Exception $e) {
            report($e);
            flash(__('cms-catalog::admin.products.Error copying data'))->error();
            return back();
        }
    }

    public function changeCategoryPopup()
    {
        return view($this->view . '.change-category-popup', ['categoriesTree' => Category::getForSelect()]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function changeCategory(Request $request)
    {
        $this->authorizeForAction('edit', $this->model());

        $categoryId = $request->get('category_id');
        $ids = $request->get('IDS', []);
        if (!$categoryId || !count($ids)) {
            return $this->error(__('cms-catalog::admin.products.Please select category and products'));
        }

        $this->model()::whereKey($ids)
            ->each(
                function (Product $product) use ($categoryId) {
                    $product->category()->associate($categoryId);
                    $product->save();
                }
            );

        return $this->success(['reload' => true]);
    }

    protected function title(): string
    {
        return __('cms-catalog::admin.products.Products');
    }

    protected function exportUrl(): ?string
    {
        return parent::exportUrl();
    }

//    /**
//     * @param  Product  $obj
//     * @param  array  $viewData
//     * @return array
//     */
//    protected function formData($obj, array $viewData): array
//    {
//        $selectedSpecifications = $obj->productSpecifications
//            ->mapToGroups(function (ProductSpecification $productSpecification) {
//                return [$productSpecification->spec_id => $productSpecification->spec_value_id];
//            })->toArray();
//
//        return [
//            'specifications' => $this->getSpecificationsForSelect(),
//            'selectedSpecifications' => $selectedSpecifications,
//            'categoriesTree' => Category::getForSelect(),
//        ];
//    }

    protected function indexViewData($result, array $viewData): array
    {
        $buttons = [];
        if ($this->allowsForAction('edit', $this->model())) {
            $buttons[] = Button::make()
                ->setAttribute('data-list-action', 'changeCategoryPopup')
                ->setClass('btn btn-sm btn-info')
                ->setTitle(__('cms-catalog::admin.products.Move products to another category'))
                ->setIcon('fa fa-share');
        }

        $result->loadMissing(['provider', 'color']);

        return compact('buttons');
    }

    protected function selectionIndexResult($query, Request $request)
    {
        $query
//            ->with('color', 'category')
//            ->orderBy('group_key')
            ->sorting();
    }

    protected function createViewData($model, array $viewData): array
    {
        $selectedSpecifications = $model->productSpecifications
            ->mapToGroups(
                function (ProductSpecification $productSpecification) {
                    return [$productSpecification->spec_id => $productSpecification->spec_value_id];
                }
            )->toArray();

        /** @var Administrator $admin */
        $admin = Auth::user();
        $isProvider = $admin->onlyProvider();

        $providers = $isProvider
            ? []
            : $this->adminRepository->getByRoleForSelect(
                Role::DEFAULT_PROVIDER,
                ['roles'],
                __('cms-providers::admin.provider.choice')
            );

        $moderators = $isProvider
            ? []
            : $this->adminRepository->getByRoleForSelect(
                Role::DEFAULT_MODERATOR,
                ['roles'],
                __('cms-providers::admin.moderator.choice')
            );

        return [
            'isProvider' => $isProvider,
            'specifications' => $this->getSpecificationsForSelect(),
            'selectedSpecifications' => $selectedSpecifications,
            'providers' => $providers,
            'selectedProvider' => [],
            'moderators' => $moderators,
            'selectedModerator' => [$model->moderator_id],
            'collections' => $this->collectionRepo->forSelectWithTranslation(),
            'selectedCollections' => [],
            'relations' => $this->repo->forSelectWithTranslation($model->id),
            'selectedRelations' => [],
            'labels' => $this->labelRepo->forSelectWithTranslation(),
            'selectedLabels' => [],
            'categoriesTree' => Category::getForSelect(),
            'brands' => $this->brandRepo->forSelectWithTranslation(null, __('cms-catalog::admin.brand.choice')),
            'selectedBrand' => [],
        ];
    }

    private function getSpecificationsForSelect(): Collection
    {
        return Specification::with(
            [
                'specValues' => function ($query) {
                    $query->sorting();
                }
            ]
        )
            ->sorting()
            ->get();
    }

    protected function editViewData($model, array $viewData): array
    {
        $selectedSpecifications = $model->productSpecifications
            ->mapToGroups(
                function (ProductSpecification $productSpecification) {
                    return [$productSpecification->spec_id => $productSpecification->spec_value_id];
                }
            )->toArray();

        /** @var Administrator $admin */
        $admin = Auth::user();
        $isProvider = $admin->onlyProvider();

        $providers = $isProvider
            ? []
            : $this->adminRepository->getByRoleForSelect(
                Role::DEFAULT_PROVIDER,
                ['roles'],
                __('cms-providers::admin.provider.choice')
            );

        $moderators = $isProvider
            ? []
            : $this->adminRepository->getByRoleForSelect(
                Role::DEFAULT_MODERATOR,
                ['roles'],
                __('cms-catalog::admin.Choose moderator')
            );

        return [
            'isProvider' => $isProvider,
            'specifications' => $this->getSpecificationsForSelect(),
            'selectedSpecifications' => $selectedSpecifications,
            'providers' => $providers,
            'selectedProvider' => [$model->provider_id],
            'moderators' => $moderators,
            'selectedModerator' => [$model->moderator_id],
            'collections' => $this->collectionRepo->forSelectWithTranslation(),
            'selectedCollections' => $model->collections()->pluck('collection_id')->toArray(),
            'relations' => $this->repo->forSelectWithTranslation($model->id),
            'selectedRelations' => $model->relations()->pluck('relation_id')->toArray(),
            'labels' => $this->labelRepo->forSelectWithTranslation(),
            'selectedLabels' => $model->labels()->pluck('label_id')->toArray(),
            'categoriesTree' => Category::getForSelect(),
            'brands' => $this->brandRepo->forSelectWithTranslation(null, __('cms-catalog::admin.brand.choice')),
            'selectedBrand' => [$model->brand_id],
        ];
    }

    protected function fill($obj, FormRequest $request): array
    {
        /** @var Product $obj */
        $data = $request->validated();

        if (!$obj->provider_id && !$request->has('provider_id')) {
            /** @var $user Administrator */
            $user = Auth::user();
            $data['provider_id'] = $user->id;
        }

        if ($request->input('published_at')) {
            $data['published_at'] = Carbon::createFromFormat('d.m.Y', $request->input('published_at'))->startOfDay();
        }

        $data['dimensions'] = Product::sortDimensions($data['dimensions']);

//        $obj->expires_at = Carbon::parse($request->get('expires_at'))->endOfDay();

//        if (config('cms.catalog.brands.enabled', false)) {
//            $obj->brand()->associate($request->get('brand_id'));
//        }
//
//        if (config('cms.catalog.models.enabled', false)) {
//            $obj->model()->associate($request->get('model_id'));
//        }

        return $data;
    }

    protected function afterSuccessfulSave($obj, Request $request)
    {
        $obj->updateSpecValueRelation($request->get('SPEC_VALUES', []));
        $obj->collections()->sync($request->get('collections', []));
        $obj->relations()->sync($request->get('relations', []));
        $obj->labels()->sync($request->get('labels', []));
    }

    /**
     * @return array
     * @throws Exception
     */
    protected function settings(): array
    {
        $result = [];

        // Products
//        $products = new RenderSettings(
//            new Tab('products', __('cms-catalog::admin.products.Products'), 2, 'fa-folder-o')
//        );

        // Products meta
//        $items = [
//            Title::make()
//                ->setHelpText(__('cms-catalog::admin.products.Product meta-tags keys')),
//            Heading::make()
//                ->setHelpText(__('cms-catalog::admin.products.Product meta-tags keys')),
//            Description::make()
//                ->setHelpText(__('cms-catalog::admin.products.Product meta-tags keys')),
//            Keywords::make()
//                ->setHelpText(__('cms-catalog::admin.products.Product meta-tags keys')),
//        ];

//        $result[] = new MultilingualGroup($products, $items);

//        $result[] = AdminLimit::make();

        $tabs = new RenderSettings(
            new Tab('page', __('cms-catalog::admin.products.import'), 1, 'fa-folder-o')
        );

        $result[] = File::make($tabs)
            ->setName(__('cms-catalog::admin.products.import'))
            ->setSort(1)
            ->setKey('import')
            ->setRules('nullable|file');

        return $result;
    }
}
