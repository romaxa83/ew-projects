<?php

namespace App\Console\Commands\BodyShop;

use App\Models\BodyShop\Orders\Order;
use DB;
use Exception;
use Illuminate\Console\Command;

class PurgeDeletedOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders-bs:purge-deleted';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Purge deleted body shop orders older than 30 days';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $this->info('Starting..');

        $timestamp = now()->subDays(config('orders-bs.purge_after'))->format('Y-m-d H:i:s');

        $orders = Order::onlyTrashed()
            ->where('deleted_at', '<', $timestamp)
            ->get();

        $orders->each(function ($order) {
            try {
                DB::beginTransaction();
                $order->forceDelete();
                DB::commit();
            } catch (Exception $e) {
                $this->error('Order ID: ' . $order->id);
                $this->error($e->getMessage());
            }
        });

        $this->info('Finished..');
    }
}
