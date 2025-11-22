<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Http\Resources\TimezoneListResource;
use App\Services\TimezoneService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TimezoneController extends ApiController
{

    /**
     *
     * @param TimezoneService $timezoneService
     * @return AnonymousResourceCollection
     *
     * @OA\Get(
     *     path="/api/timezone-list",
     *     tags={"Timezones"},
     *     summary="Get timezone list",
     *     operationId="Get timezone list",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/TimezoneListResource")
     *     ),
     * )
     */
    public function timezoneList(TimezoneService $timezoneService): AnonymousResourceCollection
    {
        return TimezoneListResource::collection(
            $timezoneService->getTimezonesList()
        );
    }
}
