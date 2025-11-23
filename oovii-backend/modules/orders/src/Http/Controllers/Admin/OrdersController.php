<?php

namespace WezomCms\Orders\Http\Controllers\Admin;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use WezomCms\Catalog\Models\Category;
use WezomCms\Catalog\Models\Product;
use WezomCms\Core\Contracts\ButtonInterface;
use WezomCms\Core\Contracts\ButtonsContainerInterface;
use WezomCms\Core\Foundation\Buttons\ButtonsMaker;
use WezomCms\Core\Foundation\Helpers;
use WezomCms\Core\Http\Controllers\AbstractCRUDController;
use WezomCms\Core\Settings\AdminLimit;
use WezomCms\Core\Settings\Fields\AbstractField;
use WezomCms\Core\Settings\MultilingualGroup;
use WezomCms\Core\Settings\PageName;
use WezomCms\Core\Settings\RenderSettings;
use WezomCms\Core\Settings\SiteLimit;
use WezomCms\Core\Settings\Tab;
use WezomCms\Core\Traits\ActionShowTrait;
use WezomCms\Core\Traits\Model\Filterable;
use WezomCms\Core\Traits\SettingControllerTrait;
use WezomCms\Core\Traits\SoftDeletesActionsTrait;
use WezomCms\Orders\Enums\PayedModes;
use WezomCms\Orders\Events\CreatedOrder;
use WezomCms\Orders\Http\Requests\Admin\AddOrderItemRequest;
use WezomCms\Orders\Http\Requests\Admin\CreateOrderRequest;
use WezomCms\Orders\Http\Requests\Admin\UpdateOrderRequest;
use WezomCms\Orders\ModelFilters\OrderTrashedFilter;
use WezomCms\Orders\Models\Delivery;
use WezomCms\Orders\Models\Order;
use WezomCms\Orders\Models\OrderDeliveryInformation;
use WezomCms\Orders\Models\OrderPaymentInformation;
use WezomCms\Orders\Models\OrderStatus;
use WezomCms\Orders\Models\Payment;
use WezomCms\Providers\Models\Provider;
use WezomCms\Users\UsersServiceProvider;

class OrdersController extends AbstractCRUDController
{
    use ActionShowTrait;
    use SettingControllerTrait;
    use SoftDeletesActionsTrait;

    /**
     * Model name.
     *
     * @var string
     */
    protected $model = Order::class;

    /**
     * Base view path name.
     *
     * @var string
     */
    protected $view = 'cms-orders::admin.orders';

    /**
     * Resource route name.
     *
     * @var string
     */
    protected $routeName = 'admin.orders';

    /**
     * Form request class name for "create" action.
     *
     * @var string
     */
    protected $createRequest = CreateOrderRequest::class;

    /**
     * Form request class name for "update" action.
     *
     * @var string
     */
    protected $updateRequest = UpdateOrderRequest::class;

    /**
     * @param Order $obj
     * @param FormRequest $request
     */
    public function afterSuccessfulUpdate($obj, FormRequest $request)
    {
        foreach ($request->get('QUANTITY', []) as $id => $quantity) {
            $obj->items()->whereKey($id)->update(compact('quantity'));
        }

        $obj->client()->updateOrCreate([], $request->get('client', []));

        $obj->recipient()->updateOrCreate([], $request->get('recipient', []));

        $obj->deliveryInformation()->updateOrCreate([], $request->get('deliveryInformation', []));
    }

    /**
     * @param Order $order
     * @param Provider $provider
     * @param ButtonsContainerInterface $buttonsContainer
     * @return Factory|\Illuminate\View\View
     * @throws AuthorizationException
     * @throws BindingResolutionException
     */
    public function addItem(Order $order, Provider $provider, ButtonsContainerInterface $buttonsContainer)
    {
        $this->authorizeForAction('edit', $order);

        $this->before();

        $buttonsContainer->add(ButtonsMaker::save())
            ->add(ButtonsMaker::saveAndClose(route('admin.orders.edit', $order->id)))
            ->add(ButtonsMaker::close(route('admin.orders.edit', $order->id)));

        $this->addBreadcrumb(
            __(
                'cms-orders::admin.orders.Order: :number from: :date',
                ['number' => $order->id, 'date' => $order->created_at->format('d.m.Y H:i')]
            )
        );

        $this->pageName->setPageName(__('cms-orders::admin.orders.Add item'));
        $this->addBreadcrumb(__('cms-orders::admin.orders.Add item'));
        $this->renderJsValidator(new AddOrderItemRequest());

        return view(
            'cms-orders::admin.orders.add-item',
            [
                'routeName' => $this->routeName,
                'obj' => $order,
                'categoriesTree' => Category::getForSelect(),
                'provider' => optional($provider->adminProfile)->id,
            ]
        );
    }

