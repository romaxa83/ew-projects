<?php

namespace WezomCms\Providers\Database\Seeders;

use Illuminate\Database\Seeder;
use WezomCms\Providers\Models\Provider;

class ProviderSeeder extends Seeder
{
    public function run()
    {
        if(!Provider::query()->first()){
            Provider::factory(15)->create();
        }
    }
}
