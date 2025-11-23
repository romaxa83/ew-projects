<?php

namespace WezomCms\Catalog\Database\Seeders;


class SpecificationRemoveSeeder extends SpecificationSeeder
{
    public function run()
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \DB::table('specifications')->truncate();
        \DB::table('specification_translations')->truncate();
        \DB::table('spec_values')->truncate();
        \DB::table('spec_value_translations')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        parent::run();
    }
}
