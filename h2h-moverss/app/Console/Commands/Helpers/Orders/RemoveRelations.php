<?php

namespace App\Console\Commands\Helpers\Orders;

use App\Models\Attachment;
use App\Models\Audit;
use App\Models\Communications\CommunicationRecord;
use App\Models\Order;
use App\Models\Order\Activity;
use App\Models\Order\CustomExtra;
use App\Models\Order\Estimate;
use App\Models\Order\Estimate\Calculated;
use App\Models\Order\Estimate\Interstate;
use App\Models\Order\Estimate\Intrastate;
use App\Models\Order\Estimate\Local;
use App\Models\Order\Extended;
use App\Models\Order\Inventory;
use App\Models\Order\InventoryActivity;
use App\Models\Order\Material;
use App\Models\Order\MobileEstimate;
use App\Models\Order\Notes;
use App\Models\Order\Payment;
use App\Models\Order\Payroll\Payroll;
use App\Models\Order\Service;
use App\Models\Order\StatusChangeHistory;
use App\Models\Order\Waypoint;
use App\Models\Order\Work;
use App\Models\Order\WorkDispatchTouch;
use App\Models\Order\Worker;
use Illuminate\Console\Command;

class RemoveRelations extends Command
{
    protected $signature = 'helpers:order_remove_relations';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        try {
            $start = microtime(true);

            $this->exec();

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

    public function exec()
    {
//         $orderId = 146126;
        $orderId = $this->ask('Order ID');

        $res = Order::query()->where('id', $orderId)->delete();
        $this->info("[x] Order: {$res}");

//        162141

        $res = Activity::query()->where('order_id', $orderId)->delete();
        $this->info("[x] Activities deleted: {$res}");

        $res = CustomExtra::query()->where('order_id', $orderId)->delete();
        $this->info("[x] CustomExtra deleted: {$res}");

        $res = Estimate::query()->where('order_id', $orderId)->delete();
        $this->info("[x] Estimate deleted: {$res}");

        $res = Extended::query()->where('order_id', $orderId)->delete();
        $this->info("[x] Extended deleted: {$res}");

        $res = Inventory::query()->where('order_id', $orderId)->delete();
        $this->info("[x] Inventory deleted: {$res}");

        $res = InventoryActivity::query()->where('order_id', $orderId)->delete();
        $this->info("[x] InventoryActivity deleted: {$res}");

        $res = Material::query()->where('order_id', $orderId)->delete();
        $this->info("[x] Material deleted: {$res}");

        $res = MobileEstimate::query()->where('order_id', $orderId)->delete();
        $this->info("[x] MobileEstimate deleted: {$res}");

        $res = Notes::query()->where('order_id', $orderId)->delete();
        $this->info("[x] Notes deleted: {$res}");

        $res = Payment::query()->where('order_id', $orderId)->delete();
        $this->info("[x] Payment deleted: {$res}");

        $res = Service::query()->where('order_id', $orderId)->delete();
        $this->info("[x] Service deleted: {$res}");

        $res = StatusChangeHistory::query()->where('order_id', $orderId)->delete();
        $this->info("[x] StatusChangeHistory deleted: {$res}");

        $res = Waypoint::query()->where('order_id', $orderId)->delete();
        $this->info("[x] Waypoint deleted: {$res}");

        $res = Work::query()->where('order_id', $orderId)->delete();
        $this->info("[x] Work deleted: {$res}");

        $res = WorkDispatchTouch::query()->where('order_id', $orderId)->delete();
        $this->info("[x] WorkDispatchTouch deleted: {$res}");

        $res = Worker::query()->where('order_id', $orderId)->delete();
        $this->info("[x] WorkDispatchTouch deleted: {$res}");

        $res = Payroll::query()->where('order_id', $orderId)->delete();
        $this->info("[x] Payroll deleted: {$res}");

        $res = Calculated::query()->where('order_id', $orderId)->delete();
        $this->info("[x] Calculated deleted: {$res}");

        $res = Interstate::query()->where('order_id', $orderId)->delete();
        $this->info("[x] Interstate deleted: {$res}");

        $res = Intrastate::query()->where('order_id', $orderId)->delete();
        $this->info("[x] Intrastate deleted: {$res}");

        $res = Local::query()->where('order_id', $orderId)->delete();
        $this->info("[x] Intrastate deleted: {$res}");

        $res = Audit::query()->where('order_id', $orderId)->delete();
        $this->info("[x] Audit deleted: {$res}");
        $res = Audit::query()
            ->where('auditable_type', Order::MORPH_NAME)
            ->where('auditable_id', $orderId)
            ->delete();
        $this->info("[x] Audit MORPH deleted: {$res}");

        $res = CommunicationRecord::query()->where('order_id', $orderId)->delete();
        $this->info("[x] CommunicationRecord deleted: {$res}");

        $res = CommunicationRecord::query()
            ->where('entity_type', Order::MORPH_NAME)
            ->where('entity_id', $orderId)
            ->delete();
        $this->info("[x] CommunicationRecord MORPH deleted: {$res}");

        $res = Attachment::query()
            ->where('entity_type', Order::MORPH_NAME)
            ->where('entity_id', $orderId)
            ->delete();
        $this->info("[x] Attachment MORPH deleted: {$res}");
    }
}