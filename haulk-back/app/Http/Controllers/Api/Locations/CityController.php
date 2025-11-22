<?php

namespace App\Http\Controllers\Api\Locations;

use App\Http\Controllers\ApiController;
use App\Http\Requests\Locations\CityRequest;
use App\Http\Resources\Locations\CityResource;
use App\Http\Resources\Locations\CityAutocompleteResource;
use App\Models\Locations\City;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Throwable;

class CityController extends ApiController
{
    /**
     * @param Request $request
     * @return AnonymousResourceCollection
     *
     * @OA\Get(
     *     path="/api/cities",
     *     tags={"Cities"},
     *     summary="Get cities paginated list",
     *     operationId="Get cities data",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(
     *          name="page",
     *          in="query",
     *          description="Page number",
     *          required=false,
     *          @OA\Schema(
     *              type="integer",
     *              default="5"
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="per_page",
     *          in="query",
     *          description="States per page",
     *          required=false,
     *          @OA\Schema(
     *              type="integer",
     *              default="10"
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="order_by",
     *          in="query",
     *          description="Field for sort",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *              default="id",
     *              enum ={"id","name","status"}
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="order_type",
     *          in="query",
     *          description="Type for sort",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *              default="asc",
     *              enum ={"asc","desc"}
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="name",
     *          in="query",
     *          description="Scope for filter by name",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *              default="Los-Angeles",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="status",
     *          in="query",
     *          description="Scope for filter by status",
     *          required=false,
     *          @OA\Schema(
     *              type="boolean",
     *              default="true",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="stateId",
     *          in="query",
     *          description="Scope for filter by state",
     *          required=false,
     *          @OA\Schema(
     *              type="integer",
     *              default="1",
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/CityPaginate")
     *     ),
     * )
     * @throws \Exception
     */
    public function index(Request $request)
    {
        $this->authorize('locations');

        $orderBy = $request->input('order_by', 'id');
        $perPage = (int) $request->input('per_page', 10);
        $orderByType = $request->input('order_type', 'asc');
        $cities = City::filter($request->only(['name', 'status', 'stateId']))
            ->orderBy($orderBy, $orderByType)
            ->oldest('id')->paginate($perPage);
        return CityResource::collection($cities);
    }

    /**
     * @param CityRequest $request
     * @return CityResource
     *
     * @OA\Post(
     *     path="/api/cities",
     *     tags={"Cities"},
     *     summary="Create city",
     *     operationId="Create city",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *     @OA\Parameter(
     *          name="name",
     *          in="query",
     *          description="City name",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *              default="Los-Angeles",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="status",
     *          in="query",
     *          description="Status of state",
     *          required=true,
     *          @OA\Schema(
     *              type="boolean",
     *              default="true",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="zip",
     *          in="query",
     *          description="City zip",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *              default="323213",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="state_id",
     *          in="query",
     *          description="State id",
     *          required=true,
     *          @OA\Schema(
     *              type="integer",
     *              default="1",
     *          )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/City")
     *     ),
     * )
     * @throws Throwable
     */
    public function store(CityRequest $request)
    {
        $this->authorize('locations create');

        $city = new City();
        $city->fill($request->all());
        $city->saveOrFail();
        return new CityResource($city);
    }

    /**
     * @param City $city
     * @return CityResource
     *
     * @OA\Get(
     *     path="/api/cities/{cityId}",
     *     tags={"Cities"},
     *     summary="Get info about city",
     *     operationId="Get city data",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/City")
     *     ),
     * )
     */
    public function show(City $city)
    {
        $this->authorize('locations read');

        return new CityResource($city);
    }

    /**
     * @param City $city
     * @param CityRequest $request
     * @return CityResource
     *
     * @OA\Put(
     *     path="/api/cities/{cityId}",
     *     tags={"Cities"},
     *     summary="Update city",
     *     operationId="Update city",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *     @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="City id",
     *          required=true,
     *          @OA\Schema(
     *              type="integer",
     *              default="1",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="name",
     *          in="query",
     *          description="City name",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *              default="Los-Angeles",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="status",
     *          in="query",
     *          description="Status of state",
     *          required=true,
     *          @OA\Schema(
     *              type="boolean",
     *              default="true",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="zip",
     *          in="query",
     *          description="City zip",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *              default="323213",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="state_id",
     *          in="query",
     *          description="State id",
     *          required=true,
     *          @OA\Schema(
     *              type="integer",
     *              default="1",
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/City")
     *     ),
     * )
     */
    public function update(City $city, CityRequest $request)
    {
        $this->authorize('locations update');

        $city->fill($request->all());
        $city->update();
        return new CityResource($city);
    }

    public function destroy(int $id)
    {
        // TODO IF NEED
    }

    /**
     * @param Request $request
     * @return AnonymousResourceCollection
     *
     * @OA\Get(
     *     path="/api/city-autocomplete",
     *     tags={"Cities"},
     *     summary="Get city-state-zip autocomplete list",
     *     operationId="Get city-state-zip autocomplete list",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(
     *          name="s",
     *          in="query",
     *          description="Scope for autocomplete by name",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *              default="Los-Angeles",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="zip",
     *          in="query",
     *          description="Scope for autocomplete by zip",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *              default="12345",
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/CityAutocomplete")
     *     ),
     * )
     * @throws \Exception
     */
    public function autocomplete(Request $request)
    {
        $cities = City::filter($request->only(['s', 'zip']))
            ->with('state')
            ->orderBy('country_code', 'asc')
            ->orderBy('name', 'asc')
            ->orderBy('zip', 'asc')
            ->limit(50)
            ->get();

        return CityAutocompleteResource::collection($cities);
    }
}
