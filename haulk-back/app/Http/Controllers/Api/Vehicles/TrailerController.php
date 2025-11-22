<?php

namespace App\Http\Controllers\Api\Vehicles;

use App\Events\BS\Vehicles\DeleteVehicleEvent;
use App\Events\BS\Vehicles\SyncVehicleEvent;
use App\Exceptions\HasRelatedEntitiesException;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Vehicles\SameVinRequest;
use App\Http\Requests\Vehicles\Trailers\GPSDevicesListRequest;
use App\Http\Requests\Vehicles\Trailers\TrailerIndexRequest;
use App\Http\Requests\Vehicles\Trailers\TrailerRequest;
use App\Http\Requests\Vehicles\Trucks\TruckAddDriverHistoryRequest;
use App\Http\Requests\Vehicles\VehicleHistoryRequest;
use App\Http\Resources\History\HistoryListResource;
use App\Http\Resources\History\HistoryPaginatedResource;
use App\Http\Resources\Users\UserShortListResource;
use App\Http\Resources\Vehicles\GpsDeviceResource;
use App\Http\Resources\Vehicles\SameVinResource;
use App\Http\Resources\Vehicles\Trailers\TrailerPaginateResource;
use App\Http\Resources\Vehicles\Trailers\TrailerResource;
use App\Http\Resources\Vehicles\VehicleDriverHistoryResource;
use App\Http\Resources\Vehicles\VehicleOwnerHistoryResource;
use App\Models\Users\User;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Truck;
use App\Models\Vehicles\Vehicle;
use App\Scopes\CompanyScope;
use App\Services\Saas\GPS\Devices\DeviceService;
use App\Services\Vehicles\DriverHistoryService;
use App\Services\Vehicles\TrailerService;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class TrailerController extends ApiController
{
    protected TrailerService $service;

    protected DeviceService $deviceService;
    private DriverHistoryService $driverHistoryService;

    public function __construct(
        TrailerService $service,
        DeviceService $deviceService,
        DriverHistoryService $driverHistoryService
    )
    {
        parent::__construct();

        $this->service = $service;
        $this->deviceService = $deviceService;
        $this->service->setUser(authUser());
        $this->driverHistoryService = $driverHistoryService;
    }

    /**
     * @param TrailerIndexRequest $request
     * @return AnonymousResourceCollection
     *
     * @OA\Get(
     *     path="/api/trailers",
     *     tags={"Trailers"},
     *     summary="Get trailers paginated list",
     *     operationId="Get trailers data",
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
     *         @OA\JsonContent(ref="#/components/schemas/TrailerPaginate")
     *     ),
     * )
     */
    public function index(TrailerIndexRequest $request): AnonymousResourceCollection
    {
        $this->authorize('trailers');

        $owners = Trailer::query()
            ->withGlobalScope('company', new CompanyScope())
            ->filter($request->validated())
            ->orderBy($request->order_by ?? 'id', $request->order_type ?? 'desc')
            ->paginate($request->per_page);

        return TrailerPaginateResource::collection($owners);
    }

    /**
     * @param TrailerRequest $request
     * @return TrailerResource|JsonResponse
     * @throws AuthorizationException
     * @throws Throwable
     * @OA\Post(path="/api/trailers", tags={"Trailers"}, summary="Create Trailer", operationId="Create Trailer", deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="vin", in="query", description="Trailer vin number", required=true,
     *          @OA\Schema(type="string", default="DFDFD76SDD76",)
     *     ),
     *     @OA\Parameter(name="unit_number", in="query", description="Trailer Unit number", required=true,
     *          @OA\Schema(type="string", default="34FDK",)
     *     ),
     *     @OA\Parameter(name="make", in="query", description="Trailer make", required=true,
     *          @OA\Schema(type="string", default="Audi",)
     *     ),
     *     @OA\Parameter(name="model", in="query", description="Trailer model", required=true,
     *          @OA\Schema(type="string", default="A3",)
     *     ),
     *     @OA\Parameter(name="year", in="query", description="Trailer year", required=true,
     *          @OA\Schema(type="string", default="2022",)
     *     ),
     *     @OA\Parameter(name="license_plate", in="query", description="Trailer license plate", required=false,
     *          @OA\Schema(type="string", default="ER2342",)
     *     ),
     *     @OA\Parameter(name="temporary_plate", in="query", description="Trailer temporary plate", required=false,
     *          @OA\Schema(type="string", default="ER2342",)
     *     ),
     *     @OA\Parameter(name="notes", in="query", description="Trailer notes", required=false,
     *          @OA\Schema(type="string", default="text notes",)
     *     ),
     *     @OA\Parameter(name="owner_id", in="query", description="Trailer owner id", required=true,
     *          @OA\Schema(type="integer", default="1",)
     *     ),
     *     @OA\Parameter(name="driver_id", in="query", description="Trailer driver id", required=false,
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
     *         @OA\JsonContent(ref="#/components/schemas/Trailer")
     *     ),
     * )
     */
    public function store(TrailerRequest $request)
    {
        $this->authorize('trailers create');

        try {
            $model = $this->service->create($request->getDto());

            event(new SyncVehicleEvent($model));

            return TrailerResource::make($model);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->makeErrorResponse($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     *
     * @OA\Get(
     *     path="/api/trailers/{trailerId}",
     *     tags={"Trailers"},
     *     summary="Get Trailer record",
     *     operationId="Get Trailer record",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="id", in="path", description="Trailer id", required=true,
     *          @OA\Schema( type="integer",)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Trailer")
     *     ),
     * )
     * @param Trailer $trailer
     * @return TrailerResource|JsonResponse
     * @throws AuthorizationException
     */
    public function show(Trailer $trailer)
    {
        $this->authorize('trailers read');

        if ($trailer->getCompanyId() !== request()->user()->getCompanyId()) {
            return $this->makeErrorResponse('', Response::HTTP_FORBIDDEN);
        }

        return TrailerResource::make($trailer);
    }

    /**
     * Update the specified resource in storage.
     *
     * @OA\Post(
     *     path="/api/trailers/{trailerId}",
     *     tags={"Trailers"},
     *     summary="Update Trailer record",
     *     operationId="Update Trailer",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="id", in="path", description="Trailer id", required=true,
     *          @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(name="vin", in="query", description="Trailer vin number", required=true,
     *          @OA\Schema(type="string", default="DFDFD76SDD76",)
     *     ),
     *     @OA\Parameter(name="unit_number", in="query", description="Trailer Unit number", required=true,
     *          @OA\Schema(type="string", default="34FDK",)
     *     ),
     *     @OA\Parameter(name="make", in="query", description="Trailer make", required=true,
     *          @OA\Schema(type="string", default="Audi",)
     *     ),
     *     @OA\Parameter(name="model", in="query", description="Trailer model", required=true,
     *          @OA\Schema(type="string", default="A3",)
     *     ),
     *     @OA\Parameter(name="year", in="query", description="Trailer year", required=true,
     *          @OA\Schema(type="string", default="2022",)
     *     ),
     *     @OA\Parameter(name="license_plate", in="query", description="Trailer license plate", required=false,
     *          @OA\Schema(type="string", default="ER2342",)
     *     ),
     *     @OA\Parameter(name="temporary_plate", in="query", description="Trailer temporary plate", required=false,
     *          @OA\Schema(type="string", default="ER2342",)
     *     ),
     *     @OA\Parameter(name="notes", in="query", description="Trailer notes", required=false,
     *          @OA\Schema(type="string", default="text notes",)
     *     ),
     *     @OA\Parameter(name="owner_id", in="query", description="Trailer owner id", required=true,
     *          @OA\Schema(type="integer", default="1",)
     *     ),
     *     @OA\Parameter(name="driver_id", in="query", description="Trailer driver id", required=false,
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
     *           @OA\Schema(type="number", example="10",)
     *      ),
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
     *         @OA\JsonContent(ref="#/components/schemas/Trailer")
     *     ),
     * )
     * @param TrailerRequest $request
     * @param Trailer $trailer
     * @return TrailerResource|JsonResponse
     * @throws AuthorizationException
     * @throws Throwable
     */
    public function update(TrailerRequest $request, Trailer $trailer)
    {
        $this->authorize('trailers update');

        if ($trailer->getCompanyId() !== $request->user()->getCompanyId()) {
            return $this->makeErrorResponse('', Response::HTTP_FORBIDDEN);
        }

        try {
            $trailer = $this->service->update($trailer, $request->getDto());

            event(new SyncVehicleEvent($trailer));

            return TrailerResource::make($trailer->refresh());
        } catch (Exception $e) {
            Log::error($e);

            return $this->makeErrorResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @OA\Delete(
     *     path="/api/trailers/{trailerId}",
     *     tags={"Trailers"},
     *     summary="Delete Trailer",
     *     operationId="Delete Trailer",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="id", in="path", description="Trailer id", required=true,
     *          @OA\Schema(type="integer", default="1",)
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Successful operation",
     *     ),
     * )
     * @param Trailer $truck
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function destroy(Trailer $trailer)
    {
        $this->authorize('trailers delete');

        if ($trailer->getCompanyId() !== request()->user()->getCompanyId()) {
            return $this->makeErrorResponse('', Response::HTTP_FORBIDDEN);
        }

        try {
            $this->service->destroy($trailer);

            event(new DeleteVehicleEvent($trailer));

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
     *     path="/api/trailers/{trailerId}/attachments/{attachmentId}",
     *     tags={"Trailers"},
     *     summary="Delete attachment from trailer",
     *     operationId="Delete attachment",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(response=204, description="Successful operation"),
     * )
     */
    public function deleteAttachment(Trailer $trailer, int $id)
    {
        $this->authorize('trailers update');

        try {
            $this->service->deleteAttachment($trailer, $id);

            return $this->makeSuccessResponse(null, Response::HTTP_NO_CONTENT);
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get trailer history
     *
     * @param Trailer $trailer
     * @return AnonymousResourceCollection|JsonResponse
     *
     * @throws AuthorizationException
     * @OA\Get(
     *     path="/api/trailers/{trailerId}/history",
     *     tags={"Trailers"},
     *     summary="Get trailer history",
     *     operationId="Get trailer history",
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
    public function history(Trailer $trailer)
    {
        $this->authorize('trailers read');

        try {
            return HistoryListResource::collection(
                $this->service->getHistoryShort($trailer)
            );
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get trailer history detailed paginate
     *
     * @param Trailer $trailer
     * @param VehicleHistoryRequest $request
     * @return AnonymousResourceCollection|JsonResponse
     *
     * @throws AuthorizationException
     * @OA\Get(
     *     path="/api/trailers/{trailerId}/history-detailed",
     *     tags={"Trailers"},
     *     summary="Get trailer history detailed",
     *     operationId="Get trailer history detailed",
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
     *         @OA\JsonContent(ref="#/components/schemas/HistoryPaginatedResourceBS")
     *     ),
     * )
     */
    public function historyDetailed(Trailer $trailer, VehicleHistoryRequest $request)
    {
        $this->authorize('trailers read');

        try {
            return HistoryPaginatedResource::collection($this->service->getHistoryDetailed($trailer, $request));
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get trailer history users
     *
     * @param Trailer $trailer
     * @return AnonymousResourceCollection
     *
     * @throws AuthorizationException
     * @OA\Get(
     *     path="/api/trailers/{trailerId}/history-users-list",
     *     tags={"Trailers"},
     *     summary="Get list users changes trailer",
     *     operationId="Get list users changes trailer",
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
    public function historyUsers(Trailer $trailer): AnonymousResourceCollection
    {
        $this->authorize('trailers read');

        return UserShortListResource::collection($this->service->getHistoryUsers($trailer));
    }

    /**
     * @OA\Delete(
     *     path="/api/trailers/{trailerId}/delete-registration-document",
     *     tags={"Trailers"},
     *     summary="Delete registration document from trailer",
     *     operationId="Delete registration document",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(response=204, description="Successful operation"),
     * )
     */
    public function deleteRegistrationDocument(Trailer $trailer): JsonResponse
    {
        $this->authorize('trailers update');

        try {
            $this->service->deleteDocument($trailer, Vehicle::REGISTRATION_DOCUMENT_NAME);

            return $this->makeSuccessResponse(null, Response::HTTP_NO_CONTENT);
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/trailers/{trailerId}/delete-inspction-document",
     *     tags={"Trailers"},
     *     summary="Delete inspection document from trailer",
     *     operationId="Delete inspection document",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(response=204, description="Successful operation"),
     * )
     */
    public function deleteInspectionDocument(Trailer $trailer): JsonResponse
    {
        $this->authorize('trailers update');

        try {
            $this->service->deleteDocument($trailer, Vehicle::INSPECTION_DOCUMENT_NAME);

            return $this->makeSuccessResponse(null, Response::HTTP_NO_CONTENT);
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get drivers history
     *
     * @param Trailer $trailer
     * @return AnonymousResourceCollection
     *
     * @throws AuthorizationException
     * @OA\Get(
     *     path="/api/trailers/{trailerId}/drivers-activity",
     *     tags={"Trailers"},
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
    public function driversHistory(Trailer $trailer): AnonymousResourceCollection
    {
        $this->authorize('trailers read');

        return VehicleDriverHistoryResource::collection(
            $trailer->driversHistory()
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
     *     path="/api/trailers/{trailerId}/drivers-activity",
     *     tags={"Trailers"},
     *     summary="Add drivers activity (history)",
     *     operationId="Add drivers activity (history)",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="id", in="path", description="Trailer id", required=true,
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
        Trailer $trailer
    ): AnonymousResourceCollection
    {
        $this->authorize('trailers update');

        $driver = User::query()->where('id', $request['driver_id'])->first();
        if($driver && !$driver->isDriver()){
            throw ValidationException::withMessages([
                'driver_id' => __("exceptions.user.driver.not_driver")
            ]);
        }

        $trailer = $this->driverHistoryService->add($trailer, $driver, $request->all());

        return VehicleDriverHistoryResource::collection(
            $trailer->driversHistory()
                ->with('driver')
                ->orderBy('assigned_at', 'desc')
                ->paginate(5)
        );
    }

    /**
     * Get owners history
     *
     * @param Trailer $trailer
     * @return AnonymousResourceCollection
     *
     * @throws AuthorizationException
     * @OA\Get(
     *     path="/api/trailers/{trailerId}/owners-activity",
     *     tags={"Trailers"},
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
    public function ownersHistory(Trailer $trailer): AnonymousResourceCollection
    {
        $this->authorize('trailers read');

        return VehicleOwnerHistoryResource::collection(
            $trailer->ownersHistory()
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
     *     path="/api/trailers/same-vin",
     *     tags={"Trailers"},
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
            $this->service->getTrailersWithVin($request->vin, $request->id ?? null)
        );
    }

    /**
     * @param GPSDevicesListRequest $request
     * @return AnonymousResourceCollection|JsonResponse
     *
     * @OA\Get(
     *     path="/api/trailers/available-gps-devices",
     *     tags={"Trailers"},
     *     summary="Get available GPS devices",
     *     operationId="Get gps devices data",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="trailer_id", in="query", description="Current trailer Id", required=false,
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

        $devices = $this->deviceService->getAvailableDevices(authUser()->getCompanyId(), null, $request->trailer_id ?? null);

        return GpsDeviceResource::collection($devices);
    }
}
