<?php

namespace WezomCms\Orders\Http\Controllers\Admin;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use WezomCms\Core\Http\Controllers\AbstractCRUDController;
use WezomCms\Orders\Http\Requests\Admin\DeliveryVariantRequest;
use WezomCms\Orders\Models\DeliveryVariant;

class DeliveryVariantsController extends AbstractCRUDController
{
    /**
     * Model name.
     *
     * @var string
     */
    protected $model = DeliveryVariant::class;

    /**
     * Base view path name.
     *
     * @var string
     */
    protected $view = 'cms-orders::admin.delivery-variants';

    /**
     * Resource route name.
     *
     * @var string
     */
    protected $routeName = 'admin.delivery-variants';

    /**
     * Form request class name.
     *
     * @var string
     */
    protected $request = DeliveryVariantRequest::class;

    /**
     * Resource name for breadcrumbs and title.
     *
     * @return string
     */
    protected function title(): string
    {
        return __('cms-orders::admin.delivery-and-payment.Delivery variants');
    }

    /**
     * @param  Builder|DeliveryVariant  $query
     * @param  Request  $request
     */
    protected function selectionIndexResult($query, Request $request)
    {
        $query->sorting();
    }
}
