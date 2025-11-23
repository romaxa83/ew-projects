<?php

namespace WezomCms\Catalog\Database\Seeders;


class LabelRemoveSeeder extends LabelSeeder
{
    public function run()
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \DB::table('labels')->truncate();
        \DB::table('label_translations')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        parent::run();
    }
}
