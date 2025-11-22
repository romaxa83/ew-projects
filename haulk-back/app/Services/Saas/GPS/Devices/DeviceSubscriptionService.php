<?php

namespace App\Services\Saas\GPS\Devices;

use App\Enums\Format\DateTimeEnum;
use App\Enums\Saas\GPS\DeviceHistoryContext;
use App\Enums\Saas\GPS\DeviceRequestStatus;
use App\Enums\Saas\GPS\DeviceStatus;
use App\Enums\Saas\GPS\DeviceStatusActivateRequest;
use App\Enums\Saas\GPS\DeviceSubscriptionStatus;
use App\Models\Alerts\Alert;
use App\Models\Notifications\Notification;
use App\Models\Saas\Company\Company;
use App\Models\Saas\GPS\Device;
use App\Models\Saas\GPS\DeviceHistory;
use App\Models\Saas\GPS\DeviceSubscription;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Truck;
use App\Services\Events\GPS\Devices\DeviceSubscriptionEventService;
use App\Services\Notifications\Inner\Patterns\CompanySubscription\CompanySubscriptionCancel;
use App\Services\Notifications\Inner\Patterns\CompanySubscription\CompanySubscriptionUnpaid;
use App\Services\Notifications\Inner\Patterns\GpsSubscription\SubscriptionCancel;
use App\Services\Notifications\Inner\Patterns\GpsSubscription\WarningSubscriptionCancel;
use Carbon\CarbonImmutable;

class DeviceSubscriptionService
{
    public function create(Company $company): DeviceSubscription
    {
        $model = new DeviceSubscription();

        $model->status = DeviceSubscriptionStatus::DRAFT();
        $model->company_id = $company->id;

        $model->save();

        return $model;
    }

    public function setNewRate(Company $company, $newRate): DeviceSubscription
    {
        $company->gpsDeviceSubscription->next_rate = $newRate;
        $company->gpsDeviceSubscription->save();

        $user = $company->getSuperAdmin();

        $alert = new Alert();
        $alert->carrier_id = $company->id;
        $alert->type = Alert::DEVICE_SUBSCRIPTION_CHANGE_RATE;
        $alert->placeholders = [
            'rate' => $newRate,
            'date' => $company->subscription->billing_end->addDay()->format(DateTimeEnum::DATE_FRONT),
        ];
        $alert->message = 'notification.gps_subscription.change_rate';
        $alert->save();

        DeviceSubscriptionEventService::deviceSubscription($company->gpsDeviceSubscription)
            ->user($user)
            ->changeRate()
            ->broadcast();

        return $company->gpsDeviceSubscription;
    }

    public function changeRate(DeviceSubscription $model): DeviceSubscription
    {
        if($model->next_rate){
            $model->current_rate = $model->next_rate;
            $model->next_rate = null;
            $model->save();
        }

        return $model;
    }

    public function setStatus(
        DeviceSubscription $model,
        DeviceSubscriptionStatus $status,
        bool $save = false
    ): DeviceSubscription
    {
        try {
            \DB::beginTransaction();

            if($status->isActive()){
                $model->status = $status;
                $model->activate_at = CarbonImmutable::now();
                $model->send_warning_notify = false;
                $model->access_till_at = null;
                $model->activate_till_at = null;
                $model->canceled_at = null;

                $devices = $model->devices()
                    ->where('status_request', DeviceRequestStatus::CANCEL_SUBSCRIPTION)
                    ->get();
                foreach ($devices as $device) {
                    /** @var $device Device */
                    $device->status_request = DeviceRequestStatus::NONE();
                    if($device->status->isActive()){
                        $device->active_till_at = null;
                    }

                    DeviceHistory::create($device, DeviceHistoryContext::SUBSCRIPTION_RESTORE());

                    $device->save();
                }
            }

            if ($save) $model->save();

            \DB::commit();

            return $model;
        } catch (\Exception $e) {
            \DB::rollBack();
            throw new \Exception($e);
        }
    }

    public function cancel(DeviceSubscription $model, bool $withCompanySubscription = false): DeviceSubscription
    {
        try {
            \DB::beginTransaction();

            $model->status = DeviceSubscriptionStatus::ACTIVE_TILL();
            $model->activate_till_at = $model->company->subscription->billing_end;

            $model->save();

            if($withCompanySubscription){
                Notification::create(new CompanySubscriptionCancel($model->company));
            } else {
                Notification::create(new SubscriptionCancel($model));
            }


            foreach ($model->devices as $device){
                /** @var $device Device */
                $device->status_request = DeviceRequestStatus::CANCEL_SUBSCRIPTION;
                $device->status_activate_request = DeviceStatusActivateRequest::NONE;
                if($device->status->isActive()){
                    $device->active_till_at = $model->company->subscription->billing_end;
                }

                DeviceHistory::create($device, DeviceHistoryContext::SUBSCRIPTION_CANCEL());

                $device->save();
            }

            /** @var $deviceRequestService DeviceRequestService */
            $deviceRequestService = resolve(DeviceRequestService::class);
            $deviceRequestService->closedIfUnsubscribe($model->company);

            \DB::commit();

            return $model;
        } catch (\Exception $e) {
            \DB::rollBack();
            throw new \Exception($e);
        }
    }

