<?php

use Database\Seeders\CatalogSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        //deprecated
        //as incompatible changes with seo fields
        //moved to DatabaseSeeder
//        $this->seeder()->run();
    }

    protected function seeder(): CatalogSeeder
    {
        return app(CatalogSeeder::class);
    }

    public function down(): void
    {
    }
};
