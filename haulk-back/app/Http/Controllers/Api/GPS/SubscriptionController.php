<?php

namespace App\Http\Controllers\Api\GPS;

use App\Http\Controllers\ApiController;
use App\Http\Resources\Saas\GPS\GPSDeviceSubscriptionResource;
use App\Models\Saas\GPS\DeviceSubscription;
use App\Repositories\Saas\GPS\DeviceSubscriptionRepository;
use App\Services\Saas\GPS\Devices\DeviceSubscriptionService;
use Illuminate\Http\Request;

class SubscriptionController extends ApiController
{
    protected DeviceSubscriptionRepository $repo;
    protected DeviceSubscriptionService $service;

    public function __construct(
        DeviceSubscriptionRepository $repo,
        DeviceSubscriptionService $service
    )
    {
        parent::__construct();

        $this->repo = $repo;
        $this->service = $service;
    }

    /**
     * @OA\Put(path="/api/gps/subscription/{id}/cancel",
     *     tags={"Carrier GPS Subscription"},
     *     summary="Cancel gps subscriptions",
     *     operationId="CancelGpsSubscriptions",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="{id}", in="path", required=true,
     *            description="ID gps subscription",
     *            @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/GpsDeviceSubscriptionResource")
     *     ),
     * )
     */
    public function cancel(Request $request, $id)
    {
        $this->authorize('gps subscription');

        try {
            /** @var $model DeviceSubscription */
            $model = $this->repo->getBy('id', $id, [
                'company.subscription',
            ]);

            if(!$model){
                throw new \Exception(__('exceptions.gps_device.subscription.not_active_subscription'), 400);
            }

            return GPSDeviceSubscriptionResource::make($this->service->cancel($model));
        } catch (\Throwable $e){
            return $this->makeErrorResponse(
                $e->getMessage(), $e->getCode()
            );
        }
    }

    /**
     * @OA\Put(path="/api/gps/subscription/{id}/restore",
     *     tags={"Carrier GPS Subscription"},
     *     summary="Restore gps subscriptions",
     *     operationId="RestoreGpsSubscriptions",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="{id}", in="path", required=true,
     *            description="ID gps subscription",
     *            @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/GpsDeviceSubscriptionResource")
     *     ),
     * )
     */
    public function restore(Request $request, $id)
    {
        $this->authorize('gps subscription');

        try {
            /** @var $model DeviceSubscription */
            $model = $this->repo->getBy('id', $id, [
                'company.subscription',
                'devices',
            ]);

            if(!$model){
                throw new \Exception(__('exceptions.gps_device.subscription.not_active_subscription'), 400);
            }

            if(!$model->status->isActiveTill()){
                throw new \Exception(__('exceptions.gps_device.subscription.not_restore'), 400);
            }

            return GPSDeviceSubscriptionResource::make($this->service->restore($model));
        } catch (\Throwable $e){
            return $this->makeErrorResponse(
                $e->getMessage(), $e->getCode()
            );
        }
    }
}


