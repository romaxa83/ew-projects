<?php

namespace App\Http\Controllers\Api\VehicleDB;

use App\Http\Controllers\ApiController;
use App\Http\Resources\VehicleDB\DecodeVinResource;
use App\Http\Resources\VehicleDB\VehicleMakeResource;
use App\Http\Resources\VehicleDB\VehicleModelResource;
use App\Http\Resources\VehicleDB\VehicleShortlistResource;
use App\Models\VehicleDB\VehicleMake;
use App\Models\VehicleDB\VehicleModel;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Truck;
use App\Services\Vehicles\VinDecodeService;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Log;

class VehicleDBController extends ApiController
{

    private VinDecodeService $vinDecodeService;

    public function __construct(VinDecodeService $vinDecodeService)
    {
        parent::__construct();

        $this->vinDecodeService = $vinDecodeService;
    }

    /**
     * Search makes by name for autocomplete.
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     * @throws AuthorizationException
     *
     * @OA\Get(
     *     path="/api/vehicle-db/makes",
     *     tags={"VehicleDB"},
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
     */
    public function getMakes(Request $request): AnonymousResourceCollection
    {
        //$this->authorize('orders');

        $makes = [];

        if ($request->s && mb_strlen($request->s) >= 2) {
            $makes = VehicleMake::filter($request->only(['s']))
                ->orderSearchWord($request->s)
                ->get();
        }

        return VehicleMakeResource::collection($makes);
    }

    /**
     * Search models by name for autocomplete.
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     * @throws AuthorizationException
     *
     * @OA\Get(
     *     path="/api/vehicle-db/models",
     *     tags={"VehicleDB"},
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
     */
    public function getModels(Request $request): AnonymousResourceCollection
    {
        //$this->authorize('orders');

        $models = [];

        if ($request->s && mb_strlen($request->s) >= 2) {
            $models = VehicleModel::filter(
                $request->only(['s', 'make_name'])
            )
                ->selectRaw('MIN(id) as id, name')
                ->groupBy(VehicleModel::TABLE_NAME . '.name')
                ->orderSearchWord($request->s)
                ->get();
        }

        return VehicleModelResource::collection($models);
    }

    /**
     * Get make, model, year by VIN
     *
     * @param Request $request
     * @return DecodeVinResource|JsonResponse|Response
     * @throws AuthorizationException
     *
     * @OA\Get(
     *     path="/api/vehicle-db/decode-vin",
     *     tags={"VehicleDB"},
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
    public function decodeVin(Request $request)
    {
        //$this->authorize('orders');

        try {
            return DecodeVinResource::make(
                $this->vinDecodeService->decodeVin($request->vin)
            );
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_SERVICE_UNAVAILABLE);
        }
    }

    /**
     *
     * @param Request $request
     * @return DecodeVinResource|JsonResponse|Response
     * @throws AuthorizationException
     *
     * @OA\Get(
     *     path="/api/vehicle-db/unit-number/shortlist",
     *     tags={"VehicleDB"},
     *     summary="Get shortlist by unit number",
     *     operationId="Get shortlist by unit number",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),

     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/VehicleShortlistResource")
     *     ),
     * )
     *
     */
    public function shortlist(Request $request)
    {
        try {
            $trucks = Truck::query()
                ->select(['id', 'unit_number'])
                ->where('carrier_id', authUser()->getCompanyId())
                ->get()
                ->toArray()
            ;
            $trailers = Trailer::query()
                ->select(['id', 'unit_number'])
                ->where('carrier_id', authUser()->getCompanyId())
                ->get()
                ->toArray()
            ;

            return VehicleShortlistResource::collection(
                array_merge($trucks, $trailers)
            );


        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_SERVICE_UNAVAILABLE);
        }
    }
}
