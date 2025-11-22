<?php

namespace App\Console\Commands\FixDB;

use App\Models\Customers\Address;
use Illuminate\Console\Command;

class CustomAddressSort extends Command
{
    protected $signature = 'fix_db:customer_address_sort';

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
        Address::query()
            ->where('sort', '=', 0)
            ->each(function(Address $item) {

                if($item->isDefault()){
                    $item->update(['sort' => $item->created_at->timestamp * 3]);
                } else {
                    $item->update([
                        'sort' => $item->fromEcomm()
                            ? $item->created_at->timestamp * 2
                            : $item->created_at->timestamp,
                    ]);
                }

            });
    }
}



