<?php

namespace WezomCms\Catalog\Database\Seeders;

use Illuminate\Database\Seeder;
use WezomCms\Catalog\Models\Labels\Label;
use WezomCms\Providers\Models\Provider;

class LabelSeeder extends Seeder
{
    public function run()
    {
        if(!Label::query()->first()){
            Label::factory(5)->create();
        }
    }
}
