<?php

namespace App\Console\Commands\Billing;

use App\Enums\Saas\GPS\DeviceSubscriptionStatus;
use App\Models\Saas\Company\Company;
use App\Services\Saas\GPS\Devices\DevicePaymentService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class TrackActiveGPSDeviceForPayment extends Command
{
    protected $signature = 'billing:track-active-gps-device';

    protected $description = 'Command description';
    protected DevicePaymentService $paymentService;

    public function __construct(DevicePaymentService $paymentService)
    {
        parent::__construct();
        $this->paymentService = $paymentService;
    }

    public function handle(): void
    {
        Company::query()
            ->with(['gpsDevices'])
            ->whereHas('gpsDeviceSubscription', function(Builder $b){
                $b->whereIn('status', DeviceSubscriptionStatus::forBilling());
            })
            ->each(function (Company $company){
                $this->paymentService->create($company);
            });
    }
}

