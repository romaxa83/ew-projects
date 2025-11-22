<?php

namespace Database\Seeders;

use App\Models\Dictionaries\TireChangesReason;
use Illuminate\Database\Seeder;

class TireChangesReasonsSeeder extends Seeder
{
    private const REASONS = [
        [
            'need_description' => false,
            'uuid' => 1,
            'translates' => [
                [
                    'language' => 'ru',
                    'title' => 'Ошибка в серийном номере',
                ],
                [
                    'language' => 'uk',
                    'title' => 'Помилка у серійному номері',
                ],
                [
                    'language' => 'en',
                    'title' => 'Error in serial number',
                ],
            ],
        ],
        [
            'need_description' => false,
            'uuid' => 2,
            'translates' => [
                [
                    'language' => 'ru',
                    'title' => 'Ошибка в типоразмере',
                ],
                [
                    'language' => 'uk',
                    'title' => 'Помилка у типорозмірі',
                ],
                [
                    'language' => 'en',
                    'title' => 'Error in size',
                ],
            ],
        ],
        [
            'need_description' => false,
            'uuid' => 3,
            'translates' => [
                [
                    'language' => 'ru',
                    'title' => 'Ошибка в марке/модели',
                ],
                [
                    'language' => 'uk',
                    'title' => 'Помилка у марці/моделі',
                ],
                [
                    'language' => 'en',
                    'title' => 'Error in mark/model',
                ],
            ],
        ],
        [
            'need_description' => false,
            'uuid' => 4,
            'translates' => [
                [
                    'language' => 'ru',
                    'title' => 'Клиент самостоятельно заменил шины',
                ],
                [
                    'language' => 'uk',
                    'title' => 'Клієнт самостійно замінив шини',
                ],
                [
                    'language' => 'en',
                    'title' => 'Client replaced the tires by himself',
                ],
            ],
        ],
        [
            'need_description' => true,
            'uuid' => 5,
            'translates' => [
                [
                    'language' => 'ru',
                    'title' => 'Другое',
                ],
                [
                    'language' => 'uk',
                    'title' => 'Інше',
                ],
                [
                    'language' => 'en',
                    'title' => 'Other',
                ],
            ],
        ],
    ];

    public function run(): void
    {
        foreach (self::REASONS as $reason) {
            $reasonItem = TireChangesReason::updateOrCreate(
                [
                    'uuid' => $reason['uuid'],
                ],
                [
                    'need_description' => $reason['need_description'],
                    'uuid' => $reason['uuid'],
                ]
            );

            foreach ($reason['translates'] as $translate) {
                $reasonItem->translates()->updateOrCreate(
                    [
                        'language' => $translate['language'],
                    ],
                    [
                        'title' => $translate['title'],
                    ]
                );
            }
        }
    }
}
