<?php

namespace App\Console\Commands\Helpers\Order;

use App\Models\Orders\Order;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Throwable;

class ActualDateFill extends Command
{
    protected $signature = 'helpers:order_actual_date';


    public function handle(): void
    {
        echo "[x] START... " . PHP_EOL;

        $chunk = 200;
        $count = Order::count();

        $progressBar = new ProgressBar($this->output, $count);
        $progressBar->setFormat('verbose');
        $progressBar->start();

        try {

            Order::query()
                ->chunk($chunk, function ($orders) use ($progressBar, $chunk) {
                    $progressBar->advance($chunk);
                    foreach ($orders as $order) {
                        /** @var $order Order */
                        $this->exec($order);
                    }
                });

        } catch (Throwable $e) {
            $progressBar->clear();
            dd($e->getMessage());
        }

        $progressBar->finish();
        echo PHP_EOL;
        echo "[x]  DONE " . PHP_EOL;
    }

    public function exec(Order $order): void
    {
        // получаем таймзону компании
        $defaultTimezone = $order->company->getTimezoneOrDefault();

        $d = null;
        $p = null;

        if($order->delivery_date_actual){
            $deliveryDate = $order->delivery_date_actual;
            $deliveryTimezone = $order->delivery_contact['timezone'] ?? $defaultTimezone;

            $d = Carbon::createFromTimestamp($deliveryDate)
                ->setTimezone(
                    $order->is_manual_change_to_delivery
                        ? $defaultTimezone
                        : $deliveryTimezone
                );
        }

        if($order->pickup_date_actual){
            $pickupDate = $order->pickup_date_actual;
            $pickupTimezone = $order->pickup_contact['timezone'] ?? $defaultTimezone;

            $p = Carbon::createFromTimestamp($pickupDate)
                ->setTimezone(
                    $order->is_manual_change_to_delivery
                        ? $defaultTimezone
                        : $pickupTimezone
                );
        }

        $order->update([
            'pickup_date_actual_tz' => $p,
            'delivery_date_actual_tz' => $d,
        ]);
    }
}
