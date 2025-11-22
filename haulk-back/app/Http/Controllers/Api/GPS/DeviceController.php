<?php

namespace App\Http\Controllers\Api\GPS;

use App\Exceptions\Billing\HasUnpaidInvoiceException;
use App\Exceptions\Billing\NotActiveSubscriptionException;
use App\Exceptions\ValidationException;
use App\Http\Controllers\ApiController;
use App\Http\Requests\GPS\AlertIndexRequest;
use App\Http\Requests\GPS\Device\AttachVehicleRequest;
use App\Http\Requests\GPS\Device\DeviceFilterRequest;
use App\Http\Requests\GPS\Device\DeviceUpdateRequest;
use App\Http\Resources\Saas\GPS\Device\DeviceResource;
use App\Http\Resources\Vehicles\VehicleSimpleResource;
use App\ModelFilters\Vehicles\VehicleForDeviceFilter;
use App\Models\GPS\History;
use App\Models\Saas\GPS\Device;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Truck;
use App\Repositories\Saas\GPS\DeviceRepository;
use App\Services\Saas\GPS\Devices\DeviceService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use stringEncode\Exception;

class DeviceController extends ApiController
{
    protected DeviceRepository $repo;
    protected DeviceService $service;

    public function __construct(
        DeviceRepository $repo,
        DeviceService $service
    )
    {
        parent::__construct();

        $this->repo = $repo;
        $this->service = $service;
    }

    /**
     * @param AlertIndexRequest $request
     * @return AnonymousResourceCollection
     *
     * @OA\GET(
     *     path="/api/gps/devices",
     *     tags={"Carrier GPS Device"},
     *     summary="Device list",
     *     operationId="Devicee list",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="status", in="query", required=false,
     *           @OA\Schema(type="string", example="active", enum={"active", "inactive", "deleted"})
     *     ),
     *     @OA\Parameter(name="statuses", in="query", required=false,
     *            @OA\Schema(type="array", enum={"active", "inactive", "deleted"}, @OA\Items())
     *     ),
     *     @OA\Parameter(name="query", in="query", required=false,
     *            @OA\Schema(type="string", example="some search")
     *      ),
     *     @OA\Parameter(name="has_history", in="query", required=false,
     *             @OA\Schema(type="boolean", example=true)
     *      ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/DeviceResource")
     *     ),
     * )
     */
    public function list(DeviceFilterRequest $request): AnonymousResourceCollection
    {
        $this->authorize('gps devices');

        $models = Device::query()
            ->withTrashed()
            ->when(data_get($request, 'has_history'), function (Builder $b){
                // laravel не поддерживает метод has между бд, а история у нас в другой бд, приходиться костылить
                $h = History::query()
                    ->select('device_id')
                    ->distinct()
                    ->toBase()
                    ->get()
                    ->pluck('device_id')
                    ->toArray();

                $b->whereIn('id', $h);
            })
            ->with([
                'company',
                'truck',
                'trailer'
            ])
            ->filter(array_merge($request->validated(), ['company_id' => authUser()->getCompanyId()]))
            ->orderBy('status', 'asc')
            ->orderBy('name', 'asc')
            ->paginate(
                $request['per_page'] ?? 20,
                ['*'],
                'page',
                    $request['page'] ?? 1
            );

        return DeviceResource::collection($models);
    }

    /**
     * @param AlertIndexRequest $request
     * @return AnonymousResourceCollection
     *
     * @OA\GET(
     *     path="/api/gps/devices-free",
     *     tags={"Carrier GPS Device"},
     *     summary="Device list a free device",
     *     operationId="Devicee list a free device",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="with_device_id", in="query", description="With device id, for edit", required=false,
     *          @OA\Schema(type="integer", default="1",)
     *     ),
     *     @OA\Parameter(name="status", in="query", required=false,
     *           @OA\Schema(type="string", example="active", enum={"active", "inactive"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/DeviceResource")
     *     ),
     * )
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('gps devices');

        $models = Device::query()
            ->when(isset($request['status']), function ($q) use ($request){
                $q->where('status', $request['status']);
            })
            ->doesntHave('truck')
            ->doesntHave('trailer')
            ->where('company_id', authUser()->getCompanyId())
            ->when(isset($request['with_device_id']), function ($q) use ($request){
                $q->orWhere('id', $request['with_device_id']);
            })
            ->get();

        return DeviceResource::collection($models);
    }

    /**
     * @param AlertIndexRequest $request
     * @return AnonymousResourceCollection
     *
     * @OA\PUT(
     *     path="/api/gps/devices/{id}",
     *     tags={"Carrier GPS Device"},
     *     summary="Device update",
     *     operationId="Devicee update",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="{id}", in="path", required=true,
     *         description="ID device",
     *         @OA\Schema(type="integer", example="5")
     *     ),
     *     @OA\Parameter(name="company_device_name", in="query", description="company device name", required=true,
     *            @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/DeviceResource")
     *     ),
     * )
     */
    public function update(DeviceUpdateRequest $request, $id): DeviceResource
    {
        $this->authorize('gps devices');

        if(authUser()->getCompany()->hasUnpaidInvoices()){
            HasUnpaidInvoiceException::denied(authUser()->getCompany());
        }

        /** @var $model Device */
        $model = $this->repo->getBy('id', $id);

        $model = $this->service->editCompany($model, $request->all());

        return DeviceResource::make($model);
    }

    /**
     * Get device list
     *
     * @param AlertIndexRequest $request
     * @return AnonymousResourceCollection
     *
     * @OA\GET(
     *     path="/api/gps/devices/has-active-at-vehicle",
     *     tags={"Carrier GPS Device"},
     *     summary="Check if the vehicle has active devices",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(response="200", description="Simple response",
     *
     *     @OA\JsonContent(ref="#/components/schemas/SimpleResponse")),
     * )
     */
    public function hasActiveAtVehicle(Request $request): JsonResponse
    {
        $this->authorize('gps devices');

        return $this->makeResponse(
            $this->repo->hasActiveAtVehicle(authUser()->getCompanyId())
        );
    }

