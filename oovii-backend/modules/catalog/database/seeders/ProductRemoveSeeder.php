<?php

namespace WezomCms\Catalog\Database\Seeders;


class ProductRemoveSeeder extends LabelSeeder
{
    public function run()
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \DB::table('products')->truncate();
        \DB::table('product_translations')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        parent::run();
    }
}
