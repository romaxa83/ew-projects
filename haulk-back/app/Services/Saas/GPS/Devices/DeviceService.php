<?php

namespace App\Services\Saas\GPS\Devices;

use App\Enums\Saas\GPS\DeviceHistoryContext;
use App\Enums\Saas\GPS\DeviceRequestStatus;
use App\Enums\Saas\GPS\DeviceStatus;
use App\Enums\Saas\GPS\DeviceStatusActivateRequest;
use App\Enums\Saas\GPS\DeviceSubscriptionStatus;
use App\Exceptions\ValidationException;
use App\Models\Alerts\Alert;
use App\Models\GPS\History;
use App\Models\Notifications\Notification;
use App\Models\Saas\GPS\Device;
use App\Models\Saas\GPS\DeviceHistory;
use App\Models\Users\User;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Truck;
use App\Models\Vehicles\Vehicle;
use App\Services\Events\EventService;
use App\Services\Events\GPS\Devices\DeviceApproveActivityEventService;
use App\Services\Notifications\Inner\Patterns\Device\RequestActivate;
use App\Services\Saas\GPS\Histories\HistoryService;
use App\ValueObjects\Phone;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;

class DeviceService
{
    protected DeviceSubscriptionService $subscriptionService;

    public function __construct(DeviceSubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    public function create(array $data): Device
    {
        /** @var Device $model */
        $model = new Device();

        $model = $this->fill($model, $data);

        $model = $this->setStatus($model, DeviceStatus::INACTIVE());

        $model->save();

        DeviceHistory::create($model, DeviceHistoryContext::CREATE());

        return $model;
    }

    public function update(Device $model, array $data): Device
    {
        $model->name = data_get($data, 'name');
        $model->company_id = data_get($data, 'company_id');
        $model->imei = data_get($data, 'imei');

        if(data_get($data, 'phone')){
            $model->phone = new Phone(data_get($data, 'phone'));
        }
        if(data_get($data, 'device_id')){
            $model->flespi_device_id = data_get($data, 'device_id');
        }

        DeviceHistory::create($model, DeviceHistoryContext::EDIT());

        $model->save();

        return $model;
    }

    public function editCompany(Device $model, array $data): Device
    {
        $model->company_device_name = data_get($data, 'company_device_name');

        DeviceHistory::create($model, DeviceHistoryContext::EDIT_COMPANY());

        $model->save();

        return $model;
    }

    private function fill(Device $model, array $data): Device
    {
        $model->name = data_get($data, 'name');
        $model->imei = data_get($data, 'imei');
        $model->flespi_device_id = data_get($data, 'device_id');
        $model->company_id = data_get($data, 'company_id');
        $model->phone = data_get($data, 'phone') ? new Phone(data_get($data, 'phone')) : null;
        $model->status_request = DeviceRequestStatus::NONE();
        $model->status_activate_request = DeviceStatusActivateRequest::NONE();

        return $model;
    }

    private function setStatus(Device $model , DeviceStatus $status, bool $save = false): Device
    {
        $model->status = $status;
        if (!($model->phone && $model->company_id)) {
            $model->status = DeviceStatus::INACTIVE();
            $model->active_at = null;
            if(!$model->inactive_at){
                $model->inactive_at = CarbonImmutable::now();
            }
        }

        $model->load(['company.gpsDevices']);

        if($model->status->isActive()){
            $model->inactive_at = null;
            if(!$model->active_at){
                $model->active_at = CarbonImmutable::now();
            }

//            if($model->company->isGPSEnabled()){
//                if($model->company->gpsDevices->countByStatus(DeviceStatus::ACTIVE()) == 0){
//                    $model->company->update(['gps_enabled_start_at' => CarbonImmutable::now()]);
//                }
//            }
        }

//        if($model->status->isInactive()){
//            $model->load(['company.gpsDevices']);
//            if($model->company && !$model->company->isGPSEnabled()){
//                if($model->company->gpsDevices->countByStatus(DeviceStatus::ACTIVE()) == 0){
//                    $model->company->update(['gps_enabled_end_at' => CarbonImmutable::now()]);
//                }
//            }
//        }

        if($save){
            $model->save();
        }

        return $model;
    }

    /**
     * @param int|null $currentTruckId
     * @param int|null $currentTrailerId
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getAvailableDevices(int $companyId, ?int $currentTruckId = null, ?int $currentTrailerId = null)
    {
        $devices = Device::query()
            ->where('company_id', $companyId)
            ->orderBy('id', 'desc');

        if (!$currentTrailerId && !$currentTruckId) {
            $devices->whereDoesntHave('truck')
                ->whereDoesntHave('trailer');
        }

        if ($currentTrailerId) {
            $devices->whereDoesntHave('truck')
                ->where(function (Builder $query) use ($currentTrailerId) {
                    $query->whereDoesntHave('trailer')
                        ->orWhereHas('trailer', function (Builder $query) use ($currentTrailerId) {
                            $query->where('id', $currentTrailerId);
                        });
                });
        }

        if ($currentTruckId) {
            $devices->whereDoesntHave('trailer')
                ->where(function (Builder $query) use ($currentTruckId) {
                    $query->whereDoesntHave('truck')
                        ->orWhereHas('truck', function (Builder $query) use ($currentTruckId) {
                            $query->where('id', $currentTruckId);
                        });
                });
        }

        return $devices->get();
    }

    public function forceDelete()
    {
        try {
            \DB::beginTransaction();

            $models = Device::query()
                ->onlyTrashed()
                ->where('deleted_at', '<=', CarbonImmutable::now()->subDays(Device::DAYS_TO_FORCE_DELETE))
                ->get();

            foreach ($models as $model){
                /** @var $model Device */
                History::query()->where('device_id', $model->id)->delete();

                DeviceHistory::query()->where('device_id', $model->id)->delete();

                $model->forceDelete();
            }

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            throw new \Exception($e);
        }
    }

    public function attachToVehicle(Device $model , Vehicle $vehicle)
    {
        try {
            \DB::beginTransaction();

            $event = EventService::vehicle($vehicle)
                ->user(authUser());

            $vehicle->update(['gps_device_id' => $model->id]);

            DeviceHistory::create($model, DeviceHistoryContext::ATTACH_TO_VEHICLE(),[
                'old' => [],
                'new' => ['vehicle_type' => get_class($vehicle), 'vehicle_id' => $vehicle->id],
            ]);

            if(!$vehicle->last_gps_history_id){
                $historyService = resolve(HistoryService::class);
                $historyService->createEmptyData($vehicle);
            }

            $event->update();

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            throw new \Exception($e);
        }
    }

    public function toggleActivate(Device $device, User $user): Device
    {
        if($device->status->isActive()){
            return $this->deactivateRequest($device, $user);
        }
        if($device->status->isInactive()){
            return $this->activateRequest($device, $user);
        }

        throw ValidationException::withMessages(['id' => __("exceptions.gps_device.cant_toggle_because_delete")])->status(422);
    }

    public function activateRequest(Device $device, User $user): Device
    {
        $device->status_request = DeviceRequestStatus::PENDING();
        $device->status_activate_request = DeviceStatusActivateRequest::ACTIVATE();
        $device->send_request_user_id = $user->id;
        $device->save();

        Notification::create(new RequestActivate($device, DeviceStatus::ACTIVE()));

        return $device;
    }

    public function deactivateRequest(Device $device, User $user): Device
    {
        $device->status_request = DeviceRequestStatus::PENDING();
        $device->status_activate_request = DeviceStatusActivateRequest::DEACTIVATE();
        $device->send_request_user_id = $user->id;
        $device->save();

        Notification::create(new RequestActivate($device, DeviceStatus::INACTIVE()));

        return $device;
    }

    public function approveRequest(Device $device): Device
    {
        if($device->status->isInactive()){
            return $this->activate($device);
        }

        return $this->deactivate($device);
    }

    public function deactivateForce(Device $device): Device
    {
        $device->status_request = DeviceRequestStatus::CLOSED();
        $device->request_closed_at = CarbonImmutable::now();
        $device->status_activate_request = DeviceStatusActivateRequest::NONE();
        $device->active_till_at = null;
        $device->status = DeviceStatus::INACTIVE();
        $device->inactive_at = CarbonImmutable::now();

        $device->save();

        return $device;
    }

    public function activate(Device $device): Device
    {
        $device->load([
            'company.gpsDeviceSubscription',
            'sendRequestUser',
        ]);


        if(!($device->company_id && $device->phone)){
            throw new \Exception(__("exceptions.gps_device.no_activate_not_company_or_phone"), 400);
        }

        try {
            \DB::beginTransaction();

            if($device->status_request->isPending()){
                $device->status_request = DeviceRequestStatus::CLOSED();
                $device->request_closed_at = CarbonImmutable::now();
            }
            $device->status_activate_request = DeviceStatusActivateRequest::NONE();
            $device->status = DeviceStatus::ACTIVE();
            $device->active_at = CarbonImmutable::now();
            $device->active_till_at = null;
            $device->inactive_at = null;

            DeviceHistory::create($device, DeviceHistoryContext::ACTIVATE());

            $device->save();

            if(
                $device->gpsSubscription
                && (
                    $device->gpsSubscription->status->isDraft()
                    || $device->gpsSubscription->status->isCanceled()
                )
            ){
                $this->subscriptionService->setStatus(
                    $device->gpsSubscription,
                    DeviceSubscriptionStatus::ACTIVE(),
                    true
                );

                $this->subscriptionService->changeRate($device->gpsSubscription);
            }

            $this->createAlert($device, 'notification.device.for_crm.activate');

            DeviceApproveActivityEventService::deviceToggleActivity($device)
                ->user($device->sendRequestUser)
                ->toggleActivity()
                ->broadcast();

            \DB::commit();

            return $device;
        } catch (\Exception $e) {
            \DB::rollBack();
            throw new \Exception($e);
        }
    }

    public function deactivate(Device $device): Device
    {
        try {
            \DB::beginTransaction();

            if($device->status_request->isPending()){
                $device->status_request = DeviceRequestStatus::CLOSED();
                $device->request_closed_at = CarbonImmutable::now();
            }

            $device->status_activate_request = DeviceStatusActivateRequest::NONE();

            // если у нас еще активна подписка, то девайс оставляем активным, но проставляем дату active_till_at
            if(
                !$device->gpsSubscription->status->isCanceled()
            ){
                $device->active_till_at = $device->company->subscription->billing_end;
                DeviceHistory::create($device, DeviceHistoryContext::ACTIVATE_TILL());
            } else {
                $device->status = DeviceStatus::INACTIVE();
                $device->inactive_at = CarbonImmutable::now();
                DeviceHistory::create($device, DeviceHistoryContext::INACTIVE());
            }

            $device->save();

            if($device->status->isInactive()){
                if($truck = Truck::query()->where('gps_device_id', $device->id)->first()){
                    $truck->update(['gps_device_id' => null]);
                }
                if($trailer = Trailer::query()->where('gps_device_id', $device->id)->first()){
                    $trailer->update(['gps_device_id' => null]);
                }
            }

           $this->createAlert($device, 'notification.device.for_crm.deactivate');

            DeviceApproveActivityEventService::deviceToggleActivity($device)
                ->user($device->sendRequestUser)
                ->toggleActivity()
                ->broadcast();

            \DB::commit();

            return $device;
        } catch (\Exception $e) {
            \DB::rollBack();
            throw new \Exception($e);
        }
    }

    public function deactivatingProcess()
    {
        try {
            \DB::beginTransaction();

            $models = Device::query()
                ->with(['gpsSubscription'])
                ->where('status', DeviceStatus::ACTIVE)
                ->whereNotNull('active_till_at')
                ->where('active_till_at' , '<', CarbonImmutable::now())
                ->get();

            foreach ($models as $model){
                /** @var $model Device */
                $model->status_activate_request = DeviceStatusActivateRequest::NONE();
                $model->status = DeviceStatus::INACTIVE();
                $model->inactive_at = CarbonImmutable::now();
                $model->active_at = null;
                $model->active_till_at = null;

                DeviceHistory::create($model, DeviceHistoryContext::INACTIVE());

                $model->save();

                $this->subscriptionService->checkActiveDeviceInSubscription($model->company_id);
            }

            $ids = $models->pluck('id')->toArray();
            // отвязываем девайсы от техники
            Truck::query()->whereIn('gps_device_id', $ids)->update(['gps_device_id' => null]);
            Trailer::query()->whereIn('gps_device_id', $ids)->update(['gps_device_id' => null]);

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            throw new \Exception($e);
        }
    }

    private function createAlert(Device $device, string $msg): Alert
    {
        $model = new Alert();
        $model->carrier_id = $device->company_id;
        $model->recipient_id = null;
        $model->type = Alert::DEVICE_TOGGLE_ACTIVITY;
        $model->meta = [
            'device_id' => $device->id,
            'status' => $device->status->value,
        ];
        $model->placeholders = [
            'imei' => $device->imei
        ];
        $model->message = $msg;
        $model->save();

        return $model;
    }

    public function removeClosedDeviceRequestStatus(): void
    {
        Device::query()
            ->where('request_closed_at', '<' , CarbonImmutable::now()->subDays(1))
            ->get()
            ->each(function(Device $model){
                DeviceHistory::createRemoveClosedStatus($model);

                $model->update([
                    'request_closed_at' => null,
                    'status_request' => DeviceRequestStatus::NONE(),
                ]);
            })
        ;
    }
}
