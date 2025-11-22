<?php

namespace App\Services\Saas\GPS\Devices;

use App\Enums\Saas\GPS\Request\DeviceRequestSource;
use App\Enums\Saas\GPS\Request\DeviceRequestStatus;
use App\Models\Alerts\Alert;
use App\Models\Saas\Company\Company;
use App\Models\Saas\GPS\DeviceRequest;
use App\Models\Users\User;
use App\Services\Events\GPS\Devices\DeviceRequestEventService;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;

class DeviceRequestService
{
    protected DeviceSubscriptionService $deviceSubscriptionService;

    public function __construct(DeviceSubscriptionService $deviceSubscriptionService)
    {
        $this->deviceSubscriptionService = $deviceSubscriptionService;
    }

    public function create(User $user, array $data): DeviceRequest
    {
        $model = new DeviceRequest();
        $model->user_id = $user->id;
        $model->company_id = $user->getCompanyId();
        $model->qty = $data['qty'];
        $model = $this->setStatus($model, DeviceRequestStatus::NEW());
        $model->save();

        if(!$user->getCompany()->gpsDeviceSubscription){
            $this->deviceSubscriptionService->create($user->getCompany());
        }

        return $model;
    }

    public function createFromBackOffice(
        Company $company,
        User $user,
        array $data
    ): DeviceRequest
    {
        $model = new DeviceRequest();
        $model->user_id = $user->id;
        $model->company_id = $company->id;
        $model->qty = $data['qty'];
        $model->source = DeviceRequestSource::BACKOFFICE();
        $model = $this->setStatus($model, DeviceRequestStatus::NEW());
        $model->save();

        if(!$company->gpsDeviceSubscription){
            $this->deviceSubscriptionService->create($company);
        }

        return $model;
    }

    public function update(DeviceRequest $model, array $data): DeviceRequest
    {
        if($model->status->isClosed()){
            throw ValidationException::withMessages(['status' => __('exceptions.gps_device.request.closed_for_editing')]);
        }

        $model = $this->setStatus($model, DeviceRequestStatus::fromValue(data_get($data, 'status')));
        $model->comment = data_get($data, 'comment');

        $model->save();

        return $model;
    }

    private function setStatus(
        DeviceRequest $model,
        DeviceRequestStatus $status,
        bool $save = false
    ): DeviceRequest
    {
        $model->status = $status;

        if($status->isClosed()){
            $model->closed_at = CarbonImmutable::now();
        }

        if($save){
            $model->save();
        }

        if(!$status->isNew()){
            $msg = null;
            if($status->isInWork()){
                $msg = 'notification.device.for_crm.take_request';
            }
            if($status->isClosed()){
                $msg = 'notification.device.for_crm.close_request';
            }

            $this->createAlert($model, $msg);

            DeviceRequestEventService::deviceRequest($model)
                ->user($model->user)
                ->status($status)
                ->broadcast();
        }

        return $model;
    }

    public function closedIfUnsubscribe(Company $company): void
    {
        $models = DeviceRequest::query()
            ->where('company_id', $company->id)
            ->whereIn('status', [
                DeviceRequestStatus::NEW,
                DeviceRequestStatus::IN_WORK
            ])
            ->get()
        ;

        if($models->isNotEmpty()){
            $models->each(function (DeviceRequest $item) use ($company) {
                $item->update([
                    'status' => DeviceRequestStatus::CLOSED,
                    'comment' => "The {$company->name} has cancelled GPS subscription",
                    'closed_at' => CarbonImmutable::now()
                ]);

                $this->createAlert($item, 'notification.device.for_crm.cancel_subscription');

                DeviceRequestEventService::deviceRequest($item)
                    ->user($item->user)
                    ->status($item->status)
                    ->broadcast();
            });
        }
    }

    private function createAlert(DeviceRequest $model, string $msg): void
    {
        $alert = new Alert();
        $alert->carrier_id = $model->company_id;
        $alert->recipient_id = $model->user_id;
        $alert->type = 'device_request';
        $alert->meta = [
            'device_id' => $model->id,
            'status' => $model->status->value,
        ];
        $alert->message = $msg;
        $alert->save();
    }
}