    /**
     * Get device list
     *
     * @param AlertIndexRequest $request
     * @return AnonymousResourceCollection
     *
     * @OA\GET(
     *     path="/api/gps/devices/has-active",
     *     tags={"Carrier GPS Device"},
     *     summary="Check if has active devices",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(response="200", description="Simple response",
     *
     *     @OA\JsonContent(ref="#/components/schemas/SimpleResponse")),
     * )
     */
    public function hasActive(Request $request): JsonResponse
    {
        $this->authorize('gps devices');

        return $this->makeResponse(
            $this->repo->hasActive(authUser()->getCompanyId())
        );
    }

    /**
     * Get device list
     *
     * @param AlertIndexRequest $request
     * @return AnonymousResourceCollection
     *
     * @OA\GET(
     *     path="/api/gps/devices/vehicle-without-device",
     *     tags={"Carrier GPS Device"},
     *     summary="Get simle list vechicle withot device",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="search", in="query", required=false,
     *          @OA\Schema(type="string", example="11ee1", description="Search vechicle by vin or unit_number")
     *     ),
     *     @OA\Response(response="200", description="Simple response",
     *          @OA\JsonContent(ref="#/components/schemas/VehicleSimpleResource")
     *      ),
     * )
     */
    public function vehicleWithoutDevice(Request $request): AnonymousResourceCollection
    {
        $trucks = Truck::query()
            ->whereDoesntHave('gpsDeviceWithTrashed')
            ->with(['owner'])
            ->filter(
                array_merge($request->all(), ['company_id' => authUser()->getCompanyId()]),
                VehicleForDeviceFilter::class
            )
            ->get();
        $trailer = Trailer::query()
            ->whereDoesntHave('gpsDeviceWithTrashed')
            ->with(['owner'])
            ->filter(
                array_merge($request->all(), ['company_id' => authUser()->getCompanyId()]),
                VehicleForDeviceFilter::class
            )
            ->get();

        return VehicleSimpleResource::collection($trucks->merge($trailer));
    }

    /**
     * Attach vehicle to gps device
     *
     * @param AlertIndexRequest $request
     * @return AnonymousResourceCollection
     *
     * @OA\PUT(
     *     path="/api/gps/devices/{id}/attach-vehicle",
     *     tags={"Carrier GPS Device"},
     *     summary="Attach vehicle to a gps device",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="{id}", in="path", required=true,
     *          description="ID device",
     *          @OA\Schema(type="integer", example="5")
     *      ),
     *      @OA\Parameter(name="is_truck", in="query", description="Is truck", required=true,
     *             @OA\Schema(type="boolean")
     *      ),
     *     @OA\Parameter(name="id_vehicle", in="query", description="Vehicle id", required=true,
     *              @OA\Schema(type="integer")
     *       ),
     *     @OA\Response(response="200", description="Simple response",
     *          @OA\JsonContent(ref="#/components/schemas/DeviceResource")
     *      ),
     * )
     */
    public function attachVehicle(AttachVehicleRequest $request, $id): DeviceResource
    {
        $this->authorize('gps devices');

        if(authUser()->getCompany()->prePaymentAttemptsCountExhausted()){
            HasUnpaidInvoiceException::denied(authUser()->getCompany());
        }

        /** @var $model Device */
        $model = $this->repo->getBy('id', $id);
        if($model->vehicle()){
            throw ValidationException::withMessages(['id' => __("exceptions.gps_device.has_attached_vehicle")]);
        }

        if($request['is_truck']){
            $vehicle = Truck::query()->where('id', $request['id_vehicle'])->first();
            $msg = __('exceptions.vehicle.truck.not_found', ["attribute" => 'id', "value" => $request['id_vehicle']]);
        } else {
            $vehicle = Trailer::query()->where('id', $request['id_vehicle'])->first();
            $msg = __('exceptions.vehicle.trailer.not_found', ["attribute" => 'id', "value" => $request['id_vehicle']]);
        }

        if(!$vehicle){
            throw ValidationException::withMessages(['id_vehicle' => $msg]);
        }

        $this->service->attachToVehicle($model, $vehicle);

        return DeviceResource::make($model->refresh());
    }

    /**
     * Get device list
     *
     * @param AlertIndexRequest $request
     * @return AnonymousResourceCollection
     *
     * @OA\PUT(
     *     path="/api/gps/devices/{id}/toggle-activate",
     *     tags={"Carrier GPS Device"},
     *     summary="Send request for activate/deactivate device",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="{id}", in="path", required=true,
     *          description="ID device",
     *          @OA\Schema(type="integer", example="5")
     *      ),
     *     @OA\Response(response="200", description="Simple response",
     *          @OA\JsonContent(ref="#/components/schemas/DeviceResource")
     *      ),
     * )
     */
    public function toggleActivate(Request $request, $id): DeviceResource
    {
        $this->authorize('gps devices');

        if(authUser()->getCompany()->hasUnpaidInvoices()){
            HasUnpaidInvoiceException::denied(authUser()->getCompany());
        }
        if(
            authUser()->getCompany()->isTrialExpired() && !authUser()->getCompany()->hasPaymentMethod()
        ){
            NotActiveSubscriptionException::denied(authUser()->getCompany());
        }

        /** @var $model Device */
        $model = $this->repo->getBy('id', $id);

        $this->service->toggleActivate($model, authUser());

        return DeviceResource::make($model->refresh());
    }
}