    /**
     * @param $id
     * @param AddOrderItemRequest $request
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function storeItem($id, AddOrderItemRequest $request)
    {
        $order = Order::findOrFail($id);

        $this->authorizeForAction('edit', $order);

        $product = Product::findOrFail($request->get('product_id'));

        $order->items()->create(
            [
                'product_id' => $request->get('product_id'),
                'quantity' => $request->get('quantity', $product->minCountForPurchase()),
                'price' => $product->priceForPurchase(),
                'purchase_price' => $product->priceForPurchase(),
            ]
        );

        event(new CreatedOrder($order));

        flash(__('cms-orders::admin.orders.Item successfully stored'))->success();

        // Redirect
        switch (app('request')->get('form-action')) {
            case ButtonInterface::ACTION_SAVE_AND_CLOSE:
                return redirect()->route('admin.orders.edit', $order->id);
            case ButtonInterface::ACTION_SAVE:
            default:
                if (ButtonInterface::ACTION_STORE === app(Route::class)->getActionMethod()) {
                    return redirect()->route('admin.orders.edit', [$order->id]);
                }

                return redirect()->back();
        }
    }

    /**
     * @param $id
     * @param $itemId
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function deleteItem($id, $itemId)
    {
        $obj = Order::findOrFail($id);

        $this->authorizeForAction('edit', $obj);

        if ($obj->items()->where('id', $itemId)->delete()) {
            flash(__('cms-core::admin.layout.Data deleted successfully'))->success();
        } else {
            flash(__('cms-core::admin.layout.Data deletion error'))->error();
        }

        return redirect()->back();
    }

    public function renderDeliveryForm(Request $request): JsonResponse
    {
        $delivery = Delivery::find($request->input('delivery_id'));
        if (!$delivery) {
            return $this->success(['html' => '']);
        }

        $deliveryInformation = OrderDeliveryInformation::make($request->input('deliveryInformation', []));

        return $this->success(
            [
                'html' => optional($this->renderDeliveryFormInputs($delivery, $deliveryInformation))->render()
            ]
        );
    }

    /**
     * Resource name for breadcrumbs and title.
     *
     * @return string
     */
    protected function title(): string
    {
        return __('cms-orders::admin.orders.Orders');
    }

    /**
     * @param Model|Order $model
     * @return string
     */
    protected function showTitle(Model $model): string
    {
        return __(
            'cms-orders::admin.orders.Order: :number from: :date',
            ['number' => $model->id, 'date' => $model->created_at->format('d.m.Y H:i')]
        );
    }

    /**
     * @return string|object|Filterable|null
     */
    protected function trashedFilter()
    {
        return OrderTrashedFilter::class;
    }

    /**
     * @param Order $obj
     * @param array $viewData
     * @return array
     */
    protected function editViewData($obj, array $viewData): array
    {
        $obj->load(
            [
                'items.product' => function ($query) {
                    $query->withTrashed();
                }
            ]
        );

        $data = $this->createViewData($obj, $viewData);

        // Restore users select
        $users = ['' => __('cms-core::admin.layout.Not set')];
        if ($obj->user) {
            $users[$obj->user->id] = $obj->user->full_name;
        }

        $data['users'] = $users;
        $data['deliveryForm'] = $this->renderDeliveryFormInputs($obj->delivery, $obj->deliveryInformation);
        $data['paymentData'] = $this->renderPaymentInfo($obj->paymentInformation);

        return $data;
    }

