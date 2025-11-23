<?php

namespace WezomCms\Orders\Http\Controllers\Site;

use WezomCms\Core\Http\Controllers\SiteController;
use WezomCms\Orders\Models\DeliveryVariant;
use WezomCms\Orders\Models\PaymentVariant;

class DeliveryAndPaymentController extends SiteController
{
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function __invoke()
    {
        $siteSettings = settings('delivery-and-payment.site', []);

        $this->seo()->fill($siteSettings, false);

        $this->addBreadcrumb(array_get($siteSettings, 'name'), route('delivery-and-payment'));

        $paymentVariants = PaymentVariant::published()
            ->sorting()
            ->get()
            ->filter(function (PaymentVariant $variant) {
                return $variant->imageExists() || $variant->text;
            });
        $deliveryVariants = DeliveryVariant::published()
            ->sorting()
            ->get()
            ->filter(function (DeliveryVariant $variant) {
                return $variant->imageExists() || $variant->text;
            });

        return view('cms-orders::site.delivery-and-payment', [
            'text1' => array_get($siteSettings, 'text1'),
            'text2' => array_get($siteSettings, 'text2'),
            'text3' => array_get($siteSettings, 'text3'),
            'paymentVariants' => $paymentVariants,
            'deliveryVariants' => $deliveryVariants,
        ]);
    }
}
