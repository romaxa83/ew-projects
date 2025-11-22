<?php

namespace App\Console\Commands\Helpers;

use App\Models\Orders\Order;
use App\Services\Orders\OrderService;
use Illuminate\Console\Command;

class CheckPaymentForMile extends Command
{
    protected $signature = 'helper:order_payment_for_mile';

    protected OrderService $service;

    public function __construct(OrderService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    public function handle()
    {
        $orderId = $this->ask('Enter Order id');
        try {
            $start = microtime(true);

            $this->exec($orderId);

            $time = microtime(true) - $start;

            logger_info("[helper] FILL PAYMENT FOR MILE [time = {$time}]");
            $this->info("[helper] FILL PAYMENT FOR MILE [time = {$time}]");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            logger_info('[helper] FILL PAYMENT FOR MILE', [
                'msg' => $e->getMessage()
            ]);
            return self::FAILURE;
        }
    }

    private function exec($id): void
    {
        $order = Order::find($id);
        if(!$order){
            throw new \Exception("Order not found [$id]");
        }

        $this->service->paymentForDistance($order, true);
    }
}
