<?php

namespace App\Console\Commands\Helpers;

use App\Models\Orders\Order;
use App\Services\Orders\OrderService;
use Illuminate\Console\Command;

class FillPaymentForMile extends Command
{
    protected $signature = 'helper:payment_for_mile';

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

            logger_info("[helper] FILL PAYMENT FOR MILE [time = {$time}], [req = $this->count]");
            $this->info("[helper] FILL PAYMENT FOR MILE [time = {$time}], [req = $this->count]");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            logger_info('[helper] FILL PAYMENT FOR MILE', [
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
                $this->service->paymentForDistance($item, true);

                $this->count++;
            })
        ;
    }
}

