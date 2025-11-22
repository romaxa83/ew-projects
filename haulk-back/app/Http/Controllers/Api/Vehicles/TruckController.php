<?php

namespace App\Http\Controllers\Api\Vehicles;

use App\Events\BS\Vehicles\DeleteVehicleEvent;
use App\Events\BS\Vehicles\SyncVehicleEvent;
use App\Exceptions\HasRelatedEntitiesException;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Vehicles\SameVinRequest;
use App\Http\Requests\Vehicles\Trucks\GPSDevicesListRequest;
use App\Http\Requests\Vehicles\Trucks\TruckAddDriverHistoryRequest;
use App\Http\Requests\Vehicles\Trucks\TruckIndexRequest;
use App\Http\Requests\Vehicles\Trucks\TruckRequest;
use App\Http\Requests\Vehicles\VehicleHistoryRequest;
use App\Http\Resources\History\HistoryListResource;
use App\Http\Resources\History\HistoryPaginatedResource;
use App\Http\Resources\Users\UserShortListResource;
use App\Http\Resources\Vehicles\GpsDeviceResource;
use App\Http\Resources\Vehicles\SameVinResource;
use App\Http\Resources\Vehicles\Trucks\TruckPaginateResource;
use App\Http\Resources\Vehicles\Trucks\TruckResource;
use App\Http\Resources\Vehicles\VehicleDriverHistoryResource;
use App\Http\Resources\Vehicles\VehicleOwnerHistoryResource;
use App\Models\Saas\GPS\Device;
use App\Models\Users\User;
use App\Models\Vehicles\Truck;
use App\Models\Vehicles\TruckDriverHistory;
use App\Models\Vehicles\Vehicle;
use App\Scopes\CompanyScope;
use App\Services\Events\EventService;
use App\Services\Saas\GPS\Devices\DeviceService;
use App\Services\Vehicles\DriverHistoryService;
use App\Services\Vehicles\TruckService;
use Carbon\CarbonImmutable;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class TruckController extends ApiController
{
    protected TruckService $service;
    protected DeviceService $deviceService;
    private DriverHistoryService $driverHistoryService;

    public function __construct(
        TruckService $truckService,
        DeviceService $deviceService,
        DriverHistoryService $driverHistoryService
    )
    {
        parent::__construct();

        $this->service = $truckService;
        $this->deviceService = $deviceService;
        $this->service->setUser(authUser());
        $this->driverHistoryService = $driverHistoryService;
    }

    /**
     * @param TruckIndexRequest $request
     * @return AnonymousResourceCollection
     *
     * @OA\Get(
     *     path="/api/trucks",
     *     tags={"Trucks"},
     *     summary="Get trucks paginated list",
     *     operationId="Get trucks data",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="page", in="query", description="Page number", required=false,
     *          @OA\Schema( type="integer", default="5")
     *     ),
     *     @OA\Parameter(name="per_page", in="query", description="States per page", required=false,
     *          @OA\Schema( type="integer", default="10")
     *     ),
     *     @OA\Parameter(  name="q", in="query", description="Scope for search by vin, unit number, licance plate, temporary plate", required=false,
     *          @OA\Schema( type="string", default="name",)
     *     ),
     *     @OA\Parameter(name="owner_id", in="query", description="Vehicle owner id", required=false,
     *          @OA\Schema(type="integer", default="1",)
     *     ),
     *     @OA\Parameter(name="driver_id", in="query", description="Vehicle driver id", required=false,
     *          @OA\Schema( type="integer", default="1", )
     *     ),
     *     @OA\Parameter(name="tag_id", in="query", description="Tag id", required=false,
     *          @OA\Schema( type="integer", default="1",)
     *     ),
     *     @OA\Parameter(name="order_by", in="query", description="Field for sort", required=false,
     *          @OA\Schema(type="string", default="status", enum ={"registration_expiration_date", "inspection_expiration_date"})
     *     ),
     *     @OA\Parameter(name="order_type", in="query", description="Type for sort", required=false,
     *          @OA\Schema(type="string", default="desc", enum ={"asc","desc"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/TruckPaginate")
     *     ),
     * )
     */
    public function index(TruckIndexRequest $request): AnonymousResourceCollection
    {
        $this->authorize('trucks');

        $owners = Truck::query()
            ->withGlobalScope('company', new CompanyScope())
            ->filter($request->validated())
            ->orderBy($request->order_by ?? 'id', $request->order_type ?? 'desc')
            ->paginate($request->per_page);

        return TruckPaginateResource::collection($owners);
    }

    /**
     * @param TruckRequest $request
     * @return TruckResource|JsonResponse
     * @throws AuthorizationException
     * @throws Throwable
     * @OA\Post(path="/api/trucks", tags={"Trucks"}, summary="Create Truck", operationId="Create Truck", deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="vin", in="query", description="Truck vin number", required=true,
     *          @OA\Schema(type="string", default="DFDFD76SDD76",)
     *     ),
     *     @OA\Parameter(name="unit_number", in="query", description="Truck Unit number", required=true,
     *          @OA\Schema(type="string", default="34FDK",)
     *     ),
     *     @OA\Parameter(name="make", in="query", description="Truck make", required=true,
     *          @OA\Schema(type="string", default="Audi",)
     *     ),
     *     @OA\Parameter(name="model", in="query", description="Truck model", required=true,
     *          @OA\Schema(type="string", default="A3",)
     *     ),
     *     @OA\Parameter(name="year", in="query", description="Truck year", required=true,
     *          @OA\Schema(type="string", default="2022",)
     *     ),
     *     @OA\Parameter(name="type", in="query", description="Truck type", required=true,
     *          @OA\Schema(type="integer", default="1",)
     *     ),
     *     @OA\Parameter(name="license_plate", in="query", description="Truck license plate", required=false,
     *          @OA\Schema(type="string", default="ER2342",)
     *     ),
     *     @OA\Parameter(name="temporary_plate", in="query", description="Truck temporary plate", required=false,
     *          @OA\Schema(type="string", default="ER2342",)
     *     ),
     *     @OA\Parameter(name="notes", in="query", description="Truck notes", required=false,
     *          @OA\Schema(type="string", default="text notes",)
     *     ),
     *     @OA\Parameter(name="owner_id", in="query", description="Truck owner id", required=true,
     *          @OA\Schema(type="integer", default="1",)
     *     ),
     *     @OA\Parameter(name="driver_id", in="query", description="Truck driver id", required=false,
     *          @OA\Schema(type="integer", default="1",)
     *     ),
     *     @OA\Parameter(name="tags", in="query", description="Tags list", required=false,
     *          @OA\Schema(type="array",
     *              @OA\Items(anyOf={@OA\Schema(type="integer")})
     *          )
     *     ),
     *     @OA\Parameter(name="attachment_files", in="query", description="Attachments list", required=false),
     *     @OA\Parameter(name="color", in="query", description="Color", required=false,
     *          @OA\Schema(type="string", default="red",)
     *     ),
     *     @OA\Parameter(name="gvwr", in="query", description="GVWR", required=false,
     *          @OA\Schema(type="number", example="10",)
     *     ),
     *     @OA\Parameter(name="registration_number", in="query", description="Registration number", required=false,
     *          @OA\Schema(type="string", default="123456",)
     *     ),
     *     @OA\Parameter(name="registration_date", in="query", description="Registration date, format m/d/Y", required=false,
     *          @OA\Schema(type="string", default="01/20/2023",)
     *     ),
     *     @OA\Parameter(name="registration_expiration_date", in="query", description="Registration expiration date, format m/d/Y", required=false,
     *          @OA\Schema(type="string", default="01/20/2023",)
     *     ),
     *     @OA\Parameter(name="registration_file", in="query", description="Registration file", required=false,
     *          @OA\Schema(type="file",)
     *     ),
     *     @OA\Parameter(name="inspection_date", in="query", description="Inspection date, format m/d/Y", required=false,
     *          @OA\Schema(type="string", default="01/20/2023",)
     *     ),
     *     @OA\Parameter(name="inspection_expiration_date", in="query", description="Inspection expiration date, format m/d/Y", required=false,
     *          @OA\Schema(type="string", default="01/20/2023",)
     *     ),
     *     @OA\Parameter(name="inspection_file", in="query", description="Inspection file", required=false,
     *          @OA\Schema(type="file",)
     *     ),
     *     @OA\Parameter(name="gps_device_id", in="query", description="GPS Device id (if GPS enabled)", required=false,
     *          @OA\Schema(type="integer", default="1",)
     *     ),
     *     @OA\Response(response=201, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Truck")
     *     ),
     * )
     */
    public function store(TruckRequest $request)
    {
        $this->authorize('trucks create');

        logger_info('STORE TR');

        try {
            $model = $this->service->create($request->getDto());

            event(new SyncVehicleEvent($model));

            return TruckResource::make($model);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->makeErrorResponse($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     *
     * @OA\Get(
     *     path="/api/trucks/{truckid}",
     *     tags={"Trucks"},
     *     summary="Get truck record",
     *     operationId="Get truck record",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="id", in="path", description="Truck id", required=true,
     *          @OA\Schema( type="integer",)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Truck")
     *     ),
     * )
     * @param Truck $truck
     * @return TruckResource|JsonResponse
     * @throws AuthorizationException
     */
    public function show(Truck $truck)
    {
        $this->authorize('trucks read');

        if ($truck->getCompanyId() !== request()->user()->getCompanyId()) {
            return $this->makeErrorResponse('', Response::HTTP_FORBIDDEN);
        }

        return TruckResource::make($truck);
    }

    /**
     * Update the specified resource in storage.
     *
     * @OA\Post(
     *     path="/api/trucks/{truckId}",
     *     tags={"Trucks"},
     *     summary="Update truck record",
     *     operationId="Update truck",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="id", in="path", description="Truck id", required=true,
     *          @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(name="vin", in="query", description="Truck vin number", required=true,
     *          @OA\Schema(type="string", default="DFDFD76SDD76",)
     *     ),
     *     @OA\Parameter(name="unit_number", in="query", description="Truck Unit number", required=true,
     *          @OA\Schema(type="string", default="34FDK",)
     *     ),
     *     @OA\Parameter(name="make", in="query", description="Truck make", required=true,
     *          @OA\Schema(type="string", default="Audi",)
     *     ),
     *     @OA\Parameter(name="model", in="query", description="Truck model", required=true,
     *          @OA\Schema(type="string", default="A3",)
     *     ),
     *     @OA\Parameter(name="year", in="query", description="Truck year", required=true,
     *          @OA\Schema(type="string", default="2022",)
     *     ),
     *     @OA\Parameter(name="type", in="query", description="Truck type", required=true,
     *          @OA\Schema(type="integer", default="1",)
     *     ),
     *     @OA\Parameter(name="license_plate", in="query", description="Truck license plate", required=false,
     *          @OA\Schema(type="string", default="ER2342",)
     *     ),
     *     @OA\Parameter(name="temporary_plate", in="query", description="Truck temporary plate", required=false,
     *          @OA\Schema(type="string", default="ER2342",)
     *     ),
     *     @OA\Parameter(name="notes", in="query", description="Truck notes", required=false,
     *          @OA\Schema(type="string", default="text notes",)
     *     ),
     *     @OA\Parameter(name="owner_id", in="query", description="Truck owner id", required=true,
     *          @OA\Schema(type="integer", default="1",)
     *     ),
     *     @OA\Parameter(name="driver_id", in="query", description="Truck driver id", required=false,
     *          @OA\Schema(type="integer", default="1",)
     *     ),
     *     @OA\Parameter(name="tags", in="query", description="Tags list", required=false,
     *          @OA\Schema(type="array",
     *              @OA\Items(anyOf={@OA\Schema(type="integer")})
     *          )
     *     ),
     *     @OA\Parameter(name="attachment_files", in="query", description="Attachments list", required=false),
     *     @OA\Parameter(name="color", in="query", description="Color", required=false,
     *          @OA\Schema(type="string", default="red",)
     *     ),
     *     @OA\Parameter(name="gvwr", in="query", description="GVWR", required=false,
     *          @OA\Schema(type="number", example="10",)
     *     ),
     *     @OA\Parameter(name="registration_number", in="query", description="Registration number", required=false,
     *          @OA\Schema(type="string", default="123456",)
     *     ),
     *     @OA\Parameter(name="registration_date", in="query", description="Registration date, format m/d/Y", required=false,
     *          @OA\Schema(type="string", default="01/20/2023",)
     *     ),
     *     @OA\Parameter(name="registration_expiration_date", in="query", description="Registration expiration date, format m/d/Y", required=false,
     *          @OA\Schema(type="string", default="01/20/2023",)
     *     ),
     *     @OA\Parameter(name="registration_file", in="query", description="Registration file", required=false,
     *          @OA\Schema(type="file",)
     *     ),
     *     @OA\Parameter(name="inspection_date", in="query", description="Inspection date, format m/d/Y", required=false,
     *          @OA\Schema(type="string", default="01/20/2023",)
     *     ),
     *     @OA\Parameter(name="inspection_expiration_date", in="query", description="Inspection expiration date, format m/d/Y", required=false,
     *          @OA\Schema(type="string", default="01/20/2023",)
     *     ),
     *     @OA\Parameter(name="inspection_file", in="query", description="Inspection file", required=false,
     *          @OA\Schema(type="file",)
     *     ),
     *     @OA\Parameter(name="gps_device_id", in="query", description="GPS Device id (if GPS enabled)", required=false,
     *          @OA\Schema(type="integer", default="1",)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Truck")
     *     ),
     * )
     * @param TruckRequest $request
     * @param Truck $truck
     * @return TruckResource|JsonResponse
     * @throws AuthorizationException
     * @throws Throwable
     */
    public function update(TruckRequest $request, Truck $truck)
    {
        $this->authorize('trucks update');

        if ($truck->getCompanyId() !== $request->user()->getCompanyId()) {
            return $this->makeErrorResponse('', Response::HTTP_FORBIDDEN);
        }

        try {
            $truck = $this->service->update($truck, $request->getDto());

            event(new SyncVehicleEvent($truck));

            return TruckResource::make($truck->refresh());
        } catch (Exception $e) {
            Log::error($e);
            logger_info($e->getMessage());
            return $this->makeErrorResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @OA\Delete(
     *     path="/api/trucks/{truckId}",
     *     tags={"Trucks"},
     *     summary="Delete truck",
     *     operationId="Delete truck",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="id", in="path", description="Truck id", required=true,
     *          @OA\Schema(type="integer", default="1",)
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Successful operation",
     *     ),
     * )
     * @param Truck $truck
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function destroy(Truck $truck)
    {
        $this->authorize('trucks delete');

        if ($truck->getCompanyId() !== request()->user()->getCompanyId()) {
            return $this->makeErrorResponse('', Response::HTTP_FORBIDDEN);
        }

        try {
            $this->service->destroy($truck);

            event(new DeleteVehicleEvent($truck));

            return $this->makeSuccessResponse(null, Response::HTTP_NO_CONTENT);
        } catch (HasRelatedEntitiesException $e) {
            return $this->makeErrorResponse(
                trans('This vehicle is used in Body Shop orders.'),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        } catch (Exception $e) {
            Log::error($e);

            return $this->makeErrorResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     *
     * @OA\Delete(
     *     path="/api/trucks/{truckId}/attachments/{attachmentId}",
     *     tags={"Trucks"},
     *     summary="Delete attachment from truck",
     *     operationId="Delete attachment",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(response=204, description="Successful operation"),
     * )
     */
    public function deleteAttachment(Truck $truck, int $id)
    {
        $this->authorize('trucks update');

        try {
            $this->service->deleteAttachment($truck, $id);

            return $this->makeSuccessResponse(null, Response::HTTP_NO_CONTENT);
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get truck history
     *
     * @param Truck $truck
     * @return AnonymousResourceCollection|JsonResponse
     *
     * @throws AuthorizationException
     * @OA\Get(
     *     path="/api/trucks/{truckId}/history",
     *     tags={"Trucks"},
     *     summary="Get truck history",
     *     operationId="Get truck history",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/HistoryListResourceBS")
     *     ),
     * )
     */
    public function history(Truck $truck)
    {
        $this->authorize('trucks read');

        try {
            return HistoryListResource::collection(
                $this->service->getHistoryShort($truck)
            );
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get truck history detailed paginate
     *
     * @param Truck $truck
     * @param VehicleHistoryRequest $request
     * @return AnonymousResourceCollection|JsonResponse
     *
     * @throws AuthorizationException
     * @OA\Get(
     *     path="/api/trucks/{truckId}/history-detailed",
     *     tags={"Trucks"},
     *     summary="Get truck history detailed",
     *     operationId="Get truck history detailed",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="dates_range", in="query", description="06/06/2021 - 06/14/2021", required=false,
     *          @OA\Schema(type="string",)
     *     ),
     *     @OA\Parameter(name="user_id", in="query", description="user_id", required=false,
     *          @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter( name="page", in="query", description="page", required=false,
     *          @OA\Schema(type="integer", default="1")
     *     ),
     *     @OA\Parameter( name="per_page", in="query", description="per page", required=false,
     *          @OA\Schema(type="integer", default="10")
     *     ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/HistoryPaginatedResource")
     *     ),
     * )
     */
    public function historyDetailed(Truck $truck, VehicleHistoryRequest $request)
    {
        $this->authorize('trucks read');

        try {
            return HistoryPaginatedResource::collection($this->service->getHistoryDetailed($truck, $request));
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get truck history users
     *
     * @param Truck $truck
     * @return AnonymousResourceCollection
     *
     * @throws AuthorizationException
     * @OA\Get(
     *     path="/api/trucks/{truckId}/history-users-list",
     *     tags={"Trucks"},
     *     summary="Get list users changes truck",
     *     operationId="Get list users changes truck",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/UserShortList")
     *     ),
     * )
     */
    public function historyUsers(Truck $truck): AnonymousResourceCollection
    {
        $this->authorize('trucks read');

        return UserShortListResource::collection($this->service->getHistoryUsers($truck));
    }

    /**
     * @OA\Delete(
     *     path="/api/trucks/{truckId}/delete-registration-document",
     *     tags={"Trucks"},
     *     summary="Delete registration document from truck",
     *     operationId="Delete registration document",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(response=204, description="Successful operation"),
     * )
     */
    public function deleteRegistrationDocument(Truck $truck): JsonResponse
    {
        $this->authorize('trucks update');

        try {
            $this->service->deleteDocument($truck, Vehicle::REGISTRATION_DOCUMENT_NAME);

            return $this->makeSuccessResponse(null, Response::HTTP_NO_CONTENT);
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/trucks/{truckId}/delete-inspction-document",
     *     tags={"Trucks"},
     *     summary="Delete inspection document from truck",
     *     operationId="Delete inspection document",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(response=204, description="Successful operation"),
     * )
     */
    public function deleteInspectionDocument(Truck $truck): JsonResponse
    {
        $this->authorize('trucks update');

        try {
            $this->service->deleteDocument($truck, Vehicle::INSPECTION_DOCUMENT_NAME);

            return $this->makeSuccessResponse(null, Response::HTTP_NO_CONTENT);
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get drivers history
     *
     * @param Truck $truck
     * @return AnonymousResourceCollection
     *
     * @throws AuthorizationException
     * @OA\Get(
     *     path="/api/trucks/{truckId}/drivers-activity",
     *     tags={"Trucks"},
     *     summary="Get drivers activity (history)",
     *     operationId="Get drivers activity (history)",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="page", in="query", description="Page number", required=false,
     *          @OA\Schema( type="integer", default="5")
     *     ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/VehicleDriverHistoryResource")
     *     ),
     * )
     */
    public function driversHistory(Truck $truck): AnonymousResourceCollection
    {
        $this->authorize('trucks read');

        return VehicleDriverHistoryResource::collection(
            $truck->driversHistory()
                ->with('driver')
                ->orderBy('assigned_at', 'desc')
                ->paginate(5)
        );
    }

    /**
     * Add drivers history
     *
     * @param Truck $truck
     * @return AnonymousResourceCollection
     *
     * @throws AuthorizationException
     * @OA\Post(
     *     path="/api/trucks/{truckId}/drivers-activity",
     *     tags={"Trucks"},
     *     summary="Add drivers activity (history)",
     *     operationId="Add drivers activity (history)",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="id", in="path", description="Truck id", required=true,
     *           @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(name="start_at", in="query", description="assigned_at", required=true,
     *           @OA\Schema(type="string", default="09/12/2023 12:13:40",)
     *     ),
     *     @OA\Parameter(name="end_at", in="query", description="unassigned_at", required=true,
     *           @OA\Schema(type="string", default="09/12/2023 16:13:40",)
     *     ),
     *     @OA\Parameter(name="driver_id", in="query", description="Driver id", required=true,
     *            @OA\Schema(type="integer", default="1",)
     *      ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/VehicleDriverHistoryResource")
     *     ),
     * )
     */
    public function addDriversHistory(
        TruckAddDriverHistoryRequest $request,
        Truck $truck
    ): AnonymousResourceCollection
    {
        $this->authorize('trucks update');

        $driver = User::query()->where('id', $request['driver_id'])->first();
        if($driver && !$driver->isDriver()){
            throw ValidationException::withMessages([
                'driver_id' => __("exceptions.user.driver.not_driver")
            ]);
        }

        $truck = $this->driverHistoryService->add($truck, $driver, $request->all());

        return VehicleDriverHistoryResource::collection(
            $truck->driversHistory()
                ->with('driver')
                ->orderBy('assigned_at', 'desc')
                ->paginate(5)
        );
    }

    /**
     * Get owners history
     *
     * @param Truck $truck
     * @return AnonymousResourceCollection
     *
     * @throws AuthorizationException
     * @OA\Get(
     *     path="/api/trucks/{truckId}/owners-activity",
     *     tags={"Trucks"},
     *     summary="Get owners activity (history)",
     *     operationId="Get owners activity (history)",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="page", in="query", description="Page number", required=false,
     *          @OA\Schema( type="integer", default="5")
     *     ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/VehicleOwnerHistoryResource")
     *     ),
     * )
     */
    public function ownersHistory(Truck $truck): AnonymousResourceCollection
    {
        $this->authorize('trucks read');

        return VehicleOwnerHistoryResource::collection(
            $truck->ownersHistory()
                ->with('owner')
                ->orderBy('id', 'desc')
                ->paginate(5)
        );
    }

    /**
     * @param SameVinRequest $request
     * @return AnonymousResourceCollection
     *
     * @OA\Get(
     *     path="/api/trucks/same-vin",
     *     tags={"Trucks"},
     *     summary="Get vehicles with the same vehicle vin",
     *     operationId="Get vehicles with the same vehicle vin",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(
     *          name="id",
     *          in="query",
     *          description="Current Vehicle ID",
     *          required=false,
     *          @OA\Schema(
     *              type="integer",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="vin",
     *          in="query",
     *          description="",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *     ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/SameVinResource")
     *     ),
     * )
     */
    public function sameVin(SameVinRequest $request)
    {
        return SameVinResource::collection(
            $this->service->getTrucksWithVin($request->vin, $request->id ?? null)
        );
    }

    /**
     * @param GPSDevicesListRequest $request
     * @return AnonymousResourceCollection|JsonResponse
     *
     * @OA\Get(
     *     path="/api/trucks/available-gps-devices",
     *     tags={"Trucks"},
     *     summary="Get available gps devices list",
     *     operationId="Get trucks data",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="truck_id", in="query", description="Current truck Id", required=false,
     *          @OA\Schema(type="integer", default="1",)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/GPSDeviceCRMListResource")
     *     ),
     * )
     */
    public function availableGPSDevices(GPSDevicesListRequest $request)
    {
        $this->authorize('trucks');

        if (!authUser()->getCompany()->isGPSEnabled()) {
            return $this->makeErrorResponse('', Response::HTTP_FORBIDDEN);
        }

        $devices = $this->deviceService
            ->getAvailableDevices(authUser()->getCompanyId(), $request->truck_id ?? null);

        return GpsDeviceResource::collection($devices);
    }
}
