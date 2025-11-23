<?php

namespace WezomCms\Orders\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use WezomCms\Core\Http\Controllers\AdminController;
use WezomCms\Core\Traits\AjaxResponseStatusTrait;
use WezomCms\Orders\Services\SdekService;

class SdekController extends AdminController
{
    use AjaxResponseStatusTrait;

    public function searchCities(Request $request, SdekService $sdekService, ?int $regionCode = null): JsonResponse
    {
        $cities = $regionCode
            ? $sdekService
                ->getCitiesForSelect($regionCode, $request->get('term'))
                ->map(fn($city, $index) => [ 'id' => $index, 'text' => $city ])
                ->values()
            : collect();

        return $this->success([
            'results' => $cities,
        ]);
    }
}
