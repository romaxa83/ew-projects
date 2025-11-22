<?php

namespace Database\Seeders;

use App\Models\Localization\Language;
use App\Models\Orders\Categories\OrderCategory;
use App\Models\Orders\Categories\OrderCategoryTranslation;
use Illuminate\Database\Seeder;
use Throwable;

class OrderCategorySeeder extends Seeder
{

    private const CATEGORIES = [
        'other'
    ];

    /**
     * @throws Throwable
     */
    public function run(): void
    {
        foreach (self::CATEGORIES as $slug) {
            $translation = OrderCategoryTranslation::whereSlug($slug)->first();

            if ($translation) {
                $orderCategory = OrderCategory::find($translation->row_id);
            } else {
                $orderCategory = new OrderCategory();
            }

            $orderCategory->active = 1;
            $orderCategory->need_description = 1;
            $orderCategory->is_default = 1;
            $orderCategory->save();

            languages()->each(
                fn (Language $language) => OrderCategoryTranslation::query()->updateOrCreate(
                    [
                        'row_id' => $orderCategory->id,
                        'language' => $language->slug
                    ],
                    [
                        'slug' => $slug,
                        'title' => trans('orders.categories.defaults.' . $slug, [], $language->slug)
                    ]
                )
            );
        }
    }
}

