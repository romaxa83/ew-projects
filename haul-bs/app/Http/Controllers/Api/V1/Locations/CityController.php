<?php

namespace App\Http\Controllers\Api\V1\Locations;

use App\Foundations\Modules\Location\Repositories\CityRepository;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Locations\CityAutocompleteRequest;
use App\Http\Resources\Locations\CityAutocompleteResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CityController extends ApiController
{
    public function __construct(
        protected CityRepository $repo
    )
    {}

    /**
     * @OA\Get (
     *     path="/api/v1/city-autocomplete",
     *     tags={"Locations"},
     *     summary="Get city-state-zip autocomplete list",
     *     operationId="GetCityStateZipAutocompleteList",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *
     *     @OA\Parameter(ref="#/components/parameters/Limit"),
     *
     *     @OA\Parameter(name="search", in="query", description="Scope for autocomplete by name", required=false,
     *         @OA\Schema(type="string", example="Los-Angeles",)
     *     ),
     *     @OA\Parameter(name="zip", in="query", description="Scope for autocomplete by zip", required=false,
     *         @OA\Schema(type="string", example="00603",)
     *     ),
     *
     *     @OA\Response(response=200, description="City data as list",
     *          @OA\JsonContent(ref="#/components/schemas/CityAutocompleteResource")
     *     ),
     *
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function list(CityAutocompleteRequest $request): JsonResponse|AnonymousResourceCollection
    {
        return CityAutocompleteResource::collection(
            $this->repo->getList($request->validated())
        );
    }
}
