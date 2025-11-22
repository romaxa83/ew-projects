<?php

namespace App\Http\Controllers\Api\BodyShop\Vehicles;

use App\Http\Requests\SearchRequest;
use App\Http\Resources\Orders\VehicleTypesListResource;
use App\Http\Resources\Vehicles\VehicleResource;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Truck;
use App\Models\Vehicles\Vehicle;
use App\Services\BodyShop\Sync\Commands\Vehicles\SetVehicleCommand;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class VehicleDBController extends \App\Http\Controllers\Api\VehicleDB\VehicleDBController
{
    /**
     * @OA\Get(
     *     path="/api/body-shop/vehicle-db/makes",
     *     tags={"VehicleDB Body Shop"},
     *     summary="Get makes for autocomplete",
     *     operationId="Get vehicle makes data",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(
     *          name="s",
     *          in="query",
     *          description="Make name",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/VehicleMakeResource")
     *     ),
     * )
     *
     * @OA\Get(
     *     path="/api/body-shop/vehicle-db/models",
     *     tags={"VehicleDB Body Shop"},
     *     summary="Get models for autocomplete",
     *     operationId="Get vehicle models data",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(
     *          name="make_name",
     *          in="query",
     *          description="Make name",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="s",
     *          in="query",
     *          description="Model name",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/VehicleModelResource")
     *     ),
     * )
     *
     * @OA\Get(
     *     path="/api/body-shop/vehicle-db/decode-vin",
     *     tags={"VehicleDB Body Shop"},
     *     summary="Get make, model, year by VIN",
     *     operationId="Get make, model, year by VIN",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(
     *          name="vin",
     *          in="query",
     *          description="vehicle vin",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/DecodeVinResource")
     *     ),
     * )
     *
     */

    /**
     *
     * @return AnonymousResourceCollection
     *
     * @OA\Get(
     *     path="/api/body-shop/vehicle-db/types",
     *     tags={"VehicleDB Body Shop"},
     *     summary="Get vehicle types list",
     *     operationId="Get vehicle types",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/VehicleTypesListResource")
     *     ),
     * )
     *
     */
    public function getTypes(): AnonymousResourceCollection
    {
        return VehicleTypesListResource::collection(
            Vehicle::getTypesList()
        );
    }

    /**
     *
     * @return AnonymousResourceCollection
     *
     * @OA\Get(
     *     path="/api/body-shop/vehicle-db/vehicles",
     *     tags={"Vehicles Body Shop"},
     *     summary="Get vehicles list",
     *     operationId="Get vehicles",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="q", in="query", description="Scope for filter by name, email, phone", required=false,
     *          @OA\Schema( type="string", default="name",)
     *     ),
     *     @OA\Parameter(name="searchid", in="query", description="Filter by id", required=false,
     *          @OA\Schema( type="integer", default="1",)
     *     ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Vehicle")
     *     ),
     * )
     *
     */
    public function getVehicles(SearchRequest $request): AnonymousResourceCollection
    {
        $trailers = Trailer::query()
            ->selectRaw('id, vin, make, model, year, \'trailer\' as vehicle_form')
            ->withBodyShopCompanies()
            ->filter($request->validated());

        $vehicles = Truck::query()
            ->selectRaw('id, vin, make, model, year, \'truck\' as vehicle_form')
            ->withBodyShopCompanies()
            ->filter($request->validated())
            ->union($trailers)
            ->limit(SearchRequest::DEFAULT_LIMIT)
            ->get();

        return VehicleResource::collection($vehicles);
    }
}
