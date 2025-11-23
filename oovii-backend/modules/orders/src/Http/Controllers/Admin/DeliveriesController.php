<?php

namespace WezomCms\Orders\Http\Controllers\Admin;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use WezomCms\Core\Http\Controllers\AbstractCRUDController;
use WezomCms\Core\Settings\Fields\AbstractField;
use WezomCms\Core\Settings\Fields\Input;
use WezomCms\Core\Settings\Fields\Status;
use WezomCms\Core\Settings\MultilingualGroup;
use WezomCms\Core\Settings\RenderSettings;
use WezomCms\Core\Traits\SettingControllerTrait;
use WezomCms\Orders\Http\Requests\Admin\DeliveryRequest;
use WezomCms\Orders\Models\Delivery;
use WezomCms\Orders\Models\Payment;

class DeliveriesController extends AbstractCRUDController
{
    use SettingControllerTrait;

    /**
     * Model name.
     *
     * @var string
     */
    protected $model = Delivery::class;

    /**
     * Base view path name.
     *
     * @var string
     */
    protected $view = 'cms-orders::admin.deliveries';

    /**
     * Resource route name.
     *
     * @var string
     */
    protected $routeName = 'admin.deliveries';

    /**
     * Form request class name.
     *
     * @var string
     */
    protected $request = DeliveryRequest::class;

    /**
     * Resource name for breadcrumbs and title.
     *
     * @return string
     */
    protected function title(): string
    {
        return __('cms-orders::admin.deliveries.Deliveries');
    }

    /**
     * @param  Builder|Delivery  $query
     * @param  Request  $request
     */
    protected function selectionIndexResult($query, Request $request)
    {
        $query->sorting();
    }

    /**
     * @param  Delivery  $obj
     * @param  array  $viewData
     * @return array
     */
    protected function formData($obj, array $viewData): array
    {
        return [
            'payments' => Payment::getForSelect(),
            'selectedPayments' => $obj->payments()->pluck('id')->toArray(),
        ];
    }

    /**
     * @param  Delivery  $obj
     * @param  Request  $request
     */
    protected function afterSuccessfulSave($obj, Request $request)
    {
        $obj->payments()->sync($request->get('PAYMENTS', []));
    }

    /**
     * @return array|AbstractField[]|MultilingualGroup[]
     * @throws \Exception
     */
    protected function settings(): array
    {
        return [
            Status::make(RenderSettings::siteTab())
                ->setKey('sdek_test')
                ->default(true)
                ->setName(__('cms-orders::admin.deliveries.SDEK test'))
                ->setSort(1),
            Input::make(RenderSettings::siteTab())
                ->setKey('sdek_account')
                ->setName(__('cms-orders::admin.deliveries.SDEK account'))
                ->setRules('required')
                ->setSort(2),
            Input::make(RenderSettings::siteTab())
                ->setKey('sdek_password')
                ->setName(__('cms-orders::admin.deliveries.SDEK password'))
                ->setRules('required')
                ->setSort(3),
            Input::make(RenderSettings::siteTab())
                ->setKey('sdek_order_webhook_uuid')
                ->setName(__('cms-orders::admin.deliveries.SDEK webhook uuid'))
                ->setAttribute('readonly', true)
                ->setSort(4),
        ];
    }
}
