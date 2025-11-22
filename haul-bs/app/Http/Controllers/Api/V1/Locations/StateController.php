<?php

namespace App\Http\Controllers\Api\V1\Locations;

use App\Foundations\Modules\Location\Repositories\StateRepository;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Locations\StateFilterRequest;
use App\Http\Resources\Locations\StateResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StateController extends ApiController
{
    public function __construct(
        protected StateRepository $repo
    )
    {}

    /**
     * @OA\Get (
     *     path="/api/v1/state-list",
     *     tags={"Locations"},
     *     security={{"Basic": {}}},
     *     summary="Get states list",
     *     operationId="GetStatesList",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Parameter(name="name", in="query", description="Scope for filter by name",
     *           required=false,
     *           @OA\Schema(type="string", example="California")
     *      ),
     *
     *     @OA\Response(response=200, description="State data as list",
     *          @OA\JsonContent(ref="#/components/schemas/StateResource")
     *     ),
     *
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function list(StateFilterRequest $request): JsonResponse|AnonymousResourceCollection
    {
        return StateResource::collection(
            $this->repo->getStatesCaching(filters: $request->validated())
        );
    }
}

