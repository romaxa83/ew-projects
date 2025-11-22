<?php

namespace App\Console\Commands\Helpers\Order\BS;


use App\Models\BodyShop\Orders\Order;
use Illuminate\Console\Command;

class AddProfitToOrder extends Command
{
    protected $signature = 'helper:add_profit_to_order';

    public function handle()
    {
        try {
            Order::query()->where('status', Order::STATUS_FINISHED)
                ->each(function (Order $order) {
                    $partsCost = round($order->getPartsCost(), 2);
                    $order->update([
                        'parts_cost' => $partsCost,
                        'profit' => $partsCost
                            ? round($order->total_amount - $partsCost, 2)
                            : $order->total_amount
                    ]);
                });
        } catch (\Throwable $e) {
            dd($e->getMessage());
        }
    }
}
