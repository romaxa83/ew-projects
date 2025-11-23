<?php

namespace App\Console\Commands\Helpers\CashRegistry;

use App\Services\CashRegistry\CashRegistryService;
use App\User;
use Illuminate\Console\Command;

class FillCashRegistry extends Command
{
    protected $signature = 'helpers:fill_cash_registry';

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
        User::query()
            ->with(['employee'])
            ->where('active', 1)
            ->whereHas('roles', function ($q) {
                $q->foreman();
            })
            ->each(fn (User $user) => $this->service->create($user->employee))
        ;
    }
}



