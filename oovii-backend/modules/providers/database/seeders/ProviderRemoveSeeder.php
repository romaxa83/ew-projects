<?php

namespace WezomCms\Providers\Database\Seeders;


class ProviderRemoveSeeder extends ProviderSeeder
{
    public function run()
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \DB::table('providers')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        parent::run();
    }
}
