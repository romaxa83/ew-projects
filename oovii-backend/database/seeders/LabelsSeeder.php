<?php

namespace Database\Seeders;

use DB;
use Illuminate\Database\Seeder;
use Throwable;
use WezomCms\Catalog\Models\Labels\Label;
use WezomCms\Catalog\Models\Labels\LabelTranslation;

class LabelsSeeder extends Seeder
{
    public function run(): void
    {
        if (!$labels = Label::query()->where('is_gender', true)->count()) {
            try {
                DB::transaction(function () {
                    $data = $this->getData();

                    foreach ($data as $sort => $item) {
                        $model = new Label();
                        $model->sort = $sort;
                        $model->is_gender = data_get($item, 'is_gender', true);
                        $model->save();

                        foreach ($item['translates'] as $lang => $trans) {
                            $translation = new LabelTranslation();
                            $translation->locale = $lang;
                            $translation->name = $trans['name'];
                            $translation->label_id = $model->id;
                            $translation->save();
                        }
                    }
                });
            } catch (Throwable $e) {
                dd($e->getMessage());
            }
        }
    }

    protected function getData(): array
    {
        return [
            [
                'translates' => [
                    'ru' => [
                        'name' => 'Мужчинам',
                    ],
                    'kk' => [
                        'name' => 'Мужчинам',
                    ],
                ],
            ],
            [
                'translates' => [
                    'ru' => [
                        'name' => 'Женщинам',
                    ],
                    'kk' => [
                        'name' => 'Женщинам',
                    ],
                ],
            ],
            [
                'translates' => [
                    'ru' => [
                        'name' => 'Детям',
                    ],
                    'kk' => [
                        'name' => 'Детям',
                    ],
                ],
            ],
        ];
    }
}
