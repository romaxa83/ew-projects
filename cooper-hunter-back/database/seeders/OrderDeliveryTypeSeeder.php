<?php

namespace Database\Seeders;

use App\Enums\Orders\OrderDeliveryTypeEnum;
use App\Models\Localization\Language;
use App\Models\Orders\Deliveries\OrderDeliveryType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Throwable;

class OrderDeliveryTypeSeeder extends Seeder
{

    /**
     * @throws Throwable
     */
    public function run(): void
    {
        if (OrderDeliveryType::query()->exists()) {
            return;
        }

        $types = OrderDeliveryTypeEnum::getValues();

        $translationKey = OrderDeliveryTypeEnum::getLocalizationKey();

        foreach ($types as $type) {
            $deliveryType = new OrderDeliveryType();

            $deliveryType->sort = OrderDeliveryType::DEFAULT_SORT;
            $deliveryType->active = OrderDeliveryType::DEFAULT_ACTIVE;

            $deliveryType->save();

            languages()->each(
                fn (Language $language) => $deliveryType
                    ->translations()
                    ->create([
                        'slug' => Str::slug(trans(key: $translationKey . '.' . $type, locale: $language->slug)),
                        'title' => trans(key: $translationKey . '.' . $type, locale: $language->slug),
                        'language' => $language->slug,
                        'description' => null
                    ])
            );
        }
    }
}

