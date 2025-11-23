<?php

namespace WezomCms\Orders;

use AntistressStore\CdekSDK2\CdekClientV2;
use Auth;
use Cart;
use Event;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Relations\Relation;
use SidebarMenu;
use WezomCms\Catalog\Helpers\GatesHelpers;
use WezomCms\Catalog\Models\Product;
use WezomCms\Core\BaseServiceProvider;
use WezomCms\Core\Contracts\Assets\AssetManagerInterface;
use WezomCms\Core\Contracts\PermissionsContainerInterface;
use WezomCms\Core\Contracts\SitemapXmlGeneratorInterface;
use WezomCms\Core\Models\Administrator;
use WezomCms\Orders\Cart\Storage\DatabaseStorage;
use WezomCms\Orders\Cart\Storage\SessionStorage;
use WezomCms\Orders\Contracts\CartInterface;
use WezomCms\Orders\Contracts\NovaPoshtaServiceInterface;
use WezomCms\Orders\Database\Seeders\DeliveriesSeeder;
use WezomCms\Orders\Database\Seeders\OrderStatusesSeeder;
use WezomCms\Orders\Database\Seeders\PaymentsSeeder;
use WezomCms\Orders\Enums\PayedModes;
use WezomCms\Orders\Events\AutoPayedOrder;
use WezomCms\Orders\Events\CanceledOrder;
use WezomCms\Orders\Events\CreatedOrders;
use WezomCms\Orders\Listeners\SendAutoPayedOrderNotification;
use WezomCms\Orders\Listeners\SendCanceledOrderNotification;
use WezomCms\Orders\Listeners\SendCreatedOrdersNotification;
use WezomCms\Orders\Models\Delivery;
use WezomCms\Orders\Models\Order;
use WezomCms\Orders\Models\OrderItem;
use WezomCms\Orders\Models\OrderStatus;
use WezomCms\Orders\Models\Payment;
use WezomCms\Orders\Observers\OrderObserver;
use WezomCms\Orders\Services\NovaPoshtaService;
use WezomCms\ProductReviews\Models\ProductReview;

class OrdersServiceProvider extends BaseServiceProvider
{
    /**
     * All module widgets.
     *
     * @var array|string|null
     */
    protected $widgets = 'cms.orders.orders.widgets';

    /**
     * Dashboard widgets.
     *
     * @var array|string|null
     */
    protected $dashboard = 'cms.orders.orders.dashboards';

    /**
     * List of enum classes for auto scanning localization keys.
     *
     * @var array
     */
    protected $enumClasses = [
        PayedModes::class,
    ];

    /**
     * Custom translation keys.
     *
     * @var array
     */
    protected $translationKeys = [
        'cms-orders::admin.pieces',
        'cms-orders::site.pieces',
        'cms-orders::admin.house',
        'cms-orders::site.house',
        'cms-orders::admin.room',
        'cms-orders::site.room',
        'cms-orders::site.sdek.tariffs.139',
        'cms-orders::site.sdek.tariffs.480',
        'cms-orders::admin.sdek.tariffs.139',
        'cms-orders::admin.sdek.tariffs.480',
    ];

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(CartInterface::class, function () {
            $precision = config('cms.core.money.precision', 0);
            $quantityPrecision = config('cms.orders.cart.quantity_precision', 0);
            $driver = config('cms.orders.cart.storage');

            switch ($driver) {
                case 'database':
                    return new DatabaseStorage($precision, $quantityPrecision);
                case 'session':
                    return new SessionStorage($precision, $quantityPrecision);
                default:
                    throw new Exception("Unsupported cart driver '{$driver}'");
            }
        });

        if (class_exists('NovaPoshta\ApiModels\Address')) {
            $this->app->singleton(NovaPoshtaServiceInterface::class, function () {
                return new NovaPoshtaService(
                    (string) settings('deliveries.site.nova_poshta_key'),
                    $this->app->getLocale()
                );
            });
        }

