<?php

namespace WezomCms\Catalog\Http\Controllers\Admin;

use Auth;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use WezomCms\Catalog\Http\Requests\Admin\CollectionRequest;
use WezomCms\Catalog\Models\Collections\Category;
use WezomCms\Catalog\Models\Collections\Collection;
use WezomCms\Catalog\Models\Product;
use WezomCms\Catalog\Repositories\CollectionRepository;
use WezomCms\Catalog\Repositories\ProductRepository;
use WezomCms\Core\Http\Controllers\AbstractCRUDController;
use WezomCms\Core\Models\Administrator;
use WezomCms\Core\Models\Role;
use WezomCms\Core\Repositories\AdminRepository;
use WezomCms\Core\Settings\AdminLimit;
use WezomCms\Core\Settings\Fields\Image;
use WezomCms\Core\Settings\Fields\Input;
use WezomCms\Core\Settings\RenderSettings;
use WezomCms\Core\Settings\Tab;
use WezomCms\Core\Traits\SettingControllerTrait;

class CollectionController extends AbstractCRUDController
{
    use SettingControllerTrait;

    protected $model = Collection::class;

    protected $view = 'cms-catalog::admin.collections';

    protected $routeName = 'admin.collections';

    protected $request = CollectionRequest::class;

    public function __construct(
        protected ProductRepository $productRepo,
        protected CollectionRepository $collectionRepo,
        protected AdminRepository $adminRepo
    ) {
        parent::__construct();
    }

    protected function title(): string
    {
        return __('cms-catalog::admin.collection.collections');
    }

    protected function selectionIndexResult($query, Request $request)
    {
        $query->withCount(['products']);
    }

    protected function formData($obj, array $viewData): array
    {
        return [
            'categoriesTree' => Category::getForSelect(),
            'moderators' => $this->adminRepo->getByRoleForSelect(
                Role::DEFAULT_MODERATOR,
                ['roles'],
                __('cms-core::admin.moderator.choice')
            ),
        ];
    }

    protected function fillStoreData($model, FormRequest $request): array
    {
        /** @var $model Collection */
        /** @var $user Administrator */
        $user = Auth::user();
        $model->creator_id = $user->id;

        if ($request['time_counter'] === Collection::START_AT_COUNTER) {
            $model->start_counter = true;
            $model->end_counter = false;
        } else {
            $model->start_counter = false;
            $model->end_counter = true;
        }

        return $request->all();
    }

    protected function fillUpdateData($model, FormRequest $request): array
    {
        /** @var Collection $model */
        if ($request['time_counter'] === Collection::START_AT_COUNTER) {
            $model->start_counter = true;
            $model->end_counter = false;
        } else {
            $model->start_counter = false;
            $model->end_counter = true;
        }

        if($model->start_at->format('d.m.Y H:i') != $request['start_at']){
            $model->is_send_start = false;
        }
        if($model->end_at->format('d.m.Y H:i') != $request['end_at']){
            $model->is_send_finish = false;
        }

        return $request->all();
    }

    protected function afterSuccessfulSave($model, FormRequest $request)
    {
        if($productList = $request['products-list']){
            $productIds = [];
            foreach (explode(',', $productList) ?? [] as $item){
                if(is_numeric($item)){
                    $productIds[] = (int)$item;
                }
            }
            if(!empty($productIds)){
                /** @var $model Collection */
                $model->products()->sync($productIds);
            }
        }

        if (!$model->end_at) {
            $model->end_at = $model->start_at->addDays(config('cms.catalog.collections.sale_days'));
        }

        $model->published = !$model->checkForPublished();

        $model->save();
    }

//    public function beforeDelete($obj, bool $force = false)
//    {
//        /** @var $obj Collection */
//        $rels = DB::table('collection_product_relations')
//            ->where('collection_id', $obj->id)->delete();
//
//        parent::beforeDelete($obj, $force);
//    }

    public function destroy($id)
    {
        /** @vat $model Collection */
        $model = Collection::findOrFail($id);

        $this->authorizeForAction('delete', $model);

        $res = DB::transaction(function() use ($model){
            DB::table('collection_product_relations')
                ->where('collection_id', $model->id)->delete();

            if ($model->delete()) {
                return true;
            } else {
                return false;
            }

        });

        if ($res) {
            flash(__('cms-core::admin.layout.Data deleted successfully'))->success();
        } else {
            flash(__('cms-core::admin.layout.Data deletion error'))->error();
        }

        return redirect()->route($this->makeRouteName('index'));
    }

    protected function settings(): array
    {
        $result = [
            AdminLimit::make()
        ];

        $tabs = new RenderSettings(
            new Tab('page', __('cms-catalog::admin.collection.tab.title_all_product'), 1, 'fa-folder-o')
        );

        $result[] = Input::make($tabs)
            ->setKey('title')
            ->setName(__('cms-catalog::admin.collection.tab.title'))
            ->setIsMultilingual()
            ->setSort(5);

        $result[] = Image::make($tabs)
            ->setSettings(config('cms.catalog.collections.images'))
            ->setName(__('cms-catalog::admin.collection.tab.image_ru', ['width' => 400, 'height' => 250]))
            ->setSort(1)
            ->setKey('ru')
            ->setRules('nullable|file');

        $result[] = Image::make($tabs)
            ->setSettings(config('cms.catalog.collections.images'))
            ->setName(__('cms-catalog::admin.collection.tab.image_kk', ['width' => 400, 'height' => 250]))
            ->setSort(1)
            ->setKey('kk')
            ->setRules('nullable|file');

        return $result;
    }

    public function addProduct(Request $request)
    {
        $product = Product::query()
            ->where('id', data_get($request->all(), 'productId.0'))
            ->first();

        if($collectionID = data_get($request->all(), 'collectionId')){
            /** @var $collection Collection */
            $collection = $this->collectionRepo->getByID($collectionID);
            $collection->products()->attach($product);
        }

        return view($this->view . '.product-list', ['product' => $product]);
    }

    public function deleteProduct(Request $request)
    {
        if($collectionID = data_get($request->all(), 'collectionId')){
            /** @var $collection Collection */
            $collection = $this->collectionRepo->getByID($collectionID);
            return $collection
                ->products()
                ->where('id', data_get($request->all(), 'productId'))
                ->delete()
            ;
        }

    }
}

