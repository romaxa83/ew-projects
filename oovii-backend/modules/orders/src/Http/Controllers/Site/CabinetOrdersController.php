<?php

namespace WezomCms\Orders\Http\Controllers\Site;

use Auth;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use WezomCms\Core\Http\Controllers\SiteController;
use WezomCms\Core\Traits\LoadMoreTrait;
use WezomCms\Orders\Models\Order;

class CabinetOrdersController extends SiteController
{
    use LoadMoreTrait;

    /**
     * @return Factory|View
     */
    public function __invoke()
    {
        // Meta
        $settings = settings('orders.site', []);

        // Select orders
        $orders = Order::where('user_id', Auth::id())
            ->with('delivery', 'payment')
            ->latest('id')
            ->paginate(array_get($settings, 'limit', 10));

        // Render
        return $this->viewLoadMore(
            $orders,
            function () use ($orders) {
                return view('cms-orders::site.partials.cabinet-orders-list', compact('orders'));
            },
            function () use ($orders, $settings) {
                $this->addBreadcrumb(array_get($settings, 'name'));
                $this->seo()->fill($settings)->noIndex();

                return view('cms-orders::site.cabinet.orders', compact('orders'));
            }
        );
    }
}
