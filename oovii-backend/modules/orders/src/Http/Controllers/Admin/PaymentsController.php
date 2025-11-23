<?php

namespace WezomCms\Orders\Http\Controllers\Admin;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use WezomCms\Core\Http\Controllers\AbstractCRUDController;
use WezomCms\Core\Settings\Fields\AbstractField;
use WezomCms\Core\Settings\Fields\Checkbox;
use WezomCms\Core\Settings\Fields\Input;
use WezomCms\Core\Settings\Fields\Textarea;
use WezomCms\Core\Settings\MultilingualGroup;
use WezomCms\Core\Settings\RenderSettings;
use WezomCms\Core\Settings\Tab;
use WezomCms\Core\Traits\SettingControllerTrait;
use WezomCms\Orders\Http\Requests\Admin\PaymentRequest;
use WezomCms\Orders\Models\Payment;

class PaymentsController extends AbstractCRUDController
{
    use SettingControllerTrait;

    /**
     * Model name.
     *
     * @var string
     */
    protected $model = Payment::class;

    /**
     * Base view path name.
     *
     * @var string
     */
    protected $view = 'cms-orders::admin.payments';

    /**
     * Resource route name.
     *
     * @var string
     */
    protected $routeName = 'admin.payments';

    /**
     * Form request class name.
     *
     * @var string
     */
    protected $request = PaymentRequest::class;

    /**
     * Resource name for breadcrumbs and title.
     *
     * @return string
     */
    protected function title(): string
    {
        return __('cms-orders::admin.payments.Payments');
    }

    /**
     * @param  Builder|Payment  $query
     * @param  Request  $request
     */
    protected function selectionIndexResult($query, Request $request)
    {
        $query->sorting();
    }

    /**
     * @return array|AbstractField[]|MultilingualGroup[]
     * @throws \Exception
     */
    protected function settings(): array
    {
        $liqPay = new RenderSettings(
            new Tab('liq_pay', __('cms-orders::admin.payments.LiqPay'), 1, 'fa-file-text')
        );

        $result = [];

        $result[] = Input::make($liqPay)
            ->setKey('public_key')
            ->setName(__('cms-orders::admin.payments.Public key'))
            ->setRules('required')
            ->setSort(1);

        $result[] = Input::make($liqPay)
            ->setKey('private_key')
            ->setName(__('cms-orders::admin.payments.Private key'))
            ->setRules('required')
            ->setSort(2);

        $result[] = Checkbox::make($liqPay)
            ->setKey('sandbox')
            ->setName(__('cms-orders::admin.payments.Sandbox mode'))
            ->setValuesList([1 => __('cms-orders::admin.payments.Enabled')])
            ->setSort(2);

        $paymentDescription = new RenderSettings(
            new Tab('payment', __('cms-orders::admin.payments.Description payment'), 2, 'fa-folder-o')
        );

        $result[] = Textarea::make($paymentDescription)
            ->setName(__('cms-orders::admin.payments.Description payment'))
            ->setIsMultilingual()
            ->setKey('payment')
            ->setRules('required');

        return $result;
    }
}
