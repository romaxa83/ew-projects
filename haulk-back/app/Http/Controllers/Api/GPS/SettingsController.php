<?php

namespace App\Http\Controllers\Api\GPS;

use App\Http\Controllers\ApiController;
use App\Http\Requests\GPS\CarrierSpeedLimitRequest;
use App\Http\Resources\Carrier\CarrierResource;
use Illuminate\Http\JsonResponse;

class SettingsController extends ApiController
{
    /**
     * Update speed limit
     *
     * @param CarrierSpeedLimitRequest $request
     * @return CarrierResource|JsonResponse
     *
     * @OA\Put(
     *     path="/api/gps/speed-limit",
     *     tags={"Carrier GPS"},
     *     summary="Update speed limit",
     *     operationId="Update speed limit",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(
     *          name="speed_limit",
     *          in="query",
     *          description="Speed limit",
     *          required=true,
     *          @OA\Schema(
     *              type="float",
     *              default="70.0",
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/CarrierProfileResource")
     *     ),
     * )
     */
    public function updateSpeedLimit(CarrierSpeedLimitRequest $request)
    {
        $this->authorize('gps-settings update-speed-limit');

        $validated = $request->validated();

        $company = $request->user()->getCompany();
        $company->fill($validated);
        $company->save();

        return CarrierResource::make($company);
    }
}
