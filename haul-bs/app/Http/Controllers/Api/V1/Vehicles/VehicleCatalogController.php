<?php

namespace App\Http\Controllers\Api\V1\Vehicles;

use App\Enums\Vehicles\VehicleType;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Common\SearchRequest;
use App\Foundations\Http\Requests\Common\SearchRequest as FoundationSearchRequest;
use App\Http\Requests\Vehicles\DecodeVinRequest;
use App\Http\Requests\Vehicles\ModelListRequest;
use App\Http\Resources\Vehicles\DecodeVinResource;
use App\Http\Resources\Vehicles\MakeResource;
use App\Http\Resources\Vehicles\ModelResource;
use App\Http\Resources\Vehicles\TypeResource;
use App\Http\Resources\Vehicles\VehicleSearchResource;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Truck;
use App\Repositories\Vehicles\MakeRepository;
use App\Repositories\Vehicles\ModelRepository;
use App\Services\Vehicles\DecoderVin\VinDecodeService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\ResourceCollection;

class VehicleCatalogController extends ApiController
{
    public function __construct(
        protected MakeRepository $makeRepo,
        protected ModelRepository $modelRepo,
        protected VinDecodeService $vinDecodeService
    )
    {}

    /**
     * @OA\Get(
     *     path="/api/v1/vehicles/makes",
     *     tags={"Vehicles catalog"},
     *     security={{"Basic": {}}},
     *     summary="Get makes for autocomplete",
     *     operationId="GetMakesForAutocomplete",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Parameter(name="search", in="query", required=false,
     *         description="Scope for filter by name",
     *         @OA\Schema(type="string", default="null",)
     *     ),
     *
     *     @OA\Response(response=200, description="Vehicle make data",
     *         @OA\JsonContent(ref="#/components/schemas/VehicleMakeResource")
     *     ),
     *
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function makes(SearchRequest $request): ResourceCollection
    {
        return MakeResource::collection(
            $this->makeRepo->listWithSort($request->validated())
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v1/vehicles/models",
     *     tags={"Vehicles catalog"},
     *     security={{"Basic": {}}},
     *     summary="Get models for autocomplete",
     *     operationId="GetModelsForAutocomplete",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Parameter(name="search", in="query", required=false,
     *         description="Scope for filter by name",
     *         @OA\Schema(type="string", default="null",)
     *     ),
     *     @OA\Parameter(name="make_name", in="query", required=false,
     *         description="Scope for filter by make name",
     *         @OA\Schema(type="string", default="null",)
     *     ),
     *
     *     @OA\Response(response=200, description="Vehicle model data",
     *         @OA\JsonContent(ref="#/components/schemas/VehicleModelResource")
     *     ),
     *
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function models(ModelListRequest $request): ResourceCollection
    {
        return ModelResource::collection(
            $this->modelRepo->listWithSort($request->validated())
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v1/vehicles/types",
     *     tags={"Vehicles catalog"},
     *     security={{"Basic": {}}},
     *     summary="Get vehicle types list",
     *     operationId="GetVehicleTypesList",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Response(response=200, description="Vehicle type data",
     *         @OA\JsonContent(ref="#/components/schemas/VehicleTypeResource")
     *     ),
     *
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function types(): ResourceCollection
    {
        return TypeResource::collection(
            VehicleType::getTypesList()
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v1/vehicles/decode-vin",
     *     tags={"Vehicles catalog"},
     *     security={{"Basic": {}}},
     *     summary="Get make, model, year by VIN",
     *     operationId="GetMakeModelYearByVIN",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Parameter(name="vin", in="query", required=false,
     *         description="vehicle vin",
     *         @OA\Schema(type="string", default="null",)
     *     ),
     *
     *     @OA\Response(response=200, description="Vehicle type data",
     *         @OA\JsonContent(ref="#/components/schemas/DecodeVinResource")
     *     ),
     *
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function decodeVin(DecodeVinRequest $request): DecodeVinResource
    {
        return DecodeVinResource::make(
            $this->vinDecodeService->decodeVin($request->vin)
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v1/vehicles",
     *     tags={"Vehicles catalog"},
     *     security={{"Basic": {}}},
     *     summary="Get vehicles list",
     *     operationId="GetVehiclesList",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Parameter(ref="#/components/parameters/ID"),
     *     @OA\Parameter(ref="#/components/parameters/Limit"),
     *
     *     @OA\Parameter(name="search", in="query", required=false,
     *          description="Scope for filter by name, email, phone",
     *          @OA\Schema( type="string", default="name",)
     *     ),
     *
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/VehicleSearchResource")
     *     ),
     *
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function getVehicles(FoundationSearchRequest $request): AnonymousResourceCollection
    {
        $trailers = Trailer::query()
            ->selectRaw('id, vin, make, model, year, \'trailer\' as vehicle_form')
            ->filter($request->validated());

        $vehicles = Truck::query()
            ->selectRaw('id, vin, make, model, year, \'truck\' as vehicle_form')
            ->filter($request->validated())
            ->union($trailers)
            ->limit($request->limit ?? FoundationSearchRequest::DEFAULT_LIMIT)
            ->get();

        return VehicleSearchResource::collection($vehicles);
    }
}
