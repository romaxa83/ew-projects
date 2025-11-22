<?php

namespace App\Http\Controllers\Api\V1\Locations;

use App\Foundations\Modules\Location\Services\TimezoneService;
use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\Locations\TimezoneResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TimezoneController extends ApiController
{
    public function __construct(protected TimezoneService $service)
    {}

    /**
     * @OA\Get (
     *     path="/api/v1/timezone",
     *     tags={"Locations"},
     *     summary="Get timezone list",
     *     operationId="GetTimezoneList",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *
     *     @OA\Response(response=200, description="Timezone data as list",
     *          @OA\JsonContent(ref="#/components/schemas/TimezoneResource")
     *     ),
     *
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function list(): JsonResponse|AnonymousResourceCollection
    {
        return TimezoneResource::collection(
            $this->service->getTimezonesList()
        );
    }
}
