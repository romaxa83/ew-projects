<?php

namespace App\Http\Controllers\V1\Data\Usdot;

use App\Http\Controllers\ApiController;
use App\Http\Resources\Usdot\UsdotApiResource;
use App\Services\Usdot\UsdotService;

class UsdotController extends ApiController
{
    public function companyInfo(int $usdot, UsdotService $service): UsdotApiResource
    {
        return UsdotApiResource::make(
            $service->getCarrierInfoByUsdot($usdot)
        );
    }
}

/**
 * @see UsdotController::companyInfo()
 *
 * @OA\Get(path="/v1/data/company-info/{usdot}", tags={"V1 Data USDOT"}, summary="Get usdot info from fmcsa",
 *     operationId="Get compny info from fmcsa", deprecated=false,
 *     @OA\Parameter(ref="#/components/parameters/Content-type"),
 *     @OA\Parameter(ref="#/components/parameters/Accept"),
 *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
 *     @OA\Parameter(ref="#/components/parameters/Authorization"),
 *
 *     @OA\Response(response=200, description="Successful operation",
 *         @OA\JsonContent(ref="#/components/schemas/UsdotApiResource")
 *     ),
 *     @OA\Response(response=204, description="Successful operation",),
 * )
 */
