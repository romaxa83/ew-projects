<?php

namespace WezomCms\Firebase\Helpers;

use WezomCms\Firebase\Dto\TemplateDto;
use WezomCms\Firebase\Models\Template;
use WezomCms\Firebase\Repositories\TemplateRepository;
use WezomCms\Firebase\Services\TemplateService;

class SetTemplates
{
    public function __construct(
        protected TemplateService $service,
        protected TemplateRepository $repo,
    ) {
    }

    public function run(): void
    {
        foreach ($this->data() as $item) {
            if (!$this->repo->existBy('type', $item['type'])) {
                $this->service->create(TemplateDto::byArgs($item));

                echo "Set notification template - [{$item['type']}]" . PHP_EOL;
            }
        }
    }

    private function data(): array
    {
        return [
            [
                'type' => Template::TYPE_REGISTRY,
                'vars' => [
                    'user_name' => 'Имя пользователя'
                ],
                'translations' => [
                    [
                        'locale' => 'ru',
                        'title' => 'Регистрация',
                        'text' => '{user_name} вы успешно зарегестрировались',
                    ],
                    [
                        'locale' => 'kk',
                        'title' => 'Регистрация (kk)',
                        'text' => '{user_name} вы успешно зарегестрировались (kk)',
                    ]
                ]
            ],
            [
                'type' => Template::TYPE_COLLECTION_SOON_FINISH,
                'vars' => [
                    'collection_name' => 'Название коллекции',
                    'finished_at' => 'Дата завершения',
                    'user_name' => 'Имя пользователя'
                ],
                'translations' => [
                    [
                        'locale' => 'ru',
                        'title' => 'Коллекция',
                        'text' => 'Коллекция {collection_name} будет завершена в - {finished_at}, вы {user_name} еще можете затариться',
                    ],
                    [
                        'locale' => 'kk',
                        'title' => 'Коллекция (kk)',
                        'text' => 'Коллекция {collection_name} будет завершена в - {finished_at}, вы {user_name} еще можете затариться (kk)',
                    ]
                ]
            ],
            [
                'type' => Template::TYPE_COLLECTION_START,
                'vars' => [
                    'collection_name' => 'Название коллекции',
                    'user_name' => 'Имя пользователя'
                ],
                'translations' => [
                    [
                        'locale' => 'ru',
                        'title' => 'Старт коллекции',
                        'text' => 'Коллекция {collection_name} стартовала, вы {user_name} должны затариться ))',
                    ],
                    [
                        'locale' => 'kk',
                        'title' => 'Старт коллекции (kk)',
                        'text' => 'Коллекция {collection_name} стартовала, вы {user_name} должны затариться )) (kk)',
                    ]
                ]
            ],
            [
                'type' => Template::TYPE_ORDER_CHANGE_STATUS,
                'vars' => [
                    'order_id' => 'Номер заказа',
                    'user_name' => 'Имя пользователя',
                    'status_text' => 'Текст статуса заказа',
                ],
                'translations' => [
                    [
                        'locale' => 'ru',
                        'title' => 'Заказ №{order_id}',
                        'text' => '{status_text}',
                    ],
                    [
                        'locale' => 'kk',
                        'title' => 'Заказ №{order_id}',
                        'text' => '{status_text}',
                    ]
                ]
            ],
        ];
    }
}

