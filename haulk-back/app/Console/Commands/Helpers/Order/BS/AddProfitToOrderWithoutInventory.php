<?php

namespace App\Console\Commands\Helpers\Order\BS;


use App\Models\BodyShop\Orders\Order;
use Illuminate\Console\Command;

class AddProfitToOrderWithoutInventory extends Command
{
    protected $signature = 'helper:add_profit_to_order_without_inventory';

    public function handle()
    {
        try {
            Order::query()
                ->where('status', Order::STATUS_FINISHED)
                ->where('profit', '=', '0')
                ->each(function (Order $order) {
                    $order->update([
                        'profit' => $order->total_amount
                    ]);
                });
        } catch (\Throwable $e) {
            dd($e->getMessage());
        }
    }
}