    /**
     * @param Order $obj
     * @param array $viewData
     * @return array
     */
    protected function createViewData($obj, array $viewData): array
    {
        $providers = Provider::query()
            ->where('active', true)
            ->whereHas('adminProfile', function (Builder $query) {
                $query->providers();
            })
            ->pluck('company', 'id');

        return [
            'deliveries' => Delivery::getForSelect(true),
            'deliveryForm' => '',
            'payments' => Payment::getForSelect(),
            'statuses' => OrderStatus::getForSelect(),
            'users' => [],
            'providers' => $providers,
            'paymentData' => '',
        ];
    }

    /**
     * @param Delivery $delivery
     * @param OrderDeliveryInformation $deliveryInformation
     * @return View|null
     */
    protected function renderDeliveryFormInputs(Delivery $delivery, OrderDeliveryInformation $deliveryInformation)
    {
        $driver = optional($delivery)->makeDriver();
        if (!$driver) {
            return null;
        }

        return $driver->renderAdminFormInputs($deliveryInformation);
    }

    protected function renderPaymentInfo(?OrderPaymentInformation $paymentInfo = null): string
    {
        if (!$paymentInfo || (!$driver = $paymentInfo->getPaymentDriver())) {
            return '';
        }

        return $driver->renderPaymentData($paymentInfo);
    }

    /**
     * @param Model|Order $obj
     * @param FormRequest $request
     * @return array
     */
    protected function fillStoreData($obj, FormRequest $request): array
    {
        $obj->payment()->associate($request->get('payment_id'));
        $obj->payed = $request->boolean('payed');
        $obj->payed_mode = PayedModes::MANUAL;

        $obj->delivery()->associate($request->get('delivery_id'));
        $obj->provider()->associate($request->get('provider_id'));

        if (Helpers::providerLoaded(UsersServiceProvider::class)) {
            $obj->user()->associate($request->get('user_id'));
        }

        return parent::fillStoreData($obj, $request);
    }

    /**
     * @param Order $obj
     * @param FormRequest $request
     */
    protected function afterSuccessfulStore($obj, FormRequest $request)
    {
        $obj->changeStatus(OrderStatus::find($request->get('status_id')))->save();

        $obj->client()->create($request->get('client', []));

        $obj->recipient()->create($request->get('recipient', []));

        $obj->deliveryInformation()->create($request->get('deliveryInformation', []));
    }

    /**
     * @param Order $obj
     * @param FormRequest $request
     * @return array
     */
    protected function fillUpdateData($obj, FormRequest $request): array
    {
        $obj->changeStatus(OrderStatus::find($request->get('status_id')));

        $obj->payment()->associate($request->get('payment_id'));
        $obj->payed = $request->get('payed');

        // If manually changed payed status
        if ($obj->isDirty('payed')) {
            $obj->payed_mode = PayedModes::MANUAL;
        }

        $obj->delivery()->associate($request->get('delivery_id'));

        if (Helpers::providerLoaded(UsersServiceProvider::class)) {
            $obj->user()->associate($request->get('user_id'));
        }

        return parent::fillUpdateData($obj, $request);
    }

    /**
     * @return array|AbstractField[]|MultilingualGroup[]
     * @throws Exception
     */
    protected function settings(): array
    {
        return [
            SiteLimit::make()->setName(__('cms-orders::admin.orders.Limit orders at page in LK')),
            MultilingualGroup::make(RenderSettings::siteTab(), [PageName::make()->default('Orders')]),
            MultilingualGroup::make(
                new RenderSettings(
                    new Tab('site_thanks', __('cms-orders::admin.orders.Thanks page'), 2, 'fa-file-text')
                ),
                [PageName::make()->default('Thanks')]
            ),
            AdminLimit::make(),
        ];
    }

    /**
     * @param Order $obj
     * @param array $viewData
     * @return array
     */
    protected function showViewData($obj, array $viewData): array
    {
        return [
            'delivery' => optional($obj->delivery)->name,
            'deliveryData' => $obj->delivery
                ? optional($obj->delivery->makeDriver())->renderAdminFormData($obj->deliveryInformation)
                : null,
            'payment' => optional($obj->payment)->name,
            'payed' => $obj->payed ? __('cms-core::admin.layout.Yes') : __('cms-core::admin.layout.No'),
            'status' => optional($obj->status)->name,
            'paymentData' => $this->renderPaymentInfo($obj->paymentInformation),
        ];
    }
}
