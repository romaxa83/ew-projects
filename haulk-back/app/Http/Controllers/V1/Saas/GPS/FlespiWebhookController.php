<?php

namespace App\Http\Controllers\V1\Saas\GPS;

use App\Enums\Saas\GPS\DeviceHistoryContext;
use App\Enums\Saas\GPS\DeviceStatus;
use App\Http\Controllers\ApiController;
use App\Models\GPS\Alert;
use App\Models\Saas\GPS\Device;
use App\Models\Saas\GPS\DeviceHistory;
use App\Repositories\Saas\GPS\DeviceRepository;
use App\Services\GPS\GPSDataService;
use App\Services\Saas\GPS\Devices\DeviceSubscriptionService;
use App\ValueObjects\Phone;
use Illuminate\Http\Request;

class FlespiWebhookController extends ApiController
{
    protected DeviceRepository $repo;
    protected DeviceSubscriptionService $deviceSubscriptionService;

    public function __construct(
        DeviceRepository $repo,
        DeviceSubscriptionService $deviceSubscriptionService
    )
    {
        parent::__construct();

        $this->repo = $repo;
        $this->deviceSubscriptionService = $deviceSubscriptionService;
    }

    public function update(Request $request)
    {
        logger_flespi('[flespi-webhook-update] Request data: ', $request->all());

        try {
            /** @var $model Device */
            $model = $this->repo->getBy('flespi_device_id', data_get($request, 'device_id'));
            if(!$model){
                throw new \Exception("[flespi-webhook-update] Device [".data_get($request, 'device_id')."] not found");
            }

            $model->imei = data_get($request, 'new_data.configuration.ident');
            $model->phone = new Phone(data_get($request, 'new_data.configuration.phone'));

            DeviceHistory::create($model, DeviceHistoryContext::EDIT_FLESPI());

            $model->save();

            logger_flespi('[flespi-webhook-update] SUCCESS UPDATE');
        } catch (\Exception $e) {
            logger_flespi('[flespi-webhook-update] FAIL UPDATE', [
                'msg' => $e->getMessage()
            ]);
        }
    }

    // @see event_code - https://flespi.io/docs/#/gw/devices
    public function connectDevice(Request $request): void
    {
        logger_flespi('[flespi-webhook-connect] Request data: ', $request->all());

//        if(data_get($request, 'data.payload.ident')){
//            /** @var $device Device */
//            $device = Device::query()
//                ->with([
//                    'truck.lastGPSHistory',
//                    'trailer.lastGPSHistory'
//                ])
//                ->where('imei', data_get($request, 'data.payload.ident'))
//                ->first()
//            ;
//
//            if(
//                $device
//                && $device->vehicle()
//                && $device->vehicle()->last_gps_history_id
//            ){
//                /** @var $service GPSDataService */
//                $service = resolve(GPSDataService::class);
//
//                if(
//                    data_get($request, 'data.payload.event_code')
//                    && data_get($request, 'data.payload.event_code') == 301
//                    && $device->isConnected()
//                ){
//                    $device->update(['is_connected' => false]);
//
//
//
//                    $service->createHistoryFromDevice($device, Alert::ALERT_DEVICE_CONNECTION_LOST);
//                }
//                if(
//                    data_get($request, 'data.payload.event_code')
//                    && data_get($request, 'data.payload.event_code') == 300
//                    && !$device->isConnected()
//                ){
//                    $device->update(['is_connected' => true]);
//
//                    $service->createHistoryFromDevice($device, Alert::ALERT_DEVICE_CONNECTION_RESTORED);
//                }
//            }
//        }
    }

    public function disconnectDevice(Request $request)
    {
        logger_flespi('[flespi-webhook-disconnect] Request data: ', $request->all());
    }

    public function delete(Request $request)
    {
        logger_flespi('[flespi-webhook-delete] Request data: ', $request->all());

        try {
            /** @var $model Device */
            $model = $this->repo->getBy('flespi_device_id', data_get($request, 'data.origin_id'));
            if(!$model){
                throw new \Exception("[flespi-webhook-delete] Device [".data_get($request, 'data.origin_id')."] not found");
            }

            $model->status = DeviceStatus::DELETED;

            DeviceHistory::create($model, DeviceHistoryContext::EDIT_FLESPI());

            $model->save();
            $model->delete();

            $this->deviceSubscriptionService->checkActiveDeviceInSubscription($model->company_id);

            logger_flespi('[flespi-webhook-delete] SUCCESS Delete');
        } catch (\Exception $e) {
            logger_flespi('[flespi-webhook-delete] FAIL Delete', [
                'msg' => $e->getMessage()
            ]);
        }
    }
}

