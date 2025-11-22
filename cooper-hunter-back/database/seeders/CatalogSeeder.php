<?php

namespace Database\Seeders;

use App\Console\Commands\Catalog\SeedBrand;
use App\Console\Commands\Catalog\SeedCatalogCategories;
use App\Enums\Catalog\Products\ProductOwnerType;
use App\Models\Catalog\Brands\Brand;
use App\Models\Catalog\Categories\Category;
use App\Models\Catalog\Categories\CategoryTranslation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Throwable;

class CatalogSeeder extends Seeder
{
    /**
     * @throws Throwable
     */
    public function run(): void
    {
       $this->seedCategory();
       $this->seedBrand();
    }

    protected function seedCategory()
    {
        if(!Category::query()->olmo()->exists()){
            $this->createOlmoCategory();
        }

        if (Category::query()->exists()) {
            return;
        }

        Artisan::call(SeedCatalogCategories::class);
    }

    protected function seedBrand()
    {
        if(Brand::exists()){
            return;
        }
        Artisan::call(SeedBrand::class);
    }

    protected function createOlmoCategory(): void
    {
        $model = new Category();
        $model->slug = 'olmo';
        $model->owner_type = ProductOwnerType::OLMO();
        $model->save();

        $langs = ['en', 'es'];
        foreach ($langs as $lang){
            $t = new CategoryTranslation();
            $t->title = 'OLMO';
            $t->row_id = $model->id;
            $t->language = $lang;
            $t->save();
        }

    }
}
