<?php

namespace WezomCms\Users\Http\Controllers\Admin;

use Illuminate\Foundation\Http\FormRequest;
use WezomCms\Core\Http\Controllers\AbstractCRUDController;
use WezomCms\ServicesOrders\Helpers\Price;
use WezomCms\Users\Http\Requests\Admin\UpdateLoyaltyLevel;
use WezomCms\Users\Models\LoyaltyLevel;

class LoyaltyLevelController extends AbstractCRUDController
{

    /**
     * Model name.
     *
     * @var string
     */
    protected $model = LoyaltyLevel::class;

    /**
     * Base view path name.
     *
     * @var string
     */
    protected $view = 'cms-users::admin.loyalty-levels';

    /**
     * Resource route name.
     *
     * @var string
     */
    protected $routeName = 'admin.loyalties';

    /**
     * Form request class name for "update" action.
     *
     * @var string
     */
    protected $updateRequest = UpdateLoyaltyLevel::class;

    protected $hideCreateBnt = true;

    /**
     * Resource name for breadcrumbs and title.
     *
     * @return string
     */
    protected function title(): string
    {
        return __('cms-users::admin.loyalty level title');
    }

    /**
     * @param  LoyaltyLevel  $model
     * @param  FormRequest  $request
     * @return array
     */
    protected function fill($obj, FormRequest $request): array
    {
        $data = $request->all();
        $data['sum_services'] = Price::toDB($request['sum_service']);
        $data['discount_sto'] = Price::toDB($request['discount_sto']);
        $data['discount_spares'] = Price::toDB($request['discount_spares']);

        return $data;
    }
}
