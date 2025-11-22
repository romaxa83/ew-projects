<?php

namespace App\Http\Controllers\Api\GPS;

use App\Http\Controllers\ApiController;
use App\Http\Requests\Saas\GPS\History\HistoryExportFilterRequest;
use App\Http\Requests\Saas\GPS\History\HistoryFilterRequest;
use App\Http\Requests\Saas\GPS\History\Route\RouteCreateRequest;
use App\Http\Requests\Saas\GPS\History\Route\RouteFilterRequest;
use App\Http\Resources\Saas\GPS\History\HistoryAdditionalResource;
use App\Http\Resources\Saas\GPS\History\HistoryResource;
use App\Models\GPS\Route;
use App\Repositories\Saas\GPS\HistoryRepository;
use App\Services\Saas\GPS\Histories\HistoryService;
use App\Services\Saas\GPS\Histories\RouteService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class HistoryController extends ApiController
{
    protected HistoryRepository $repo;
    protected HistoryService $service;

    public function __construct(
        HistoryRepository $repo,
        HistoryService $service
    )
    {

        parent::__construct();

        $this->repo = $repo;
        $this->service = $service;
    }

    /**
     * @param HistoryFilterRequest $request
     * @return AnonymousResourceCollection
     * @throws AuthorizationException
     *
     * @OA\Get(path="/api/gps/history",
     *     tags={"Carrier GPS History"},
     *     summary="Returns history list",
     *     operationId="history list",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="page", in="query", description="Page number", required=false,
     *          @OA\Schema(type="integer", default="5")
     *     ),
     *     @OA\Parameter(name="per_page", in="query", description="Per page", required=false,
     *          @OA\Schema(type="integer", default="10")
     *     ),
     *     @OA\Parameter(name="order_type", in="query", description="Type for sort by field - received_at", required=false,
     *          @OA\Schema(type="string", default="desc", enum ={"asc","desc"})
     *     ),
     *     @OA\Parameter(name="id", in="query", description="ID", required=false,
     *           @OA\Schema(type="integer", example="1")
     *      ),
     *     @OA\Parameter(name="event_type", in="query", description="Event type", required=false,
     *            @OA\Schema(type="string", example="driving", enum={"driving", "idle", "long_idle", "engine_off", "change_driver"})
     *     ),
     *     @OA\Parameter(name="alert_type", in="query", description="Alert type", required=false,
     *            @OA\Schema(type="string", example="driving", enum={"device_battery", "device_connection", "speeding"})
     *     ),
     *     @OA\Parameter(name="device_id", in="query", description="Device ID", required=false,
     *           @OA\Schema(type="integer", example="5")
     *      ),
     *     @OA\Parameter(name="truck_id", in="query", description="Truck ID", required=false,
     *          @OA\Schema(type="integer", example="5")
     *     ),
     *     @OA\Parameter(name="trailer_id", in="query", description="Trailer ID", required=false,
     *           @OA\Schema(type="integer", example="5")
     *     ),
     *     @OA\Parameter(name="driver_id", in="query", description="Driver ID", required=false,
     *            @OA\Schema(type="integer", example="5")
     *     ),
     *     @OA\Parameter (name="date_from", in="query", description="Date from", required=false,
     *          @OA\Schema (type="string", example="2023-08-22", description="format - Y-m-d")
     *      ),
     *      @OA\Parameter (name="date_to", in="query", description="Date to", required=false,
     *           @OA\Schema(type="string", example="2023-08-22", description="format - Y-m-d")
     *      ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/HistoryDevicePaginatedResource")
     *     ),
     * )
     */
    public function index(HistoryFilterRequest $request): AnonymousResourceCollection
    {
        $models = $this->repo->getByCompanyPagination(
            authUser()->getCompanyId(),
            $request->all()
        );

        return HistoryResource::collection($models);
    }

    /**
     * @param HistoryFilterRequest $request
     * @return AnonymousResourceCollection
     * @throws AuthorizationException
     *
     * @OA\Get(path="/api/gps/history/coords-route",
     *     tags={"Carrier GPS History"},
     *     summary="Returns coord for route",
     *     operationId="HistoryCoordsRoute",
     *     deprecated=true,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Parameter(name="truck_id", in="query", description="Truck ID", required=false,
     *          @OA\Schema(type="integer", example="5")
     *     ),
     *     @OA\Parameter(name="trailer_id", in="query", description="Trailer ID", required=false,
     *           @OA\Schema(type="integer", example="5")
     *     ),
     *     @OA\Parameter (name="date_from", in="query", description="Date to", required=false,
     *           @OA\Schema(type="string", example="10/25/2023", description="format - m/d/Y")
     *     ),
     *     @OA\Parameter (name="date_to", in="query", description="Date to", required=false,
     *           @OA\Schema(type="string", example="10/25/2023", description="format - m/d/Y")
     *     ),
     *
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/HistoryDeviceRouteArrayResource")
     *     ),
     * )
     */
    public function coordsRoute(HistoryFilterRequest $request): JsonResponse
    {
        return $this->makeResponse(
            $this->service->getCoordsForRoute($request->validated())
        );
    }

    /**
     * @param HistoryFilterRequest $request
     * @return AnonymousResourceCollection
     * @throws AuthorizationException
     *
     * @OA\Get(path="/api/gps/history/route",
     *     tags={"Carrier GPS History"},
     *     summary="Returns coord for route",
     *     operationId="HistoryRoute",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Parameter(name="truck_id", in="query", description="Truck ID", required=false,
     *          @OA\Schema(type="integer", example="5")
     *     ),
     *     @OA\Parameter(name="trailer_id", in="query", description="Trailer ID", required=false,
     *           @OA\Schema(type="integer", example="5")
     *     ),
     *     @OA\Parameter(name="device_id", in="query", description="Device ID", required=false,
     *           @OA\Schema(type="integer", example="5")
     *     ),
     *     @OA\Parameter (name="date", in="query", description="Date to", required=false,
     *           @OA\Schema(type="string", example="2023-08-22", description="format - Y-m-d")
     *     ),
     *
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/HistoryDeviceRouteResource")
     *     ),
     * )
     */
    public function route(RouteFilterRequest $request): JsonResponse
    {
        $model = Route::query()
            ->filter($request->validated())
            ->first();

        if(!$model || empty($model->data)){
            // если передан девайс, то это значит что он либо не активен, либо удален и  возможно нет
            // назначенной техники на нем, отдаем только то роут если есть, не генерим новый
            if(isset($request['device_id']) && $request['device_id'] != null){
                logger_info("[worker] GOOGLE ROAD API NOT exec");
            } else {

                \Artisan::call('worker:google_road', [
                    '--date' => $request['date'],
                    '--truck' => $request['truck_id'] ?? null,
                    '--trailer' => $request['trailer_id'] ?? null,
                    '--device' => $request['device_id'] ?? null,
                ]);
                logger_info("[worker] GOOGLE ROAD API exec FOR REQUEST", [
                    'request' => $request->all()
                ]);
            }

            $model = Route::query()
                ->filter($request->validated())
                ->first();
        }

        return $this->makeResponse($model->data ?? []);
    }

    /**
     * @param HistoryFilterRequest $request
     * @return AnonymousResourceCollection
     * @throws AuthorizationException
     *
     * @OA\POST(path="/api/gps/history/route",
     *     tags={"Carrier GPS History"},
     *     summary="Set coord for route",
     *     operationId="SetHistoryRoute",
     *     deprecated=true,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Parameter(name="truck_id", in="query", required=true,
     *          @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(name="trailer_id", in="query", required=false,
     *          @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter (name="date", in="query", description="Date", required=true,
     *            @OA\Schema(type="string", example="10/25/2023", description="format - m/d/Y")
     *     ),
     *     @OA\Parameter (name="data", in="query", description="Data of location", required=true,
     *             @OA\Schema(type="array", @OA\Items(type="array", @OA\Items()))
     *      ),
     *
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/HistoryDeviceRouteResource")
     *     ),
     * )
     */
    public function setRoute(
        RouteCreateRequest $request,
        RouteService $service
    ): JsonResponse
    {
        $model = $service->create($request->validated());

        return $this->makeResponse($model->data);
    }

    /**
     * @param HistoryFilterRequest $request
     * @return AnonymousResourceCollection
     * @throws AuthorizationException
     *
     * @OA\Get(path="/api/gps/history/export",
     *     tags={"Carrier GPS History"},
     *     summary="Returns a link to download excel files",
     *     operationId="history export",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="truck_id", in="query", description="Truck ID", required=false,
     *          @OA\Schema(type="integer", example="5")
     *     ),
     *     @OA\Parameter(name="trailer_id", in="query", description="Trailer ID", required=false,
     *          @OA\Schema(type="integer", example="5")
     *     ),
     *     @OA\Parameter(name="device_id", in="query", description="Device ID", required=false,
     *          @OA\Schema(type="integer", example="5")
     *     ),
     *     @OA\Parameter (name="date_from", in="query", description="Date from", required=false,
     *          @OA\Schema (type="string", example="2023-08-22", description="format - Y-m-d")
     *      ),
     *      @OA\Parameter (name="date_to", in="query", description="Date to", required=false,
     *           @OA\Schema(type="string", example="2023-08-22", description="format - Y-m-d")
     *      ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     ),
     * )
     */
    public function export(HistoryExportFilterRequest $request): JsonResponse
    {
        try {
            $link = $this->service->export(
                $this->repo->getByCompanyCollection(
                    authUser()->getCompanyId(),
                    $request->all()
                ),
                authUser()->getCompany(),
                $request->all()
            );

            return $this->makeSuccessResponse($link, 200);
        } catch (\Exception $e) {
            return $this->makeErrorResponse($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @param HistoryFilterRequest $request
     * @return AnonymousResourceCollection
     * @throws AuthorizationException
     *
     * @OA\Get(path="/api/gps/history/additional",
     *     tags={"Carrier GPS History"},
     *     summary="Returns a additional by vehicle",
     *     operationId="historyTotalMileage",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="truck_id", in="query", description="Truck ID", required=false,
     *          @OA\Schema(type="integer", example="5")
     *     ),
     *     @OA\Parameter(name="trailer_id", in="query", description="Trailer ID", required=false,
     *          @OA\Schema(type="integer", example="5")
     *     ),
     *     @OA\Parameter(name="device_id", in="query", description="Device ID", required=false,
     *          @OA\Schema(type="integer", example="5")
     *     ),
     *     @OA\Parameter (name="date_from", in="query", description="Date from", required=false,
     *          @OA\Schema (type="string", example="2023-08-22", description="format - Y-m-d")
     *      ),
     *      @OA\Parameter (name="date_to", in="query", description="Date to", required=false,
     *           @OA\Schema(type="string", example="2023-08-22", description="format - Y-m-d")
     *      ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/HistoryAdditionalResource")
     *     ),
     * )
     */
    public function additional(HistoryFilterRequest $request): HistoryAdditionalResource
    {
        $additional = $this->service->getAdditional(
            $this->repo->getByCompanyCollection(
                authUser()->getCompanyId(),
                $request->all()
            )
        );

        return HistoryAdditionalResource::make($additional);
    }
}
