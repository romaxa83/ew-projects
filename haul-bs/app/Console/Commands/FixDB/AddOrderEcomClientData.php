<?php

namespace App\Console\Commands\FixDB;

use App\Enums\Orders\OrderType;
use App\Models\Inventories\Transaction;
use App\Models\Orders\Parts\Order;
use DB;
use Illuminate\Console\Command;

class AddOrderEcomClientData extends Command
{
    protected $signature = 'fix_db:order_ecommerce_client';

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
            ->whereNotNull('ecommerce_client')
            ->whereNull('ecommerce_client_email')
            ->whereNull('ecommerce_client_name')
            ->each(function (Order $model) {
                $model->ecommerce_client_email = $model->ecommerce_client->email->getValue();
                $model->ecommerce_client_name = $model->ecommerce_client->getFullNameAttribute();
                $model->save();
            });
    }
}

