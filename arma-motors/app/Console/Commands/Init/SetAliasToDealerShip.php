<?php

namespace App\Console\Commands\Init;

use App\Models\Dealership\Dealership;
use App\Repositories\Dealership\DealershipRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SetAliasToDealerShip extends Command
{
    protected $signature = 'am:d-alias';

    protected $description = 'Set alias to dealer ship';

    public function handle(DealershipRepository $dealershipRepository)
    {
        foreach ($dealershipRepository->getAll(['current', 'brand']) as $item){
            $name = $item->current->name . ' ' . $item->brand->name;
            $slug = Str::slug($name);
            /** @var $item Dealership */
            $item->alias = $slug;
            $item->save();
        }

        $this->info("[✔]");
    }
}
