<?php

namespace App\Console\Commands\Billing;

use App\Models\Saas\GPS\Device;
use App\Services\Saas\GPS\Devices\DeviceSubscriptionService;
use App\Services\Saas\GPS\Flespi\Collections\DeviceEntityCollection;
use App\Services\Saas\GPS\Flespi\Commands\Devices\DeviceBlockCommand;
use App\Services\Saas\GPS\Flespi\Commands\Devices\DeviceGetAllCommand;
use Illuminate\Console\Command;

class GpsSubscriptionCanceled extends Command
{
    protected $signature = 'billing:gps_subscription_canceled';

    protected $description = 'Command description';

    protected DeviceSubscriptionService $service;

    public function __construct(DeviceSubscriptionService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    public function handle()
    {
//        $device = Device::find(15);
//
//        /** @var $command DeviceBlockCommand */
//        $command = resolve(DeviceBlockCommand::class);
//        $res = $command->device($device)->handler();
//
//        dd($device);


        $this->cancelingSubscription();
    }

    private function cancelingSubscription()
    {
        $this->service->cancelingProcess();
    }
}

