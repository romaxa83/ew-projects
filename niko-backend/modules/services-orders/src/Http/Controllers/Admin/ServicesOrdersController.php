<?php

namespace WezomCms\ServicesOrders\Http\Controllers\Admin;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use WezomCms\Core\Http\Controllers\AbstractCRUDController;
use WezomCms\Core\Settings\AdminLimit;
use WezomCms\Core\Settings\Fields\AbstractField;
use WezomCms\Core\Settings\MultilingualGroup;
use WezomCms\Core\Traits\SettingControllerTrait;
use WezomCms\Services\Models\Service;
use WezomCms\Services\Types\ServiceType;
use WezomCms\ServicesOrders\Http\Requests\Admin\ServiceOrderRequest;
use WezomCms\ServicesOrders\Models\ServicesOrder;
use WezomCms\ServicesOrders\Types\OrderStatus;

class ServicesOrdersController extends AbstractCRUDController
{
    use SettingControllerTrait;

    /**
     * Model name.
     *
     * @var string
     */
    protected $model = ServicesOrder::class;

    /**
     * Base view path name.
     *
     * @var string
     */
    protected $view = 'cms-services-orders::admin';

    /**
     * Resource route name.
     *
     * @var string
     */
    protected $routeName = 'admin.services-orders';

    /**
     * Form request class name.
     *
     * @var string
     */
    protected $request = ServiceOrderRequest::class;

    protected $hideCreateBnt = true;

    /**
     * Resource name for breadcrumbs and title.
     *
     * @return string
     */
    protected function title(): string
    {
        return __('cms-services-orders::admin.Service orders');
    }

    /**
     * @param  Builder  $query
     * @param  Request  $request
     */
    protected function selectionIndexResult($query, Request $request)
    {
        return $query->notReject();
    }

    /**
     * @param  ServicesOrder  $obj
     * @param  array  $viewData
     * @return array
     */
    protected function formData($obj, array $viewData): array
    {
        return [
            'services' => Service::getForSelect(),
        ];
    }

    /**
     * @return array|AbstractField[]|MultilingualGroup[]
     * @throws \Exception
     */
    protected function settings(): array
    {
        return [AdminLimit::make()];
    }
}
