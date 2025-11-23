<?php

namespace WezomCms\Orders\Http\Controllers\Site;

use WezomCms\Core\Foundation\JsResponse;
use WezomCms\Core\Http\Controllers\SiteController;
use WezomCms\Orders\Contracts\NovaPoshtaServiceInterface;
use WezomCms\Orders\Presenters\NovaPoshtaApiPresenter;
use WezomLaravel\Orders\Http\Requests\Site\NovaPoshtaCitiesRequest;
use WezomLaravel\Orders\Http\Requests\Site\NovaPoshtaCityWarehousesRequest;

class NovaPoshtaController extends SiteController
{
    /**
     * @param  NovaPoshtaCitiesRequest  $request
     * @param  NovaPoshtaServiceInterface  $novaPoshta
     * @return JsResponse
     */
    public function getCities(NovaPoshtaCitiesRequest $request, NovaPoshtaServiceInterface $novaPoshta)
    {
        return JsResponse::make()->massAssigment([
            'cities' => NovaPoshtaApiPresenter::presentCities($novaPoshta->getCities($request->get('query'))),
        ]);
    }

    /**
     * @param  NovaPoshtaCityWarehousesRequest  $request
     * @param  NovaPoshtaServiceInterface  $novaPoshta
     * @return JsResponse
     */
    public function getCityWarehouses(NovaPoshtaCityWarehousesRequest $request, NovaPoshtaServiceInterface $novaPoshta)
    {
        return JsResponse::make()->massAssigment([
            'warehouses' => NovaPoshtaApiPresenter::presentWarehouses(
                $novaPoshta->getCityWarehouses($request->get('city_ref'))
            ),
        ]);
    }
}
