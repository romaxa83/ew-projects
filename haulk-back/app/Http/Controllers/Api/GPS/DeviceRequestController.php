<?php

namespace App\Http\Controllers\Api\GPS;

use App\Enums\Saas\GPS\Request\DeviceRequestStatus;
use App\Exceptions\Billing\HasUnpaidInvoiceException;
use App\Http\Controllers\ApiController;
use App\Http\Requests\GPS\AlertIndexRequest;
use App\Http\Requests\GPS\Device\DeviceSendRequest;
use App\Models\Saas\GPS\DeviceRequest;
use App\Services\Saas\GPS\Devices\DeviceRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DeviceRequestController extends ApiController
{
    protected DeviceRequestService $service;

    public function __construct(
        DeviceRequestService $service
    )
    {
        parent::__construct();

        $this->service = $service;
    }

    /**
     * @param AlertIndexRequest $request
     * @return AnonymousResourceCollection
     *
     * @OA\Post(
     *     path="/api/gps/devices/request",
     *     tags={"Carrier GPS Device request"},
     *     summary="Send a request for the number of devices",
     *     operationId="DeviceRequest",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Parameter(name="qty", in="query", description="device quantity", required=true,
     *            @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/SimpleResponse")
     *     ),
     * )
     */
    public function create(DeviceSendRequest $request): JsonResponse
    {
//        $this->authorize('gps-devices request');

        if(authUser()->getCompany()->hasUnpaidInvoices()){
            HasUnpaidInvoiceException::denied(authUser()->getCompany());
        }

        $model = $this->service->create(authUser(), $request->validated());

        if($model){
            return $this->makeResponse(true);
        }

        return $this->makeResponse(false);
    }

    /**
     * @param AlertIndexRequest $request
     * @return AnonymousResourceCollection
     *
     * @OA\Get(
     *     path="/api/gps/devices/request/can-add",
     *     tags={"Carrier GPS Device request"},
     *     summary="Can send a request for the number of devices",
     *     operationId="CanAddDeviceRequest",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/SimpleResponse")
     *     ),
     * )
     */
    public function canCreate(Request $request): JsonResponse
    {
//        $this->authorize('gps-devices request');

        return $this->makeResponse(
            !DeviceRequest::query()
                ->where('company_id', authUser()->getCompanyId())
                ->where('status','!=', DeviceRequestStatus::CLOSED)
                ->exists()
        );
    }
}

