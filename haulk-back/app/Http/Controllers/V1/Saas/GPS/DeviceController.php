<?php

namespace App\Http\Controllers\V1\Saas\GPS;

use App\Http\Controllers\ApiController;
use App\Http\Requests\Saas\GPS\Device\DeviceFilterRequest;
use App\Http\Requests\Saas\GPS\Device\DeviceRequest;
use App\Http\Requests\Saas\GPS\Device\DeviceUpdateRequest;
use App\Http\Resources\Saas\GPS\Device\DeviceFlespiResource;
use App\Http\Resources\Saas\GPS\Device\DeviceResource;
use App\Models\Saas\GPS\Device;
use App\Permissions\Saas\GPS\Devices\DeviceCreate;
use App\Permissions\Saas\GPS\Devices\DeviceList;
use App\Permissions\Saas\GPS\Devices\DeviceUpdate;
use App\Repositories\Saas\GPS\DeviceRepository;
use App\Services\Saas\GPS\Devices\DeviceService;
use App\Services\Saas\GPS\Flespi\Collections\DeviceEntityCollection;
use App\Services\Saas\GPS\Flespi\Commands\Devices\DeviceGetAllCommand;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Request;

class DeviceController extends ApiController
{
    protected DeviceService $service;
    protected DeviceRepository $repo;

    public function __construct(
        DeviceService $service,
        DeviceRepository $repo
    )
    {
        parent::__construct();

        $this->service = $service;
        $this->repo = $repo;
    }

    /**
     * @param DeviceFilterRequest $request
     * @return AnonymousResourceCollection
     * @throws AuthorizationException
     *
     * @OA\Get(path="/v1/saas/gps-devices", tags={"GPS Device"},
     *     summary="Returns deviced list",
     *     operationId="devices list",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="page", in="query", description="Page number", required=false,
     *          @OA\Schema( type="integer", default="5")
     *     ),
     *     @OA\Parameter(name="per_page", in="query", description="Per page", required=false,
     *          @OA\Schema( type="integer", default="10")
     *     ),
     *     @OA\Parameter(name="id", in="query", required=false,
     *          @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(name="query", in="query", required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(name="company_id", in="query", required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(name="status", in="query", required=false,
     *          @OA\Schema(type="string", example="active", enum={"active", "inactive", "deleted"})
     *      ),
     *     @OA\Parameter(name="status_request", in="query", required=false,
     *           @OA\Schema(type="string", example="pending", enum={"pending", "closed", "cancel_subscription"})
     *       ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/DevicePaginatedResource")
     *     ),
     * )
     */
    public function index(DeviceFilterRequest $request): AnonymousResourceCollection
    {
        $this->authorize(DeviceList::KEY);

        $model = Device::query()
            ->withTrashed()
            ->filter($request->validated())
            ->with(['company' => function ($q){
                $q->orderBy('name');
            }])
            ->paginate($request->getPerPage(), ['*'], 'page', $request->getPage());

        return DeviceResource::collection($model);
    }

    /**
     * @param DeviceRequest $request
     * @return DeviceResource|JsonResponse
     * @throws AuthorizationException
     *
     * @OA\POST(
     *     path="/v1/saas/gps-devices",
     *     tags={"GPS Device"},
     *     summary="Create device",
     *     operationId="Create device",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="name", in="query", description="Device name", required=true,
     *          @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(name="imei", in="query", description="IMEI", required=true,
     *          @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(name="device_id", in="query", description="IMEI ID", required=true,
     *           @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(name="company_id", in="query", description="Company id", required=false,
     *          @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(name="phone", in="query", description="phone", required=false,
     *            @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/DeviceResource")
     *     ),
     * )
     */
    public function store(DeviceRequest $request)
    {
        $this->authorize(DeviceCreate::KEY);

        return DeviceResource::make($this->service->create($request->validated()));
    }

