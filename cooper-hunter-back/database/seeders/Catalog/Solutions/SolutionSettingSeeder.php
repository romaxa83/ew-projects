<?php

namespace Database\Seeders\Catalog\Solutions;

use App\Enums\Solutions\SolutionSeriesEnum;
use App\Models\BaseModel;
use App\Models\Catalog\Solutions\Series\SolutionSeries;
use App\Models\Localization\Language;
use Core\Enums\BaseEnum;
use Illuminate\Database\Seeder;

class SolutionSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $this->setData(
            SolutionSeriesEnum::class,
            SolutionSeries::class
        );
    }

    private function setData(string|BaseEnum $enumClass, string|BaseModel $modelClass): void
    {
        $climateZones = $enumClass::getKeys();

        foreach ($climateZones as $climateZone) {
            $model = $modelClass::where('slug', $climateZone);

            if ($model->exists()) {
                $model = $model->first();
            } else {
                $model = new $modelClass();
                $model->slug = $climateZone;
                $model->save();
            }

            languages()
                ->each(
                    fn(Language $language) => $model->translations()
                        ->updateOrCreate(
                            [
                                'language' => $language->slug,
                            ],
                            [
                                'title' => trans(
                                    key: $enumClass::getLocalizationKey() . '.' . $climateZone,
                                    locale: $language->slug
                                )
                            ]
                        )
                );
        }
    }
}
