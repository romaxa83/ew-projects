<?php

namespace App\Console\Commands\Catalog;

use App\Models\Catalog\Brands\Brand;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class SeedBrand extends Command
{
    protected $signature = 'catalog-brands:seed';

    /** @throws Throwable */
    public function handle(): int
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('catalog_brands')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        foreach ($this->data() as $name){
            $model = new Brand();
            $model->name = $name;
            $model->slug = Str::slug($name);
            $model->save();
        }

        return self::SUCCESS;
    }

    public function data(): array
    {
        return [
            'C&H','OLMO'
        ];
    }
}
