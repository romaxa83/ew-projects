<?php

namespace App\Console\Commands\Helpers;

use App\Models\Orders\Order;
use App\Services\Orders\OrderService;
use Illuminate\Console\Command;

class SetDriverPickupToOldOrder extends Command
{
    protected $signature = 'helper:set_driver_pickup_to_order';

    protected OrderService $service;

    protected $count = 0;

    public function __construct(OrderService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    public function handle()
    {

        try {
            $start = microtime(true);

            $this->exec();

            $time = microtime(true) - $start;

            logger_info("[helper] ".__CLASS__." [time = {$time}], [req = $this->count]");
            $this->info("[helper] ".__CLASS__." [time = {$time}], [req = $this->count]");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            logger_info('[helper] '.__CLASS__, [
                'msg' => $e->getMessage()
            ]);
            return self::FAILURE;
        }
    }

    private function exec(): void
    {
        Order::query()
            ->get()
            ->each(function(Order $item){
                if(!$item->driver_pickup_id){
                    $item->driver_pickup_id = $item->driver_id;
                }
                if(!$item->driver_delivery_id){
                    $item->driver_delivery_id = $item->driver_id;
                }
                $item->save();

                $this->count++;
            })
        ;
    }
}


