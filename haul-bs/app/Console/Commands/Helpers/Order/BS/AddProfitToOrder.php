<?php

namespace App\Console\Commands\Helpers\Order\BS;

use App\Enums\Orders\BS\OrderStatus;
use App\Models\Orders\BS\Order;
use Illuminate\Console\Command;
use Symfony\Component\Console\Helper\ProgressBar;

class AddProfitToOrder extends Command
{
    protected $signature = 'helper:add_profit_to_order';

    public function handle()
    {
        $progressBar = new ProgressBar($this->output, Order::count());
        $progressBar->setFormat('verbose');

        try {
            $progressBar->start();


            Order::query()->where('status', OrderStatus::Finished())
                ->each(function (Order $order) use($progressBar) {
                    $partsCost = round($order->getPartsCost(), 2);
                    $order->update([
                        'parts_cost' => $partsCost,
                        'profit' => $partsCost
                            ? round($order->total_amount - $partsCost, 2)
                            : $order->total_amount
                    ]);
                    $progressBar->advance();
                });

            $progressBar->finish();
            echo PHP_EOL;

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $progressBar->clear();
            $this->error($e->getMessage());
            return self::FAILURE;
        }
    }
}
