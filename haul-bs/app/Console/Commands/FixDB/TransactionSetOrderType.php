<?php

namespace App\Console\Commands\FixDB;

use App\Enums\Orders\OrderType;
use App\Models\Inventories\Transaction;
use DB;
use Illuminate\Console\Command;

class TransactionSetOrderType extends Command
{
    protected $signature = 'fix_db:transaction_order_type';

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
        Transaction::query()->each(function (Transaction $transaction) {
            if($transaction->order_id){
                $transaction->update(['order_type' => OrderType::BS()]);
            }
            if($transaction->order_parts_id){
                $transaction->update(['order_type' => OrderType::Parts()]);
            }
        });
    }
}

