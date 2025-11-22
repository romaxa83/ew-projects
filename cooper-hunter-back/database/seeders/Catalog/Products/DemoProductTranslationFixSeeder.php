<?php

namespace Database\Seeders\Catalog\Products;

use App\Models\Catalog\Products\Product;
use App\Models\Catalog\Products\ProductTranslation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;

class DemoProductTranslationFixSeeder extends Seeder
{
    public function run(): void
    {
        Product::query()
            ->whereDoesntHave('translation')
            ->chunk(
                100,
                static function (Collection $products) {
                    $translations = [];

                    foreach ($products as $p) {
                        foreach (languages() as $locale) {
                            $translations[] = [
                                'row_id' => $p->id,
                                'language' => $locale->slug,
                            ];
                        }
                    }

                    ProductTranslation::query()->insertOrIgnore($translations);
                }
            );
    }
}
