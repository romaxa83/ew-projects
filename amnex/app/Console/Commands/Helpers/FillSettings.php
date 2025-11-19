<?php

namespace App\Console\Commands\Helpers;

use Illuminate\Console\Command;
use Throwable;
use Wezom\Settings\Models\Setting;

class FillSettings extends Command
{
    protected $signature = 'helpers:fill-setting';

    /**
     * @throws Throwable
     */
    public function handle(): int
    {
        $data = [
            [
                'key' => Setting::KEY_DAYS_TO_EXPIRE,
                'value' => 2,
                'title' => 'Expiration time for the quote, days',
                'type' => Setting::TYPE_INT
            ],
            [
                'group' => Setting::GROUP_MILEAGE_RATE,
                'key' => Setting::KEY_RATE_0_20_MILES,
                'value' => 0,
                'title' => 'Rate for 0-20 miles, $',
                'type' => Setting::TYPE_FLOAT
            ],
            [
                'group' => Setting::GROUP_MILEAGE_RATE,
                'key' => Setting::KEY_RATE_20_40_MILES,
                'value' => 0,
                'title' => 'Rate for 20-40 miles, $',
                'type' => Setting::TYPE_FLOAT
            ],
            [
                'group' => Setting::GROUP_MILEAGE_RATE,
                'key' => Setting::KEY_RATE_40_60_MILES,
                'value' => 0,
                'title' => 'Rate for 40-60 miles, $',
                'type' => Setting::TYPE_FLOAT
            ],
            [
                'group' => Setting::GROUP_MILEAGE_RATE,
                'key' => Setting::KEY_RATE_60_80_MILES,
                'value' => 0,
                'title' => 'Rate for 60-80 miles, $',
                'type' => Setting::TYPE_FLOAT
            ],
            [
                'group' => Setting::GROUP_MILEAGE_RATE,
                'key' => Setting::KEY_RATE_80_100_MILES,
                'value' => 0,
                'title' => 'Rate for 80-100 miles, $',
                'type' => Setting::TYPE_FLOAT
            ],
            [
                'group' => Setting::GROUP_MILEAGE_RATE,
                'key' => Setting::KEY_FURTHER_MILES,
                'value' => 0,
                'title' => 'Further mileage increment, miles',
                'type' => Setting::TYPE_INT
            ],
            [
                'group' => Setting::GROUP_MILEAGE_RATE,
                'key' => Setting::KEY_FURTHER_RATE,
                'value' => 0,
                'title' => 'Further rate increment, $',
                'type' => Setting::TYPE_FLOAT
            ],
            [
                'group' => Setting::GROUP_ACCESORIAL,
                'key' => Setting::KEY_PRICE_FOR_PALLET,
                'value' => 0,
                'title' => 'Price for 1 pallet, $',
                'type' => Setting::TYPE_FLOAT
            ],
            [
                'group' => Setting::GROUP_ACCESORIAL,
                'key' => Setting::KEY_PRICE_FOR_PIECE,
                'value' => 0,
                'title' => 'Price for 1 piece, $',
                'type' => Setting::TYPE_FLOAT
            ],
            [
                'group' => Setting::GROUP_STORAGE,
                'key' => Setting::KEY_PRICE_FOR_STORAGE,
                'value' => 0,
                'title' => 'Price for 1 storage day, $',
                'type' => Setting::TYPE_FLOAT
            ],
            [
                'group' => Setting::GROUP_ACCESORIALS,
                'key' => 'chassis_2_day_min',
                'value' => null,
                'title' => 'Chassis (2 Day Min), $',
                'type' => Setting::TYPE_FLOAT
            ],
            [
                'group' => Setting::GROUP_ACCESORIALS,
                'key' => 'prepull_price',
                'value' => null,
                'title' => 'Prepull (Pre-approval), $',
                'type' => Setting::TYPE_FLOAT
            ],
            [
                'group' => Setting::GROUP_ACCESORIALS,
                'key' => 'chassis_split_price',
                'value' => null,
                'title' => 'Chassis Split, $',
                'type' => Setting::TYPE_FLOAT
            ],
            [
                'group' => Setting::GROUP_ACCESORIALS,
                'key' => 'detention_price',
                'value' => null,
                'title' => 'Detention (First 2 Hours Free), $',
                'type' => Setting::TYPE_FLOAT
            ],
            [
                'group' => Setting::GROUP_ACCESORIALS,
                'key' => 'container_storage_price',
                'value' => null,
                'title' => 'Container Storage, $',
                'type' => Setting::TYPE_FLOAT
            ],
            [
                'group' => Setting::GROUP_ACCESORIALS,
                'key' => 'redelivery_price',
                'value' => null,
                'title' => 'Redelivery/Stop-off/ (Pre-approval), $',
                'type' => Setting::TYPE_FLOAT
            ],
            [
                'group' => Setting::GROUP_ACCESORIALS,
                'key' => 'dry_run_price',
                'value' => null,
                'title' => 'Dry Run (Pre-approval), $',
                'type' => Setting::TYPE_FLOAT
            ],
            [
                'group' => Setting::GROUP_ACCESORIALS,
                'key' => 'bobtail_drop',
                'value' => null,
                'title' => 'Bobtail-Drop (Pre-approval)',
                'type' => Setting::TYPE_STRING
            ],
            [
                'group' => Setting::GROUP_ACCESORIALS,
                'key' => 'tri_axle_price',
                'value' => null,
                'title' => 'Tri-axle (Pre-approval), $',
                'type' => Setting::TYPE_FLOAT
            ],
            [
                'group' => Setting::GROUP_ACCESORIALS,
                'key' => 'overweight_price',
                'value' => null,
                'title' => 'Overweight (Pre-approval or Applicability), $',
                'type' => Setting::TYPE_FLOAT
            ],
            [
                'group' => Setting::GROUP_ACCESORIALS,
                'key' => 'hazardous_cargo_price',
                'value' => null,
                'title' => 'Hazardous Cargo (Pre-approval or Applicability), $',
                'type' => Setting::TYPE_FLOAT
            ],
            [
                'group' => Setting::GROUP_ACCESORIALS,
                'key' => 'reefer_price',
                'value' => null,
                'title' => 'Reefer, $',
                'type' => Setting::TYPE_FLOAT
            ],
            [
                'group' => Setting::GROUP_ACCESORIALS,
                'key' => 'piper_pass_fee',
                'value' => null,
                'title' => 'Pier pass / port Check admin fee, %',
                'type' => Setting::TYPE_INT
            ],
            [
                'group' => Setting::GROUP_ACCESORIALS,
                'key' => 'bonded_price',
                'value' => null,
                'title' => 'Bonded, $',
                'type' => Setting::TYPE_FLOAT
            ],
        ];

        foreach ($data as $item) {
            if (!Setting::query()->where('key', $item['key'])->exists()) {
                $model = new Setting();
                $model->key = $item['key'];
                $model->value = $item['value'];
                $model->title = $item['title'];
                $model->group_title = $item['group'] ?? null;
                $model->type = $item['type'];
                $model->save();

                $this->info("SET [" . $item['key'] . " = " . $item['key'] . "]");
            }
        }

        return static::SUCCESS;
    }
}
