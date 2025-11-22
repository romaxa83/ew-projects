<?php

namespace App\Http\Controllers\V1\Saas\GPS;

use App\Http\Controllers\ApiController;
use App\Http\Requests\Saas\GPS\Device\DeviceRequestCreateRequest;
use App\Http\Requests\Saas\GPS\Device\DeviceRequestFilterRequest;
use App\Http\Requests\Saas\GPS\Device\DeviceRequestUpdateRequest;
use App\Http\Resources\Saas\GPS\DeviceRequest\DeviceRequestResource;
use App\Models\Saas\Company\Company;
use App\Models\Saas\GPS\DeviceRequest;
use App\Permissions\Saas\GPS\Devices\DeviceList;
use App\Permissions\Saas\GPS\Devices\Request\DeviceRequestList;
use App\Permissions\Saas\GPS\Devices\Request\DeviceRequestUpdate;
use App\Repositories\Saas\GPS\DeviceRepository;
use App\Repositories\Saas\GPS\DeviceRequestRepository;
use App\Services\Saas\GPS\Devices\DeviceRequestService;
use App\Services\Saas\GPS\Devices\DeviceService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DeviceRequestController extends ApiController
{
    protected DeviceRequestService $service;
    protected DeviceRequestRepository $repo;

    public function __construct(
        DeviceRequestService $service,
        DeviceRequestRepository $repo
    )
    {
        parent::__construct();

        $this->service = $service;
        $this->repo = $repo;
    }

    /**
     * @OA\Get(path="/v1/saas/gps-devices/requests",
     *     tags={"GPS Device request"},
     *     summary="Returns requests devices list",
     *     operationId="DeviceRequestList",
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

     *     @OA\Parameter(name="company_id", in="query", required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(name="status", in="query", required=false,
     *          @OA\Schema(type="string", example="active", enum={"new", "in_work", "closed"})
     *      ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/DeviceRequestPaginatedResource")
     *     ),
     * )
     */
    public function index(DeviceRequestFilterRequest $request): AnonymousResourceCollection
    {
        $this->authorize(DeviceRequestList::KEY);

        $model = DeviceRequest::query()
            ->filter($request->validated())
            ->with([
                'company',
                'user'
            ])
            ->orderBy('created_at', 'desc')
            ->paginate(
                $request->getPerPage(),
                ['*'],
                'page',
                $request->getPage()
            );

        return DeviceRequestResource::collection($model);
    }

    /**
     * @OA\Put(path="/v1/saas/gps-devices/requests/{id}",
     *     tags={"GPS Device request"},
     *     summary="Update requests devices",
     *     operationId="UpdateDeviceRequest",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Parameter(name="{id}", in="path", required=true,
     *            description="ID device request",
     *            @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Parameter(name="status", in="query", required=true,
     *          @OA\Schema(type="string", example="active", enum={"in_work", "closed"})
     *     ),
     *     @OA\Parameter(name="comment", in="query", required=false,
     *           @OA\Schema(type="string", example="some comment")
     *       ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/DeviceRequestRawResource")
     *     ),
     * )
     */
    public function update(DeviceRequestUpdateRequest $request, $id): DeviceRequestResource
    {
        $this->authorize(DeviceRequestUpdate::KEY);

        $model = $this->service->update(
            $this->repo->getBy('id', $id),
            $request->validated()
        );

        return DeviceRequestResource::make($model);
    }

    /**
     * @OA\Post(path="/v1/saas/gps-devices/requests",
     *     tags={"GPS Device request"},
     *     summary="Create requests devices for backoffice",
     *     operationId="CreateDeviceRequestBackoffice",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Parameter(name="qty", in="query", description="device quantity", required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(name="company_id", in="query", description="Company ID", required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(name="company_id", in="query", description="Company ID", required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/DeviceRequestRawResource")
     *     ),
     * )
     */
    public function create(DeviceRequestCreateRequest $request): DeviceRequestResource
    {
        $this->authorize(DeviceRequestUpdate::KEY);

        $company = Company::find($request['company_id']);

        $model = $this->service->createFromBackOffice(
            $company,
            $request['user'],
            $request->validated()
        );

        return DeviceRequestResource::make($model);
    }
}

