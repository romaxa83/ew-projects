<?php

namespace App\Console\Commands\FixDB;

use App\Enums\Catalog\Products\ProductUnitType;
use App\Models\Catalog\Products\UnitType;
use Illuminate\Console\Command;

class SetUnitType extends Command
{
    protected $signature = 'fixdb:unit-type';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->seed();
    }

    public function seed(): void
    {
        if(!UnitType::query()->where('name', ProductUnitType::SPARES)->first()){

            $model = new UnitType();
            $model->name = ProductUnitType::SPARES;
            $model->save();
        }
    }
}
