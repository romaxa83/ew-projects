<?php

namespace App\Console\Commands\BodyShop;

use App\Models\BodyShop\Orders\Order;
use DB;
use Exception;
use Illuminate\Console\Command;

class ProcessFinishedPaidOrders extends Command
{

    protected $signature = 'orders-bs:process-finished-paid';

    protected $description = 'Delete paid orders after 24 months';

    public function handle(): void
    {
        $this->info('Starting..');

        $timestamp = now()->subDays(config('orders-bs.delete_after'));

        $orders = Order::query()
            ->where('status', Order::STATUS_FINISHED)
            ->where('is_paid', true)
            ->where('status_changed_at', '<', $timestamp)
            ->select(['id'])
            ->get();

        $orders->each(
            function (Order $order) {
                try {
                    DB::beginTransaction();
                    $order->forceDelete();
                    DB::commit();
                } catch (Exception $e) {
                    $this->error('Order ID: ' . $order->id);
                    $this->error($e->getMessage());
                }
            }
        );

        $this->info('Finished..');
    }
}
