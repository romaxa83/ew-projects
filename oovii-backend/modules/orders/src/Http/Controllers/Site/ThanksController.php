<?php

namespace WezomCms\Orders\Http\Controllers\Site;

use WezomCms\Core\Http\Controllers\SiteController;
use WezomCms\Orders\Models\Order;

class ThanksController extends SiteController
{
    /**
     * @param $order
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function __invoke(Order $order)
    {
        if (session('order-id') != $order->id) {
            return redirect()->route('home');
        }

        $settings = settings('orders.site_thanks', []);

        // Meta
        $this->addBreadcrumb(array_get($settings, 'title', array_get($settings, 'name')));
        $this->seo()
            ->setPageName(array_get($settings, 'name'));

        return view('cms-orders::site.thanks', compact('order'));
    }
}