    public function cancelIfHaulUnsubscribe(DeviceSubscription $model): DeviceSubscription
    {
        try {
            \DB::beginTransaction();

            Notification::create(new CompanySubscriptionCancel($model->company));

            $this->completeCancellation($model);

            \DB::commit();

            return $model;
        } catch (\Exception $e) {
            \DB::rollBack();
            throw new \Exception($e);
        }
    }

    public function cancelFromUnpaidSubscription(
        DeviceSubscription $model
    ): DeviceSubscription
    {
        try {
            \DB::beginTransaction();

            foreach($model->devices as $device){
                /** @var $device Device */
                if($device->status->isActive()){
                    $device->active_till_at = $model->company->subscription->billing_end;
                    $device->status_activate_request = DeviceStatusActivateRequest::DEACTIVATE();
                    $device->status_request = DeviceRequestStatus::PENDING();

                    DeviceHistory::create($device, DeviceHistoryContext::ACTIVATE_TILL_UNPAID());

                    $device->save();
                }
            }

            Notification::create(new CompanySubscriptionUnpaid($model->company));

            \DB::commit();

            return $model;
        } catch (\Exception $e) {
            \DB::rollBack();
            throw new \Exception($e);
        }
    }

    public function restore(DeviceSubscription $model): DeviceSubscription
    {
       return $this->setStatus($model, DeviceSubscriptionStatus::ACTIVE(), true);
    }

    public function cancelingProcess()
    {
        $models = DeviceSubscription::query()
            ->whereNotNull('activate_till_at')
            ->where('activate_till_at' , '<', CarbonImmutable::now())
            ->get()
        ;

        try {
            \DB::beginTransaction();

            foreach ($models as $model){
                // кенселим подписку
               $this->completeCancellation($model);
            }

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            throw new \Exception($e);
        }
    }

    public function completeCancellation(DeviceSubscription $model): void
    {
        /** @var $model DeviceSubscription */
        $model->update([
            'activate_till_at' => null,
            'canceled_at' => CarbonImmutable::now(),
            'access_till_at' => CarbonImmutable::now()->addDays(config('billing.gps.access_info_till_at')),
            'status' => DeviceSubscriptionStatus::CANCELED,
        ]);

        // переводим в inactive все активные девайсы
        foreach ($model->devices as $device){
            /** @var $device Device */
            if($device->status->isDeleted()) continue;

            if($device->status->isActive()){
                $device->status = DeviceStatus::INACTIVE();
                $device->inactive_at = CarbonImmutable::now();
                $device->active_till_at = null;
                $device->status_request = DeviceRequestStatus::CANCEL_SUBSCRIPTION;
            }

            $device->status_activate_request = DeviceStatusActivateRequest::NONE;

            DeviceHistory::create($device, DeviceHistoryContext::SUBSCRIPTION_CANCEL());

            $device->save();
        }

        $ids = $model->devices->pluck('id')->toArray();
        // отвязываем девайсы от техники
        Truck::query()->whereIn('gps_device_id', $ids)->update(['gps_device_id' => null]);
        Trailer::query()->whereIn('gps_device_id', $ids)->update(['gps_device_id' => null]);

        /** @var $deviceRequestService DeviceRequestService */
        $deviceRequestService = resolve(DeviceRequestService::class);
        $deviceRequestService->closedIfUnsubscribe($model->company);
    }

    public function deviceCancelSubscription(DeviceSubscription $model)
    {
        foreach ($model->devices as $device){
            /** @var $device Device */
            if($device->status->isDeleted()) continue;

            $device->status_request = DeviceRequestStatus::CANCEL_SUBSCRIPTION;
            $device->status_activate_request = DeviceStatusActivateRequest::NONE;

            DeviceHistory::create($device, DeviceHistoryContext::SUBSCRIPTION_CANCEL());

            $device->save();
        }
    }

    public function createWarningNotification()
    {
        $date = CarbonImmutable::now()
//            ->subHours(config('gps.subscription.warning_notifications_hours'))
        ;

        $models = DeviceSubscription::query()
            ->where('status', DeviceSubscriptionStatus::ACTIVE_TILL)
            ->where('send_warning_notify', false)
            ->whereDate('activate_till_at', '=', $date)
            ->get();

        foreach ($models as $model){
            /** @var $model DeviceSubscription */
            Notification::create(new WarningSubscriptionCancel($model));

            $model->update(['send_warning_notify' => true]);
        }
    }

    // проверяет есть ли в подписки активные девайсы, если нету, автоматом отменяет подписку
    public function checkActiveDeviceInSubscription($companyId): bool
    {
        $query = DeviceSubscription::query()
            ->with(['devices'])
            ->where('status', DeviceSubscriptionStatus::ACTIVE)
            ->where('company_id', $companyId)
            ->first();

        if($query){
            if(
                $query
                ->devices
                ->where('status', DeviceStatus::ACTIVE)
                ->isEmpty()
            ) {
                $this->cancel($query);

                return true;
            }
        }
        return false;
    }
}
