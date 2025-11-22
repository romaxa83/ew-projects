<?php

namespace App\Console\Commands\FixDB;

use App\Enums\Orders\Parts\OrderSource;
use App\Models\Orders\Parts\Order;
use Illuminate\Console\Command;

class OrderSetAmount extends Command
{
    protected $signature = 'fix_db:order_set_amount';

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

            $this->info("Done [time = {$time}]");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }
    }

    private function exec()
    {
        Order::query()
            ->where('source', OrderSource::Haulk_Depot())
            ->each(function(Order $item) {
                $item->setAmounts();
            });
    }
}
