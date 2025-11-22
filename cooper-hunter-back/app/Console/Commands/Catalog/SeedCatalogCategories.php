<?php

namespace App\Console\Commands\Catalog;

use App\Imports\ImportManager;
use App\Imports\Strategies\CategoryCsvStrategy;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class SeedCatalogCategories extends Command
{
    protected $signature = 'catalog-categories:seed';

    protected $description = 'Import categories from CSV file.';

    /** @throws Throwable */
    public function handle(): int
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('catalog_categories')
            ->truncate();
        DB::table('catalog_category_translations')
            ->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $pathToFile = database_path('files/_category.csv');
        (new ImportManager($pathToFile, CategoryCsvStrategy::class))->handle();

        return self::SUCCESS;
    }
}