        $this->app->bind('sdekClient', function () {

            if (config('cms.orders.delivery-and-payment.sdek.test', true)) {
                $client = new CdekClientV2('TEST');
            } else {
                $client = new CdekClientV2(
                    config('cms.orders.delivery-and-payment.sdek.id', ''),
                    config('cms.orders.delivery-and-payment.sdek.password', ''),
                );
            }

//            $testMode = settings('deliveries.site.sdek_test', true);
//            $client = null;
//            if ($testMode) {
//                $client = new CdekClientV2('TEST');
//            } else {
//                $client = new CdekClientV2(
//                    settings('deliveries.site.sdek_account'),
//                    settings('deliveries.site.sdek_password'),
//                );
//            }

            return $client;
        });
    }

    /**
     * Application booting.
     */
    public function boot()
    {
        Product::addExternalMethod('getInCartAttribute', function () {
            /** @var Product $this */
            return Cart::hasProduct($this->id);
        });

        Relation::morphMap([
            Order::MORPH_NAME => Order::class,
        ]);

        parent::boot();
    }

    protected function afterBootForAdminPanel()
    {
        app(AssetManagerInterface::class)
            ->addJs('vendor/cms/orders/orders.js', 'orders')
            ->group(AssetManagerInterface::GROUP_ADMIN);
        app(AssetManagerInterface::class)
            ->addJs('vendor/cms/orders/sdek.js', 'sdek')
            ->group(AssetManagerInterface::GROUP_ADMIN);
    }

    /**
     * @param  PermissionsContainerInterface  $permissions
     */
    public function permissions(PermissionsContainerInterface $permissions)
    {
        // Orders
        $permissions->add(
            'orders',
            __('cms-orders::admin.orders.Orders'),
            [
                'view',
                'create',
                'edit' => GatesHelpers::modelGate('orders.edit'),
                'delete' => GatesHelpers::modelGate('orders.delete'),
                'show' => GatesHelpers::modelGate('orders.show'),
                'restore' => GatesHelpers::modelGate('orders.restore'),
                'force-delete' => GatesHelpers::modelGate('orders.force-delete'),
                'edit-settings',
            ]
        );

        // Statuses
        $permissions->add(
            'order-statuses',
            __('cms-orders::admin.statuses.Order statuses'),
            [
                'view',
                'create',
                'edit',
                'edit-settings',
                'delete' => function (Administrator $administrator, OrderStatus $obj) {
                    return $obj->canBeDeleted()
                        && $obj->orders()->doesntExist()
                        && $administrator->hasAccess('order-statuses.delete');
                }
            ]
        );

        // Deliveries
        $permissions->add(
            'deliveries',
            __('cms-orders::admin.deliveries.Deliveries'),
            [
                'view',
                'create',
                'edit',
                'edit-settings',
                'delete' => function (Administrator $administrator, Delivery $delivery) {
                    return !$delivery->driver && $administrator->hasAccess('deliveries.delete');
                }
            ]
        );

        // Payments
        $permissions->add(
            'payments',
            __('cms-orders::admin.payments.Payments'),
            [
                'view',
                'create',
                'edit',
                'edit-settings',
                'delete' => function (Administrator $administrator, Payment $payment) {
                    return !$payment->driver && $administrator->hasAccess('payments.delete');
                }
            ]
        );

        // Delivery and payment
        $permissions->editSettings(
            'delivery-and-payment',
            __('cms-orders::admin.delivery-and-payment.Edit settings delivery and payment')
        );

        $permissions->add('delivery-variants', __('cms-orders::admin.delivery-and-payment.Delivery variants'));

        $permissions->add('payment-variants', __('cms-orders::admin.delivery-and-payment.Payment variants'));
    }

    public function adminMenu()
    {
        $orders = SidebarMenu::get('orders');
        if (!$orders) {
            $orders = SidebarMenu::add(__('cms-orders::admin.orders.Orders'))
                ->data('icon', 'fa-shopping-cart')
                ->data('badge_type', 'warning')
                ->data('position', 10)
                ->nickname('orders');
        }

        $count = Order::new()->count();

        $orders->data('badge', $orders->data('badge') + $count);

        // Main orders
        $orders->add(__('cms-orders::admin.orders.Orders'), route('admin.orders.index'))
            ->data('permission', 'orders.view')
            ->data('badge', $count)
            ->data('badge_type', 'warning')
            ->data('icon', 'fa-shopping-bag')
            ->data('position', 1);

        // Statuses
        $orders->add(__('cms-orders::admin.statuses.Statuses'), route('admin.order-statuses.index'))
            ->data('permission', 'order-statuses.view')
            ->data('icon', 'fa-bookmark-o')
            ->data('position', 3);

        // Deliveries
        $orders->add(__('cms-orders::admin.deliveries.Deliveries'), route('admin.deliveries.index'))
            ->data('permission', 'deliveries.view')
            ->data('icon', 'fa-truck')
            ->data('position', 8);

        // Payments
        $orders->add(__('cms-orders::admin.payments.Payments'), route('admin.payments.index'))
            ->data('permission', 'payments.view')
            ->data('icon', 'fa-money')
            ->data('position', 9);

        /*$deliveryAndPayment = $orders->add(__('cms-orders::admin.delivery-and-payment.Delivery and payment'))
            ->data('icon', 'fa-info')
            ->data('position', 11);

        $deliveryAndPayment->add(
            __('cms-orders::admin.delivery-and-payment.Delivery variants'),
            route('admin.delivery-variants.index')
        )
            ->data('permission', 'delivery-variants.view')
            ->data('icon', 'fa-truck')
            ->data('position', 1);

        $deliveryAndPayment->add(
            __('cms-orders::admin.delivery-and-payment.Payment variants'),
            route('admin.payment-variants.index')
        )
            ->data('permission', 'payment-variants.view')
            ->data('icon', 'fa-money')
            ->data('position', 2);

        $deliveryAndPayment->add(
            __('cms-core::admin.layout.Settings'),
            route('admin.delivery-and-payment.settings')
        )
            ->data('permission', 'delivery-and-payment.edit-settings')
            ->data('icon', 'fa-wrench')
            ->data('position', 3);*/
    }

    /**
     * Register module listeners.
     */
    protected function registerListeners()
    {
        parent::registerListeners();

        if (!app('isBackend')) {
            Event::listen('render_cabinet_menu', function ($menu) {
                $menu->add(__('cms-orders::site.orders.Orders history'), route('cabinet.orders'))
                    ->data('position', 3)
                    ->nickname('cabinet.orders');
            });

            // Event::listen(CreatedOrder::class, SendCreatedOrderNotification::class);
            Event::listen(CreatedOrders::class, SendCreatedOrdersNotification::class);
            Event::listen(AutoPayedOrder::class, SendAutoPayedOrderNotification::class);
            Event::listen(CanceledOrder::class, SendCanceledOrderNotification::class);

            Event::listen('created_product_review', function (ProductReview $review) {
                if (Auth::guest()) {
                    return;
                }

                $exists = OrderItem::where('product_id', $review->product_id)
                    ->whereHas('order', function ($query) {
                        $query->where('user_id', Auth::user()->getAuthIdentifier());
                    })->exists();

                if ($exists) {
                    $review->update(['already_bought' => true]);
                }
            });

            Event::listen('register_csrf_except_uri', function () {
                $except = ['payment-callback/*'];
                foreach (array_keys(app('locales')) as $locale) {
                    $except[] = "{$locale}/payment-callback/*";
                }
                return $except;
            });
        }

        if ($this->app->runningInConsole()) {
            Event::listen('cms:install:after_migrate', function (Command $command) {
                $force = (bool) $command->option('force');
                if (
                    Delivery::doesntExist()
                    && $command->confirm('Do your want run seed "Create deliveries values"', 'yes')
                ) {
                    $command->call('db:seed', ['--class' => DeliveriesSeeder::class, '--force' => $force]);
                }

                if (
                    Payment::doesntExist()
                    && $command->confirm('Do your want run seed "Create payments values"', 'yes')
                ) {
                    $command->call('db:seed', ['--class' => PaymentsSeeder::class, '--force' => $force]);
                }

                if (
                    OrderStatus::doesntExist()
                    && $command->confirm('Do your want run seed "Create basic order statuses"', 'yes')
                ) {
                    $command->call('db:seed', ['--class' => OrderStatusesSeeder::class, '--force' => $force]);
                }
            });

            Event::listen('cms:install', function (Command $command) {
                $command->call(
                    'vendor:publish',
                    ['--provider' => static::class, '--tag' => 'assets']
                );
            });
        }

        Order::observe(OrderObserver::class);
    }

    /**
     * @return array
     */
    public function sitemap()
    {
        return [
            [
                'sort' => 12,
                'parent_id' => 0,
                'name' => settings('delivery-and-payment.site.name'),
                'url' => route('delivery-and-payment'),
            ]
        ];
    }

    /**
     * @param  SitemapXmlGeneratorInterface  $sitemap
     */
    public function sitemapXml(SitemapXmlGeneratorInterface $sitemap)
    {
        $sitemap->addLocalizedRoute('delivery-and-payment');
    }
}
