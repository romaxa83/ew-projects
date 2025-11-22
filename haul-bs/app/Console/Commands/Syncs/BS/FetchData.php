<?php

namespace App\Console\Commands\Syncs\BS;

use App\Console\Commands\BaseCommand;

class FetchData extends BaseCommand
{
    protected $signature = 'sync:bs_data';

    public function exec(): void
    {
        \Artisan::call(UsersSync::class);
        \Artisan::call(FetchMediaUser::class);
        \Artisan::call(FetchSettings::class);
        \Artisan::call(FetchState::class);
        \Artisan::call(FetchTranslations::class);
        \Artisan::call(FetchSupplier::class);
        \Artisan::call(FetchTag::class);
        \Artisan::call(FetchCustomer::class);
        \Artisan::call(FetchMakes::class);
        \Artisan::call(FetchVehicles::class);
        \Artisan::call(FetchInventory::class);
        \Artisan::call(FetchTypeOfWork::class);
        \Artisan::call(FetchOrder::class);
    }
}
