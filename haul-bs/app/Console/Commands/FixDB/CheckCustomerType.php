<?php

namespace App\Console\Commands\FixDB;

use App\Enums\Customers\CustomerType;
use App\Models\Customers\Customer;
use Illuminate\Console\Command;

class CheckCustomerType extends Command
{
    protected $signature = 'fix_db:customer_type';

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
        Customer::query()
            ->where('from_haulk', true)
            ->whereNot('type', CustomerType::Haulk)
            ->each(function(Customer $item) {
                $item->update(['type' => CustomerType::Haulk]);
            });
    }
}


