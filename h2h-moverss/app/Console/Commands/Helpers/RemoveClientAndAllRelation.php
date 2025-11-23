<?php

namespace App\Console\Commands\Helpers;

use App\Models\DispatchEmployer;
use App\Models\DispatchTruck;
use App\Models\Order;
use App\Models\Client;
use Illuminate\Console\Command;

class RemoveClientAndAllRelation extends Command
{
    protected $signature = 'helpers:remove_client';

    private $orderRelations = [
        Order\Notes::class,
        Order\Activity::class,
        Order\Estimate::class,
        Order\Extended::class,
        Order\Inventory::class,
        Order\Material::class,
        Order\MobileEstimate::class,
        Order\Payment::class,
        Order\Service::class,
        Order\StatusChangeHistory::class,
        Order\Estimate\Calculated::class,
        Order\Estimate\Interstate::class,
        Order\Estimate\Intrastate::class,
        Order\Estimate\Local::class,
        Order\Worker::class,
    ];

    private $clienRelations = [
        Client\Activity::class,
        Client\Email::class,
        Client\Phone::class,
        Client\Notes::class,
        Client\Messenger::class,
    ];

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $clientId = $this->ask('Client ID');
        try {
            $start = microtime(true);

            $this->exec($clientId);

            $time = microtime(true) - $start;

            echo PHP_EOL;
            $this->info("Done [time = {$time}]");
            echo PHP_EOL;

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }
    }

    public function exec($clientId)
    {
        if($client = \App\Models\Client::find($clientId)){
            $this->info("Client found [{$client->full_name}]");


            foreach ($client->orders as $order) {
                $orderId = $order->id;
                $this->removeOrderRelations($orderId);

                $order->delete();

                $this->warn("[x] Order deleted");
                echo PHP_EOL;
            }

            $this->line("-------------------- Client [{$clientId}] ------------------");
            foreach ($this->clienRelations as $class){
                if($res = $class::where('client_id', $clientId)->forceDelete()){
                    $this->warn("[x] {$class} deleted [{$res}]");
                }
            }
            $client->delete();
            $this->warn("[x] Client deleted");
            echo PHP_EOL;

            return;
        }

        $this->error("Client not found by id [{$clientId}]");
    }

    public function removeOrderRelations($orderId)
    {
        $this->line("-------------------- Order [{$orderId}] ------------------");

        foreach ($this->orderRelations as $class) {
            if($res = $class::where('order_id', $orderId)->delete()){
                $this->warn("[x] {$class} deleted [{$res}]");
            }
        }

        $waypoint = Order\Waypoint::where('order_id', $orderId)->first();
        $waypointId = $waypoint->id;
        if($res = Order\WaypointNotes::where('waypoint_id', $waypointId)->delete()){
            $this->warn("[x] Order\WaypointNotes deleted [{$res}]");
        }
        if($res = $waypoint->delete()){
            $this->warn("[x] Order\Waypoint deleted [{$res}]");
        }

        $work = Order\Work::where('order_id', $orderId)->first();
        $workId = $work->id;
        if($res = DispatchTruck::where('work_id', $workId)->delete()){
            $this->warn("[x] DispatchTruck deleted [{$res}]");
        }
        if($res = DispatchEmployer::where('work_id', $workId)->delete()){
            $this->warn("[x] DispatchEmployer deleted [{$res}]");
        }
        if($res = \DB::table('orders_works_2_work')
            ->where('work_id', $workId)
            ->delete())
        {
            $this->warn("[x] orders_works_2_work deleted [{$res}]");
        }
    }
}
