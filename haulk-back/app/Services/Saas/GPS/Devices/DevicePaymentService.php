<?php

namespace App\Services\Saas\GPS\Devices;

use App\Enums\Format\DateTimeEnum;
use App\Models\Saas\Company\Company;
use App\Models\Saas\GPS\Device;
use App\Models\Saas\GPS\DeviceHistory;
use App\Models\Saas\GPS\DevicePayment;
use Carbon\CarbonImmutable;

class DevicePaymentService
{
    public function create(Company $company): void
    {
        $company->load([
            'gpsDevices',
            'gpsDeviceSubscription',
        ]);
        try {
            \DB::beginTransaction();

            foreach ($company->gpsDevices as $device){
                /** @var $device Device */

                if(!$device->status->isActive()) continue;

                if(
                    !DevicePayment::query()
                        ->where('company_id', $company->id)
                        ->where('device_id', $device->id)
                        ->where('date', CarbonImmutable::now()->format(DateTimeEnum::DATE))
                        ->exists()
                ){
                    $rate = $company->gpsDeviceSubscription->current_rate;
                    $start = $company->subscription->billing_start;
                    $end = $company->subscription->billing_end;
                    $daysInInterval = $start->daysUntil($end)->count();

                    $model = new DevicePayment();
                    $model->device_id = $device->id;
                    $model->company_id = $device->company_id;
                    $model->deactivate = $device->active_till_at !== null;
                    if($company->isExclusivePlan()){
                        $model->amount = 0;
                    } else {
                        $model->amount = $rate/$daysInInterval;
                    }
                    $model->date = CarbonImmutable::now()->format(DateTimeEnum::DATE);

                    $model->save();

                    DeviceHistory::createPayment($device, $model);
                }
            }

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            throw new \Exception($e);
        }
    }
}

