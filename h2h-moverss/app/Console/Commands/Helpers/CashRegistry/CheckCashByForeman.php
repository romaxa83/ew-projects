<?php

namespace App\Console\Commands\Helpers\CashRegistry;

use App\Models\CashRegistry\CashRegistry;
use App\Models\CashRegistry\CashRegistryItem;
use App\Services\CashRegistry\CashRegistryService;
use Illuminate\Console\Command;

class CheckCashByForeman extends Command
{
    protected $signature = 'helpers:check_cash';

    public function __construct(protected CashRegistryService $service)
    {
        parent::__construct();
    }

    public function handle()
    {
        try {
            $start = microtime(true);

            $this->exec();

            $time = microtime(true) - $start;

            echo PHP_EOL;
            $this->info("Done [time = {$time}]");
            echo PHP_EOL;

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }
    }

    public function exec()
    {
        $foremanId = $this->ask('Foreman ID');

        $cashRegistry = CashRegistry::query()
            ->where('employee_id', $foremanId)
            ->first();

        if($cashRegistry){
            $items = CashRegistryItem::query()
                ->where('cash_registry_id', $cashRegistry->id)
                ->orderBy('created_at', 'asc')
                ->get()
            ;

            $sum = $this->service->getCashOnHand($items);


            if (abs($cashRegistry->cash_on_hand - $sum) < 0.0001) {
                $this->info('Check, ['.$cashRegistry->cash_on_hand .' = '.$sum.']');
            } else {
                $this->warn('NOT Check, ['.$cashRegistry->cash_on_hand .' = '.$sum.']');
            }
        }

    }
}

