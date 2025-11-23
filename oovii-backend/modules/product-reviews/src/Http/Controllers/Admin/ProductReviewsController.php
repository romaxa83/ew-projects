<?php

namespace WezomCms\ProductReviews\Http\Controllers\Admin;

use Exception;
use Form;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use WezomCms\Core\Foundation\Buttons\Link;
use WezomCms\Core\Foundation\Helpers;
use WezomCms\Core\Http\Controllers\AbstractCRUDController;
use WezomCms\Core\Settings\AdminLimit;
use WezomCms\Core\Traits\AjaxResponseStatusTrait;
use WezomCms\Core\Traits\SettingControllerTrait;
use WezomCms\ProductReviews\Exports\ProductReviewsExport;
use WezomCms\ProductReviews\Http\Requests\Admin\ProductReviewRequest;
use WezomCms\ProductReviews\Models\ProductReview;
use Illuminate\Http\JsonResponse;

class ProductReviewsController extends AbstractCRUDController
{
    use SettingControllerTrait;
    use AjaxResponseStatusTrait;

    protected $model = ProductReview::class;

    protected $view = 'cms-product-reviews::admin';

    protected $routeName = 'admin.product-reviews';

    protected ?string $exportUrl = 'admin.product-reviews.export';

    protected $request = ProductReviewRequest::class;

    protected function title(): string
    {
        return __('cms-product-reviews::admin.Product reviews');
    }

    public function reviewsByProductId($id, $exclude = null): JsonResponse
    {
        $select = (string) Form::multiLevelSelect(
            'parent_id',
            $this->getReviewsTreeByProductId($id, $exclude),
            $exclude ? ProductReview::find($exclude)->parent_id : null,
            false,
            [
                'class' => 'form-control js-select2',
                'id' => 'parent_id',
                'placeholder' => __('cms-core::admin.layout.Not set')
            ]
        );

        return $this->success(compact('select'));
    }

    /**
     * @param  Builder  $query
     * @param  Request  $request
     */
    protected function selectionIndexResult($query, Request $request)
    {
        $query->with([
            'product' => function ($query) {
                $query->withTrashed();
            },
            'parent',
            'user',
        ]);
    }

    /**
     * @param  ProductReview  $obj
     * @param  array  $viewData
     * @return array
     */
    protected function createViewData($obj, array $viewData): array
    {
        $obj->fill(request()->only('admin_answer', 'published', 'product_id', 'parent_id'));

        return [
            'reviews' => $this->getReviewsTreeByProductId($obj->product_id, $obj->id),
            'products' => $obj->product ? [$obj->product] : [],
        ];
    }

    /**
     * @param  ProductReview  $obj
     * @param  array  $viewData
     * @return array
     */
    protected function editViewData($obj, array $viewData): array
    {
        $obj->load([
            'product' => function ($query) {
                $query->withTrashed();
            }
        ]);

        return [
            'reviews' => $this->getReviewsTreeByProductId($obj->product_id, $obj->id),
            'products' => $obj->product ? [$obj->product] : [],
        ];
    }

    /**
     * @param  int|null  $productId
     * @param  int|null  $exclude
     * @return array
     */
    protected function getReviewsTreeByProductId(?int $productId, ?int $exclude = null): array
    {
        if (!$productId) {
            return [];
        }

        $reviews = ProductReview::where('product_id', $productId)
            ->when($exclude, function ($query, $exclude) {
                $query->whereKeynot($exclude);
            })
            ->get()
            ->map(function (ProductReview $review) {
                return [
                    'value' => $review->id,
                    'name' => $review->getReviewFullName(),
                    'parent_id' => $review->parent_id,
                ];
            });

        return Helpers::groupByParentId($reviews);
    }


    /**
     * @return array
     * @throws \Exception
     */
    protected function settings(): array
    {
        $result = [];

//        $siteRenderSettings = RenderSettings::siteTab();
//
//        $result[] = Number::make($siteRenderSettings)
//            ->setName(__('cms-product-reviews::admin.Site reviews limit at product page'))
//            ->default(10)
//            ->setKey('product-page-limit')
//            ->setRules('required|numeric|min:1');
//
//        $result[] = MultilingualGroup::make($siteRenderSettings, [
//            PageName::make()
//                ->default('My reviews')
//                ->setSort(0),
//            Wysiwyg::make($siteRenderSettings)
//                ->setIsMultilingual()
//                ->setKey('text')
//                ->setName(__('cms-product-reviews::admin.Short text'))
//                ->setRules('nullable|string|max:500'),
//        ]);
//
//        $result[] = Select::make($siteRenderSettings)
//            ->setName(__('cms-product-reviews::admin.Comment rules page'))
//            ->setKey('comment_rules_page_id')
//            ->setValuesList(Page::getForSelect())
//            ->setRules('nullable|exists:pages,id');

        $result[] = AdminLimit::make();

        return $result;
    }

    /**
     * @param  string  $currentAction
     * @param  ProductReview  $model
     * @param  string|null  $index
     * @param  string|null  $indexAbility
     * @return \WezomCms\Core\Contracts\ButtonsContainerInterface
     */
    protected function formButtons(string $currentAction, $model, string $index = null, string $indexAbility = null)
    {
        $buttons = parent::formButtons($currentAction, $model, $index, $indexAbility);

        if ($currentAction === 'edit') {
            $link = route(
                'admin.product-reviews.create',
                ['admin_answer' => 1, 'published' => 1, 'product_id' => $model->product_id, 'parent_id' => $model->id]
            );

            $buttons->add(Link::make()
                ->setName(__('cms-product-reviews::admin.Write answer'))
                ->setLink($link)
                ->setClass(['btn-sm', 'btn-outline-secondary'])
                ->setIcon('fa-pencil-square-o')
                ->setSortPosition(5));
        }

        return $buttons;
    }

    public function export(Request $request)
    {
        try {
            return Excel::download(new ProductReviewsExport($request), 'reviews.xlsx');
        } catch (Exception $e){
            report($e);
            flash($e->getMessage())->error();

            return redirect()->route($this->makeRouteName('index'));
        }
    }
}
