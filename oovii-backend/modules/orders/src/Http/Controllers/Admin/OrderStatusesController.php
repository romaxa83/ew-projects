<?php

namespace WezomCms\Orders\Http\Controllers\Admin;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use WezomCms\Core\Http\Controllers\AbstractCRUDController;
use WezomCms\Orders\Http\Requests\Admin\OrderStatusRequest;
use WezomCms\Orders\Models\OrderStatus;

class OrderStatusesController extends AbstractCRUDController
{
    /**
     * Model name.
     *
     * @var string
     */
    protected $model = OrderStatus::class;

    /**
     * Base view path name.
     *
     * @var string
     */
    protected $view = 'cms-orders::admin.statuses';

    /**
     * Resource route name.
     *
     * @var string
     */
    protected $routeName = 'admin.order-statuses';

    /**
     * Form request class name.
     *
     * @var string
     */
    protected $request = OrderStatusRequest::class;

    /**
     * Resource name for breadcrumbs and title.
     *
     * @return string
     */
    protected function title(): string
    {
        return __('cms-orders::admin.statuses.Statuses');
    }

    /**
     * @param  Builder|OrderStatus  $query
     * @param  Request  $request
     */
    protected function selectionIndexResult($query, Request $request)
    {
        $query->sorting();
    }
}
