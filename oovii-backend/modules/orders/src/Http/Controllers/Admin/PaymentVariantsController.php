<?php

namespace WezomCms\Orders\Http\Controllers\Admin;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use WezomCms\Core\Http\Controllers\AbstractCRUDController;
use WezomCms\Orders\Http\Requests\Admin\PaymentVariantRequest;
use WezomCms\Orders\Models\PaymentVariant;

class PaymentVariantsController extends AbstractCRUDController
{
    /**
     * Model name.
     *
     * @var string
     */
    protected $model = PaymentVariant::class;

    /**
     * Base view path name.
     *
     * @var string
     */
    protected $view = 'cms-orders::admin.payment-variants';

    /**
     * Resource route name.
     *
     * @var string
     */
    protected $routeName = 'admin.payment-variants';

    /**
     * Form request class name.
     *
     * @var string
     */
    protected $request = PaymentVariantRequest::class;

    /**
     * Resource name for breadcrumbs and title.
     *
     * @return string
     */
    protected function title(): string
    {
        return __('cms-orders::admin.delivery-and-payment.Payment variants');
    }

    /**
     * @param  Builder|PaymentVariant  $query
     * @param  Request  $request
     */
    protected function selectionIndexResult($query, Request $request)
    {
        $query->sorting();
    }
}