    /**
     * @param DeviceRequest $request
     * @return DeviceResource|JsonResponse
     * @throws AuthorizationException
     *
     * @OA\PUT(
     *     path="/v1/saas/gps-devices/{id}",
     *     tags={"GPS Device"},
     *     summary="Update device",
     *     operationId="Update device",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="{id}", in="path", required=true,
     *           description="ID device",
     *           @OA\Schema(type="integer", example="5")
     *     ),
     *     @OA\Parameter(name="imei", in="query", description="IMEI", required=true,
     *           @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(name="device_id", in="query", description="IMEI ID", required=true,
     *            @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(name="phone", in="query", description="phone", required=true,
     *             @OA\Schema(type="string")
     *      ),
     *     @OA\Parameter(name="name", in="query", description="Device name", required=true,
     *          @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(name="company_id", in="query", description="Company id", required=false,
     *          @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/DeviceResource")
     *     ),
     * )
     */
    public function update(DeviceUpdateRequest $request, $id)
    {
        $this->authorize(DeviceUpdate::KEY);

        /** @var $model Device */
        $model = $this->repo->getBy('id', $id);

        return DeviceResource::make($this->service->update($model, $request->validated()));
    }

    /**
     * @param DeviceRequest $request
     * @return DeviceResource|JsonResponse
     * @throws AuthorizationException
     *
     * @OA\PUT(
     *     path="/v1/saas/gps-devices/{id}/approve-request",
     *     tags={"GPS Device"},
     *     summary="Approve activate/deactivate request for device",
     *     operationId="ApproveDeviceRequest",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="{id}", in="path", required=true,
     *           description="ID device",
     *           @OA\Schema(type="integer", example="5")
     *     ),

     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/DeviceResource")
     *     ),
     * )
     */
    public function approveRequest(Request $request, $id)
    {
        $this->authorize(DeviceUpdate::KEY);

        try {
            /** @var $model Device */
            $model = $this->repo->getBy('id', $id, ['company.gpsDeviceSubscription']);

//            if($model->status_request->isClosed()){
//                throw new \Exception(__('exceptions.gps_device.device_must_be_pending'), 400);
//            }
            if(
                isset($model->gpsSubscription)
                && $model->gpsSubscription->status->isActiveTill()
            ){
                throw new \Exception(__('exceptions.gps_device.subscription.subscription_disabled'), 400);
            }

            return DeviceResource::make($this->service->approveRequest($model));
        } catch (\Throwable $e){

            return $this->makeErrorResponse(
                $e->getMessage(), $e->getCode()
            );
        }
    }

    /**
     * @param DeviceRequest $request
     * @return DeviceResource|JsonResponse
     * @throws AuthorizationException
     *
     * @OA\PUT(
     *     path="/v1/saas/gps-devices/{id}/deactivate",
     *     tags={"GPS Device"},
     *     summary="Deactivating a device after canceling a gps subscription",
     *     operationId="DeactivateDevice",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="{id}", in="path", required=true,
     *           description="ID device",
     *           @OA\Schema(type="integer", example="5")
     *     ),

     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/DeviceResource")
     *     ),
     * )
     */
    public function deactivate(Request $request, $id)
    {
        $this->authorize(DeviceUpdate::KEY);

        try {
            /** @var $model Device */
            $model = $this->repo->getBy('id', $id, ['company.gpsDeviceSubscription']);

            if(
                isset($model->gpsSubscription)
                && !($model->gpsSubscription->status->isCanceled()
                    || $model->gpsSubscription->status->isActiveTill()
                )
            ){
                throw new \Exception(__('exceptions.gps_device.subscription.subscription_disabled'), 400);
            }

            return DeviceResource::make($this->service->deactivateForce($model));
        } catch (\Throwable $e){
            return $this->makeErrorResponse(
                $e->getMessage(), $e->getCode()
            );
        }
    }

    /**
     * @return AnonymousResourceCollection
     * @throws AuthorizationException
     *
     * @OA\Get(path="/v1/saas/gps-devices/flespi", tags={"GPS Device"},
     *     summary="Returns deviced list from Flespi service",
     *     operationId="flespi devices list",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(
     *               @OA\Property(property="data", title="Data", type="array",
     *                   @OA\Items(ref="#/components/schemas/DeviceFlespiResource")
     *               ),
     *          ),
     *     ),
     * )
     */
    public function listFromFlespi(): AnonymousResourceCollection
    {
        $this->authorize(DeviceList::KEY);

        /** @var $command DeviceGetAllCommand */
        $command = resolve(DeviceGetAllCommand::class);
        /** @var $res DeviceEntityCollection */
        $res = $command->handler();

        return DeviceFlespiResource::collection(
            $res->filterImei($this->repo->getAllImei())
        );
    }
}
