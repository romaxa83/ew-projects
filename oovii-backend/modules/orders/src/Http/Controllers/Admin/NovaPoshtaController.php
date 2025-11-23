<?php

namespace WezomCms\Orders\Http\Controllers\Admin;

use Illuminate\Http\Request;
use WezomCms\Core\Http\Controllers\AdminController;
use WezomCms\Core\Traits\AjaxResponseStatusTrait;
use WezomCms\Orders\Contracts\NovaPoshtaServiceInterface;
use WezomCms\Orders\Presenters\NovaPoshtaApiPresenter;

class NovaPoshtaController extends AdminController
{
    use AjaxResponseStatusTrait;

    /**
     * @param  Request  $request
     * @param  NovaPoshtaServiceInterface  $novaPoshtaService
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchCities(Request $request, NovaPoshtaServiceInterface $novaPoshtaService)
    {
        return $this->success([
            'results' => NovaPoshtaApiPresenter::presentCities($novaPoshtaService->getCities($request->get('term'))),
        ]);
    }

    /**
     * @param  Request  $request
     * @param  NovaPoshtaServiceInterface  $novaPoshtaService
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCityWarehouses(Request $request, NovaPoshtaServiceInterface $novaPoshtaService)
    {
        return $this->success([
            'results' => NovaPoshtaApiPresenter::presentWarehouses(
                $novaPoshtaService->getCityWarehouses($request->get('city_ref'))
            ),
        ]);
    }
}
