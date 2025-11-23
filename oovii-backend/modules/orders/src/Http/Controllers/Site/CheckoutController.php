<?php

namespace WezomCms\Orders\Http\Controllers\Site;

use WezomCms\Core\Http\Controllers\SiteController;

class CheckoutController extends SiteController
{
    /**
     * @return \Illuminate\Http\Response|object
     */
    public function __invoke()
    {
        $this->seo()->setPageName(__('cms-orders::site.checkout.Checkout'));

        return \Response::view('cms-orders::site.checkout')
            ->setCache(['no_cache' => true, 'no_store' => true, 'must_revalidate' => true]);
    }
}
