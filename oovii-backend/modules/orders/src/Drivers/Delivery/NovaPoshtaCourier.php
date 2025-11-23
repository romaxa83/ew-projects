<?php

namespace WezomCms\Orders\Drivers\Delivery;

use Illuminate\Contracts\View\View;
use WezomCms\Orders\Models\OrderDeliveryInformation;

class NovaPoshtaCourier extends Courier
{
    public const KEY = 'nova-poshta-courier';

    /**
     * @param  OrderDeliveryInformation  $storage
     * @return View
     */
    public function renderAdminFormInputs(OrderDeliveryInformation $storage): View
    {
        return view('cms-orders::admin.drivers.delivery.nova-poshta-courier', compact('storage'));
    }

    /**
     * @param  OrderDeliveryInformation  $storage
     * @return View
     */
    public function renderAdminFormData(OrderDeliveryInformation $storage): View
    {
        return view('cms-orders::admin.drivers.delivery.nova-poshta-courier-data', compact('storage'));
    }
}
