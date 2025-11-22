<?php

namespace App\Console\Commands\Fueling;

use App\Enums\Fueling\FuelCardStatusEnum;
use App\Models\Fueling\FuelCard;
use Illuminate\Console\Command;

class FuelCardOldDelete extends Command
{
    protected $signature = 'fuel-card:delete';

    public function handle()
    {
        try {
            FuelCard::query()
                ->where('status', FuelCardStatusEnum::DELETED)
                ->where('deleted_at', '<=', now()->subMonths(6))
                ->delete();
        } catch (\Exception $e){
            $this->error($e->getMessage());
        }
    }
}

