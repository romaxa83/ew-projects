<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use WezomCms\Firebase\UseCase\CallPushEvent;
use WezomCms\Services\Repositories\ServiceGroupRepository;
use WezomCms\Services\Types\ServiceType;
use WezomCms\ServicesOrders\Repositories\OrderRepository;

class RemindForService extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'niko:remind-service';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Команда запускаеться для напоминание пользователю о запланированом сервисе (for crone)';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @throws \Exception
     */
    public function handle()
    {
        $time = Carbon::now()->addMinutes('90');

        $serviceIds = \App::make(ServiceGroupRepository::class)->getIdByTypes([ServiceType::TYPE_STO, ServiceType::TYPE_TEST_DRIVE]);
        $orders = \App::make(OrderRepository::class)->getOrderForRemind($serviceIds, $time);

        foreach ($orders as $order){
            CallPushEvent::remindOrder($order);
        }
    }
}
